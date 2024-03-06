<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Privacy extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();        
        $this->load->model(array('group/group_model', 'category/category_model', 'users/friend_model', 'activity/activity_model', 'favourite_model', 'subscribe_model', 'notification_model'));
    }

    /**
     * Function Name: save
     * @param Privacy
     * @param Options
     * Description: add / update privacy settings of user
     */
    function save_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $options = array();

        //array of valid values
        $valid_values = array('low', 'medium', 'high', 'customize');

        if ($this->form_validation->run('api/privacy/save') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $privacy = $data['Privacy'];
            $return['Message'] = lang('privacy_setting_updated');
            if (!in_array($privacy, $valid_values))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "privacy value");
            } else
            {
                if ($privacy == 'customize')
                {
                    $options = isset($data['Options']) ? $data['Options'] : array();
                    if (!$options)
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "privacy options");
                        $this->response($return);
                    }
                }

                $this->privacy_model->save($user_id, $privacy, $options);
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: details
     * Description: get the details of user privacy settings
     */
    function details_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $this->load->model(array('privacy/privacy_model'));
        $return['Data'] = $this->privacy_model->details($user_id,FALSE,FALSE); 

        $this->response($return);
    }

    /**
     * [save_news_feed_setting_post Used to save user setting for news feed]
     */
    function save_news_feed_setting_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            if ($this->form_validation->run('api/privacy/save_news_feed_setting') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $user_id = $this->UserID;
                $news_feed_setting = $data['news_feed_setting'];
                //print_r($news_feed_setting);die;
                $this->privacy_model->save_news_feed_setting($user_id, $news_feed_setting);
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    function news_feed_setting_details_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;

        $return['Data'] = $this->privacy_model->news_feed_setting_details($user_id);

        $this->response($return);
    }

    function clear_privacy_cache_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = isset($data['UserID']) ? $data['UserID'] : 0;
        if($user_id) {
            $this->load->model(array('privacy/privacy_model'));
            $this->privacy_model->set_privacy($user_id, 'customize');
            if (CACHE_ENABLE) {
                $this->cache->delete('privacy_' . $user_id);
            }
        }
        
        $this->response($return);
    }

}
