<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Privacy_model extends Common_Model {

    protected $privacy_options = array();

    public function __construct() {
        parent::__construct();
    }

    /**
     * [set_privacy_options used to assign user privacy in variable]
     * @param type $user_id
     */
    function set_privacy_options($user_id) {
        $this->privacy_options = $this->privacy_model->news_feed_setting_details($user_id);
    }

    /**
     * [get_privacy_options used to return user privacy data]
     * @return type
     */
    function get_privacy_options() {
        return $this->privacy_options;
    }

    /**
     * Function Name: save
     * @param user_id
     * @param privacy
     * @param options
     * Description: add / update privacy settings of user
     */
    function save($user_id, $privacy, $options = array()) {
        $this->db->where('UserID', $user_id);
        $this->db->delete(USERPRIVACY);

        $this->set_privacy($user_id, $privacy);

        if ($privacy != 'customize') {
            $options = $this->get_privacy_label($privacy);
        }

        $insert_batch = array();
        $existed_keys = array();
        if ($options) {
            foreach ($options as $opt) {
                $insert_batch[] = array('UserID' => $user_id, 'PrivacyLabelKey' => $opt['Key'], 'Value' => $opt['Value'], 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                $existed_keys[] = $opt['Key'];
            }
        }

        $this->db->select('Key');
        $this->db->from(PRIVACYLABEL);
        $this->db->where_not_in('Key', $existed_keys);
        $remaining_privacy_label = $this->db->get();
        if ($remaining_privacy_label->num_rows()) {
            foreach ($remaining_privacy_label->result_array() as $remaining_label) {
                $insert_batch[] = array('UserID' => $user_id, 'PrivacyLabelKey' => $remaining_label['Key'], 'Value' => 'self', 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
            }
        }

        if ($insert_batch) {
            $this->db->insert_batch(USERPRIVACY, $insert_batch);
        }
        if (CACHE_ENABLE) {
            $privacy_details = $this->details($user_id, true);
            $this->cache->save('privacy_' . $user_id, $privacy_details, CACHE_EXPIRATION);
        }
    }

    /**
     * Function Name: get_privacy
     * @param user_id
     * Description: get user's privacy
     */
    function get_privacy($user_id) {
        $query = $this->db->limit(1)->get_where(USERDETAILS, array('UserID' => $user_id));
        if ($query->num_rows()) {
            return $query->row()->Privacy;
        } else {
            return false;
        }
    }

    /**
     * Function Name: set_privacy
     * @param user_id
     * Description: get user's privacy
     */
    function set_privacy($user_id, $privacy) {
        $this->db->set('Privacy', $privacy);
        $this->db->where('UserID', $user_id);
        $this->db->update(USERDETAILS);
    }

    function check_privacy($current_user_id, $user_id, $privacy_key, $debug = 0) {
        $return = 0;

        if ($current_user_id == $user_id) {
            return 1;
        }

        $users_relation = get_user_relation($current_user_id, $user_id);
        $privacy_details = $this->privacy_model->details($user_id);
        $privacy = "Low";
        if (isset($privacy_details['Privacy'])) {
            $privacy = ucfirst($privacy_details['Privacy']);
        }

        if (isset($privacy_details['Label'])) {
            foreach ($privacy_details['Label'] as $privacy_label) {
                if (isset($privacy_label[$privacy])) {
                    if ($privacy_label['Value'] == $privacy_key && in_array($privacy_label[$privacy], $users_relation)) {
                        return 1;
                    }
                }
            }
        }

        return 0;
    }

    function get_value($user_id, $key) {
        $this->db->select('Value');
        $this->db->from(USERPRIVACY);
        $this->db->where('UserID', $user_id);
        $this->db->where('PrivacyLabelKey', $key);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->Value;
        } else {
            return '';
        }
    }

    /**
     * Function Name: details
     * @param user_id
     * Description: get the details of user privacy settings
     */
    function details($user_id, $no_cache = FALSE, $check_only = TRUE) {

        $cache_support = false;
        $data = array('Privacy' => '', 'Label' => array(), 'DefaultOptions' => array());
        if (CACHE_ENABLE && !$no_cache) {
            $cache_support = true;
            $data = $this->cache->get('privacy_' . $user_id);
            if ($data && is_array($data)) {
                return $data;
            } else {
                $data = array('Privacy' => '', 'Label' => array(), 'DefaultOptions' => array());
            }
        }

        $exclude_privacy_options = $this->get_excluding_privacy_options();
        if ($exclude_privacy_options) {
            $this->db->where_not_in('Key', $exclude_privacy_options);
        }

        $this->db->select('Name, `Key` as `Value`, "" as Customize, DisplaySection as Section', false);
        $this->db->select('IFNULL(LowPrivacyOption,"") as Low', FALSE);
        $this->db->select('IFNULL(MediumPrivacyOption,"") as Medium', FALSE);
        $this->db->select('IFNULL(LowPrivacyOption,"") as Low', FALSE);

        $this->db->order_by('DisplayOrder');
        $query = $this->db->get(PRIVACYLABEL);

        if ($query->num_rows()) {
            $result = $query->result_array();
            $result = $this->change_label_data_for_module($result);

            $privacy = $this->get_privacy($user_id);
            $data['Privacy'] = $privacy;
            if ($privacy == 'customize') {
                $this->db->select('PrivacyLabelKey as `Key`', false);
                $this->db->select('IFNULL(Value,"") as Value', FALSE);
                $this->db->from(USERPRIVACY);
                $this->db->where('UserID', $user_id);
                $privacy_query = $this->db->get();
                if ($result) {
                    foreach ($result as $r) {
                        foreach ($privacy_query->result() as $pq) {
                            if ($pq->Key == $r['Value']) {
                                $r['Customize'] = $pq->Value;
                            }
                        }
                        $data['Label'][] = $r;
                    }
                }
            } else {
                $data['Label'] = $result;
            }
            
            //Get default options
            $this->db->select('Name,`Key` as `Value`', false);
            $this->db->from(PRIVACYOPTION);
            $this->db->order_by('DisplayOrder');
            $option_query = $this->db->get();
            if ($option_query->num_rows()) {
                $data['DefaultOptions'] = $option_query->result_array();
            }
        }

        if ($cache_support) {
            $this->cache->save('privacy_' . $user_id, $data, CACHE_EXPIRATION);
        }

        return $data;
    }

    function get_excluding_privacy_options() {
        $exclude_privacy_options = [];

        if ($this->settings_model->isDisabled(1)) { // If group module is disabled
            $exclude_privacy_options[] = 'add_in_group';
            $exclude_privacy_options[] = 'group_invite';
        }

        if ($this->settings_model->isDisabled(10)) { // If friend module is disabled
            $exclude_privacy_options[] = 'friend_request';
            $exclude_privacy_options[] = 'view_friends';
        }


        if ($this->settings_model->isDisabled(25)) { // If message module is disabled
            $exclude_privacy_options[] = 'message';
        }

        return $exclude_privacy_options;
    }

    function change_label_data_for_module($result) {

        if (!$result || !is_array($result)) {
            return $result;
        }

        $min_privacy_val = '';
        $privacy_to_check = array('network', 'friend');
        if (!$this->settings_model->isDisabled(10)) { // If friend module is enabled
            $min_privacy_val = 'friend';
            $privacy_to_check = array('network');
        }

        foreach ($result as $key => $row) {
            foreach (['Low', 'Medium', 'High', 'Value'] as $privacyLevel) {
                if (!isset($row[$privacyLevel])) {
                    continue;
                }

                if (in_array($row[$privacyLevel], $privacy_to_check)) {
                    $row[$privacyLevel] = $min_privacy_val;
                }
            }
            $result[$key] = $row;
        }
        return $result;
    }

    /**
     * Function Name: get_privacy_label
     * @param privacy
     * Description: get privacy label of (low / medium / high) options
     */
    function get_privacy_label($privacy) {
        $this->db->select('Key');
        if ($privacy == 'low') {
            $this->db->select('LowPrivacyOption as Value');
        } else if ($privacy == 'medium') {
            $this->db->select('MediumPrivacyOption as Value');
        } else if ($privacy == 'high') {
            $this->db->select('HighPrivacyOption as Value');
        }
        $query = $this->db->get(PRIVACYLABEL);
        if ($query->num_rows()) {
            $result = $query->result_array();
            $result = $this->change_label_data_for_module($result);

            return $result;
        } else {
            return array();
        }
    }

    /**
     * [save_news_feed_setting description]
     * @param  [int] $user_id           [User ID]
     * @param  [array] $news_feed_setting [Array of news feed setting option and value]
     */
    function save_news_feed_setting($user_id, $news_feed_setting) {
        $insert_batch = array();
        $cache_keys = array();

        if ($news_feed_setting) {
            foreach ($news_feed_setting as $option) {
                $insert_batch[] = array('UserID' => $user_id, 'Key' => $option['Key'], 'Value' => $option['Value'], 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                $cache_keys[$option['Key']] = $option['Value'];
            }
        }     

        if ($insert_batch) {
            $this->db->insert_on_duplicate_update_batch(USERNEWSFEEDSETTING, $insert_batch);
        }

        if (CACHE_ENABLE) {
            //echo "cach";
            $this->cache->save('nfs_' . $user_id, $cache_keys, CACHE_EXPIRATION);
        }
    }

    /**
     * [news_feed_setting_details Used to get user setting for news feed]
     * @param  [int]  $user_id  [User ID]
     * @param  boolean $no_cache [description]
     * @return [array]            [array of user setting for news feed]
     */
    function news_feed_setting_details($user_id, $no_cache = false) {
        //default_data
        $data = array();
        
        if (!$data) {
            $data = array("g" => 0,
                "e" => 0,
                "es" => 0,
                "gs" => 0,
                "p" => 0,
                "ps" => 0,
                "r" => 0,
                "rm" => 0,
                "s" => 0,
                "m" => 0);
        }
        return $data;
        
        if (CACHE_ENABLE && !$no_cache) {
            $data = $this->cache->get('nfs_' . $user_id);
            if ($data) {
                return $data;
            }
        }

        $this->db->select('Key, Value');
        $this->db->where("UserID", $user_id);
        $query = $this->db->get(USERNEWSFEEDSETTING);
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $result = $query->result_array();
            foreach ($result as $row) {
                $data[$row['Key']] = $row['Value'];
            }
        }
        if (CACHE_ENABLE && !$no_cache) {
            $this->cache->save('nfs_' . $user_id, $data, CACHE_EXPIRATION);
        }
        if (!$data) {
            $data = array("g" => 0,
                "e" => 0,
                "es" => 0,
                "gs" => 0,
                "p" => 0,
                "ps" => 0,
                "r" => 0,
                "rm" => 0,
                "s" => 0,
                "m" => 0);
        }
        return $data;
    }

    function get_default_privacy($user_id) {
        $default_privacy = $this->get_value($user_id, 'default_post_privacy');
        if ($default_privacy == 'self') {
            return 4;
        } else if ($default_privacy == 'friend') {
            return 3;
        } else if ($default_privacy == 'network') {
            return 2;
        } else {
            return 1;
        }
    }
}
