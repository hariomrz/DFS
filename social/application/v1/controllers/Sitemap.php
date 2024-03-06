<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sitemap extends Common_Controller {
        public $page_name = 'sitemap';
    public function __construct() {
            parent::__construct();
            // We load the url helper to be able to use the base_url() function
            $this->load->helper('url');     
            $this->load->model('sitemap_model');
            $this->api_server_url = 'http://api.bhopu.com/';
    }

    public function new() {
        $post_type = array(1, 2);
        $this->sitemap_model->add($this->api_server_url.'sitemap/general', get_current_date('%Y-%m-%d %H:%i:%s'), 'weekly', 0.9);
        // $total_activities = $this->sitemap_model->activity_url($post_type, TRUE);
        // echo "<pre>";print_r($total_activities);echo "-=-=total_activities";
        // if ($total_activities > 50000)
        // {
            
        // }
        $this->sitemap_model->add($this->api_server_url.'sitemap/post', get_current_date('%Y-%m-%d %H:%i:%s'), 'daily', 0.9);
        $this->sitemap_model->output('sitemapindex');
    }
    
    /**
     * Generate a sitemap index file
     * More information about sitemap indexes: http://www.sitemaps.org/protocol.html#index
     */
    public function index() {
        $post_type = array(1, 2);

        $total_activities = $this->sitemap_model->activity_url($post_type, TRUE, NULL);

        $total_pages = $total_activities/49000;
        $total_pages = ceil($total_pages);

        $this->sitemap_model->add($this->api_server_url.'sitemap/general', date('c'), 'weekly', 0.9);
        for ($i=1; $i <= $total_pages; $i++)
        {
            $this->sitemap_model->add($this->api_server_url.'sitemap/post'.'/'.$i, date('c'), 'daily', 0.9);
        }

        // $this->sitemap_model->add($this->api_server_url.'sitemap/post', get_current_date('%Y-%m-%d %H:%i:%s'), 'daily', 0.9);
        $this->sitemap_model->output('sitemapindex');
    }   
        
        /**
     * Generate a sitemap for static urls
     */
    public function general() {
        $this->sitemap_model->add(DOMAIN, date('c'), 'daily', 1);
        $this->sitemap_model->add(DOMAIN.'/about', date('c'), 'daily', 0.7);
        $this->sitemap_model->add(DOMAIN.'/newsletter', date('c'), 'daily', 0.7);
        $this->sitemap_model->add(DOMAIN.'/internal', date('c'), 'daily', 0.7);
        $this->sitemap_model->add(DOMAIN.'/poll', date('c'), 'daily', 0.9);
        $this->sitemap_model->output();
    }
    
    
    /**
     * Generate a sitemap for articles
     */
    public function articles() {
            $this->sitemap_model->activity_url();
            $this->sitemap_model->output();
    }
        
        /**
     * Generate a sitemap for post
     */
    public function post() {

        $link = $_SERVER['PHP_SELF'];
        $link_array = explode('/',$link);
        $page = end($link_array);

        $post_type = array(1, 2);
        $this->sitemap_model->activity_url($post_type, FALSE, $page);
        $this->sitemap_model->output();
    }
        
        /**
     * Generate a sitemap for event
     */
    public function events() {
            $this->sitemap_model->event_url();
            $this->sitemap_model->output();
    }
        
        /**
     * Generate a sitemap for community
     */
    public function community() {
            $this->sitemap_model->community_url();
            $this->sitemap_model->output();
    }
        
        /**
     * Generate a sitemap for community
     */
    public function groups() {
            $this->sitemap_model->group_url();
            $this->sitemap_model->output();
    }
    
}