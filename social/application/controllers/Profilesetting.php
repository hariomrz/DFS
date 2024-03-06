<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Profilesetting extends Common_Controller
{
    public $page_name = 'profilesetting';
    public $dashboard = '';

    /**
     * Profilesetting constructor.
     */
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('UserGUID')) {
            redirect('/');
        }
        $this->data['ModuleID'] = 3;
        $this->data['ModuleEntityGUID'] = $this->session->userdata('UserGUID');
    }

    public function interest()
    {
        $this->data['title'] = 'Area of Interest';
        $this->page_name = 'profilesetting';
        $this->data['pname'] = 'profilesetting';
        $this->data['sub'] = 'interest';
        /*check module settings*/
        if ($this->settings_model->isDisabled(31)) {
            $this->data['content_view'] = 'module_disabled';
            $this->load->view($this->layout, $this->data);
            $this->output->_display();
            exit();
        }
        $this->load->model('users/user_model');
        $user_id = $this->session->userdata('UserID');
        $total_count = $this->user_model->get_user_intrest_count($user_id);
        if($total_count >= MINIMUM_SELECTION){
            redirect(base_url());
        }

        $this->data['content_view'] = 'profilesetting/interest';
        $this->load->library('user_agent');
        $refferer_url = $this->agent->referrer();
        $current_user_url = get_entity_url($this->session->userdata('UserID'));

        $this->data['redirect_url'] = site_url('profilesetting/follow_people');
        if ($refferer_url == $current_user_url) {
            $this->data['redirect_url'] = $current_user_url;
        }

        $this->load->view($this->layout, $this->data);
    }

    public function categories()
    {
        $this->load->model('users/user_model');
        $user_id = $this->session->userdata('UserID');
        $total_count = $this->user_model->check_category_membership($user_id);
        if($total_count >= MINIMUM_SELECTION){
            redirect(base_url());
        }
        $this->data['title'] = 'Suggested Categories';
        $this->page_name = 'profilesetting';
        $this->data['pname'] = 'profilesetting';
        $this->data['sub'] = 'categories';

        $this->data['content_view'] = 'profilesetting/categories';
        $this->load->library('user_agent');
        $refferer_url = $this->agent->referrer();
        $current_user_url = get_entity_url($this->session->userdata('UserID'));

        $this->data['redirect_url'] = site_url('profilesetting/top_contributors');
        if ($refferer_url == $current_user_url) {
            $this->data['redirect_url'] = $current_user_url;
        }

        $this->load->view($this->layout, $this->data);
    }

    public function follow_people(){

        $this->load->model('users/user_model');
        $user_id = $this->session->userdata('UserID');
        $total_count = $this->user_model->following_count($user_id);
        if($total_count >= MINIMUM_SELECTION){
            redirect(base_url());
        }

        $this->data['title'] = 'People you may follow';
        $this->page_name = 'profilesetting';
        $this->dashboard = '';
        $this->data['content_view'] = 'profilesetting/people_you_may_follow';
        $this->data['redirect_url'] = 'dashboard';
        $this->load->view($this->layout, $this->data);
    }
    public function top_contributors(){
        $this->load->model('users/user_model');
        $user_id = $this->session->userdata('UserID');
        $total_count = $this->user_model->following_count($user_id);
        if($total_count >= MINIMUM_SELECTION){
            redirect(base_url());
        }
        $this->data['title'] = 'Top Contributors';
        $this->page_name = 'profilesetting';
        $this->dashboard = '';
        $this->data['content_view'] = 'profilesetting/top_contributors';
        $this->data['redirect_url'] = 'dashboard';
        $this->load->view($this->layout, $this->data);
    }
}