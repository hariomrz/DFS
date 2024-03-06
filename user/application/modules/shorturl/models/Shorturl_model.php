<?php
class Shorturl_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Used for get contest short url
     * @param array $post_data
     * @return array
     */
    public function get_shortened_url($post_data) {
        
        $this->db->select('SU.*');
        $this->db->from(SHORT_URLS . ' SU');
        $this->db->where('SU.user_id', $this->user_id);

        if (!empty($post_data['url_type'])) {
            $this->db->where('SU.url_type', $post_data['url_type']);
        }
        if (!empty($post_data['url_type_id'])) {
            $this->db->where('SU.url_type_id', $post_data['url_type_id']);
        }

        if (!empty($post_data['source_type'])) {
            $this->db->where('SU.source_type', $post_data['source_type']);
        }

        if (!empty($post_data['shortened_id'])) {
            $this->db->where('SU.shortened_id', $post_data['shortened_id']);
        }

        $result = $this->db->get()->result_array();
        return $result;
    }

    /**
     * Used for save contest share short url
     * @param array $post_data
     * @return array
     */
    public function save_shortened_url($post_data) {
        $current_date = format_date();
        $url_data = $post_data['url_data'];
        $return = array();
        foreach ($url_data as $key => $value) {
            $url_data = $this->is_url_exist($value['url'], $this->user_id);
            if (!$url_data) {
                $ins_data = array();
                $ins_data['shortened_id'] = generateRandomString(6);
                $ins_data['url'] = $value['url'];
                $ins_data['user_id'] = $this->user_id;
                $ins_data['url_type'] = isset($value['url_type']) ? $value['url_type'] : '';
                $ins_data['url_type_id'] = isset($value['url_type_id']) ? $value['url_type_id'] : 0;
                $ins_data['source_type'] = isset($value['source_type']) ? $value['source_type'] : '';
                $ins_data['added_date'] = $current_date;
                $insert_arr[] = $ins_data;
                $return[] = $ins_data;
            } else {
                $return[] = $url_data;
            }
        }

        if (!empty($insert_arr)) {
            $this->db->insert_batch(SHORT_URLS, $insert_arr);
        }

        return $return;
    }

    /**
     * Used for check url exist in db or not
     * @param string $url
     * @param int $user_id
     * @return array
     */
    private function is_url_exist($url, $user_id) {
        $this->db->select('SU.user_id,SU.shortened_id,SU.url,SU.url_type,SU.url_type_id,SU.source_type,SU.added_date');
        $this->db->from(SHORT_URLS . ' SU');
        $this->db->where('SU.url', $url);
        $this->db->where('SU.user_id', $user_id);
        $result = $this->db->get()->row_array();
        return $result;
    }
}
