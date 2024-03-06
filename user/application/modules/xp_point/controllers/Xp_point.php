<?php


class Xp_point extends Common_Api_Controller {    
    function __construct() {
        parent::__construct();
        $a_xp_point = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;
        $allow_coin_system = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if(!$allow_coin_system || !$a_xp_point)
        {
            $this->api_response_arry['response_code'] = 500;
            $this->api_response_arry['global_error']  ="Module not activated." ;
            $this->api_response();
        }  
    }

    /**
     * This is used to get reward list
     */
    function get_reward_list_post() {
        $reward_cache_key = 'xp_reward_list';
        $reward_list = $this->get_cache_data($reward_cache_key);
        if (!$reward_list) {
            $this->load->model('Xp_point_model');
            $reward_list = $this->Xp_point_model->get_reward_list();
            $this->set_cache_data($reward_cache_key, $reward_list, REDIS_30_DAYS);
        }
        $reward_list = array_column($reward_list,null,'level_number');
        $user_xp = $this->get_user_xp($this->user_id);
        $next_level_data = $reward_list[$user_xp['level_number']+1];

        $this->api_response_arry['data']['reward_list'] = $reward_list;
        $this->api_response_arry['data']['user_xp'] = $user_xp;
        $this->api_response_arry['data']['next_level'] = $next_level_data;
        $this->api_response();
    }

    private function get_user_xp($user_id) {
        $user_xp_cache_key = 'user_xp_'.$user_id;
        $user_xp = $this->get_cache_data($user_xp_cache_key);
        if (empty($user_xp)) {
            $this->load->model('Xp_point_model');
            $user_xp = $this->Xp_point_model->get_user_xp($user_id);

            $user_xp['badge_name'] = '';
            $user_xp['badge_icon'] = '';
            $user_xp['badge_id'] = '';
            if(!empty($user_xp['level_number'])) {
                $user_badge = $this->Xp_point_model->get_user_badge($user_xp['level_number'], $user_id);
                if(!empty($user_badge)) {
                    $user_xp = array_merge($user_xp,$user_badge);
                }
            }

            $this->set_cache_data($user_xp_cache_key, $user_xp, REDIS_30_DAYS);
        }

        if(!isset($user_xp['point'])) {
            $user_xp['point'] = 0;
        }
        if(!isset($user_xp['level_number'])) {
            $user_xp['level_number'] = 0;
        }
        return $user_xp;
    }

    /**
     * This is used to get activity list
     */
    function get_activity_list_post() {
        $activity_cache_key = 'xp_activity_list';
        $activity_list = $this->get_cache_data($activity_cache_key);
        if (!$activity_list) {
            $this->load->model('Xp_point_model');
            $activity_list = $this->Xp_point_model->get_activity_list();
            $this->set_cache_data($activity_cache_key, $activity_list, REDIS_30_DAYS);
        }

        $this->api_response_arry['data']['activity_list'] = $activity_list;
        $this->api_response();
    }

    /**
     * This is used to get user xp card
     */
    function get_user_xp_card_post() {
        $post_data = $this->post();
        $user_id    = isset($post_data['user_id']) ? $post_data['user_id'] : $this->user_id;

        $user_xp_card_cache_key = 'user_xp_card_'.$user_id;
        $user_xp_card = $this->get_cache_data($user_xp_card_cache_key);
        if (empty($user_xp_card)) {
            $this->load->model('Xp_point_model');

            $user_xp_card = $this->get_user_xp($user_id);
            if(!isset($user_xp_card['level_id'])) {
                $user_xp_card['level_id'] = 0;
            }
            if(!isset($user_xp_card['level_number'])) {
                $user_xp_card['level_number'] = 0;
            }
            

            $next_level = $this->Xp_point_model->get_next_level($user_xp_card['level_id']);
            if(!empty($next_level)) {
                $user_xp_card = array_merge($user_xp_card,$next_level);
            }

            //get max level and end point
            $max_level = $this->Xp_point_model->get_max_level();
            
            if(!empty($max_level)) {
                $user_xp_card = array_merge($user_xp_card,$max_level);
            }
            $this->set_cache_data($user_xp_card_cache_key, $user_xp_card, REDIS_30_DAYS);
        }

        $this->api_response_arry['data']['user_xp_card'] = $user_xp_card;
        $this->api_response();
    }

    /**
     * This is used to get user xp point history
     */
    function get_user_xp_history_post() {
        $this->load->model('Xp_point_model');
        $post_data = $this->post();
        $page_no    = isset($post_data['page_no']) ? $post_data['page_no'] : 1;
        $limit      = isset($post_data['page_size']) ? $post_data['page_size'] : 10;
        $offset     = get_pagination_offset($page_no, $limit);
        $result_data  =$this->Xp_point_model->get_user_xp_history($offset, $limit);
        $this->api_response_arry['data']['history'] = $result_data;
        $this->api_response_arry['data']['user_xp'] = $this->get_user_xp($this->user_id);
        $this->api_response();
    }
}