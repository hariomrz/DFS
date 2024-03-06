<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sitemap extends Common_Controller {
        public $page_name = 'sitemap';
	public function __construct() {
            parent::__construct();
            // We load the url helper to be able to use the base_url() function
            $this->load->helper('url');		
            $this->load->model('sitemap_model');
	}
	
	/**
	 * Generate a sitemap index file
	 * More information about sitemap indexes: http://www.sitemaps.org/protocol.html#index
	 */
	public function index() {
            $this->sitemap_model->add(base_url('sitemap/general.xml'), NULL, 'daily', 0.9);
            $this->sitemap_model->add(base_url('sitemap/events.xml'), NULL, 'daily', 0.9);
            $this->sitemap_model->add(base_url('sitemap/community.xml'), NULL, 'daily', 0.9);
            $this->sitemap_model->add(base_url('sitemap/group.xml'), NULL, 'daily', 0.9);
            $this->sitemap_model->add(base_url('sitemap/article.xml'), NULL, 'daily', 0.9);
            $this->sitemap_model->add(base_url('sitemap/post.xml'), NULL, 'daily', 0.9);
            //$this->sitemap_model->add(base_url('sitemap/articles'), date('Y-m-d', time()));
            $this->sitemap_model->output('sitemapindex');
	}	
        
        /**
	 * Generate a sitemap for static urls
	 */
	public function general() {
            $this->sitemap_model->add(base_url(), NULL, 'daily', 1);                
            $this->sitemap_model->add(base_url('signin'), NULL, 'daily', 0.7);
            $this->sitemap_model->add(base_url('signup'), NULL, 'daily', 0.7);
            $this->sitemap_model->add(base_url('forgot-password'), NULL, 'daily', 0.7);            
            $this->sitemap_model->add(base_url('article'), NULL, 'daily', 0.9); 
            $this->sitemap_model->add(base_url('group/discover'), NULL, 'daily', 0.9);
            $this->sitemap_model->add(base_url('journal'), NULL, 'daily', 0.9);
            $this->sitemap_model->add(base_url('community/discover'), NULL, 'daily', 0.9);
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
            $post_type = array(1,2,3,7);
            $this->sitemap_model->activity_url($post_type);
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