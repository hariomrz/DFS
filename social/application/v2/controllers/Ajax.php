<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Ajax extends Common_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function change_language() {
        $data = $this->input->post();
        $user_guid = safe_array_key($data, 'UserGUID');
        $lang = safe_array_key($data, 'lang');
        if ($user_guid && $lang) {
            $this->db->set('Language', $lang);
            $this->db->where('UserGUID', $user_guid);
            $this->db->update(USERS);
        }
        $this->session->set_userdata('language', $lang);
        echo lang('menu_dashboard');
    }

    public function change_autoplay() {
        $data = $this->input->post();
        $user_guid = safe_array_key($data, 'UserGUID');
        $auto_play = safe_array_key($data, 'autoplay');
        if ($user_guid && $auto_play) {
            $user_id = get_detail_by_guid($user_guid,3);
            if($user_id) {
                $this->db->set('VideoAutoplay', $autoplay);
                $this->db->where('UserID', $user_id);
                $this->db->update(USERDETAILS);
            }
        }
    }
}
