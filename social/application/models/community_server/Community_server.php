<?php

class Community_server extends CI_Model {

    protected $_api_key = '1510045532907123456';
    protected $_server_url = 'http://www.cs-vh.com/api/';
    protected $_api_email = 'sureshp@vinfotech.com';

    public function __construct() {
        $this->init();
    }

    public function init() {
        include_once APPPATH . 'models/community_server/Zend/Loader/Autoloader.php';
        include_once APPPATH . 'models/community_server/Zend/Loader.php';
        $zf_loader_instance = Zend_Loader::registerAutoload();
        if (ENVIRONMENT == 'testing') {
            $this->_api_email = 'sureshp@vinfotech.com';
            $this->_server_url = 'http://community.vcommonsocial.com/api/';
            $this->_api_key = '1510045532907123456';
        }
    }

    public function send_to_server($url, $data) {
        if (empty(COMMUNITY_ENABLED)) {
            return;
        }

        $data = array_merge($data, array(
            'Api_Key' => $this->_api_key,
            'Api_Email' => $this->_api_email,
        ));

        $url = $this->_server_url . $url;

        $zf_client = new Zend_Http_Client();
        $zf_client->setMethod(Zend_Http_Client::POST);

        $zf_client->setUri($url);

        foreach ($data as $fieldName => $fieldValue) {
            $zf_client->setParameterPost($fieldName, $fieldValue);
        }

        $response = $zf_client->request();
        $body = $response->getBody();
        $raw_data = $response->getRawBody();
        $status = $response->getStatus();
        $headers = $response->getHeaders();

        $body = json_decode($body, TRUE);
        
        $test;
        
        return $body;
    }

}

?>