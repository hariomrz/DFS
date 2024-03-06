<?php
class Shorturl extends Common_Api_Controller {

    function __construct() {
        parent::__construct();      
    }

    public function index_get() {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    public function index_post() {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    public function get_shortened_url_post() {
        $post_data = $this->input->post();
        $this->load->model("shorturl/Shorturl_model");
        $result = $this->Shorturl_model->get_shortened_url($post_data);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    public function save_shortened_url_post() {
        $post_data = $this->input->post();
        $this->load->model("shorturl/Shorturl_model");
        $result = $this->Shorturl_model->save_shortened_url($post_data);
        
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }
}