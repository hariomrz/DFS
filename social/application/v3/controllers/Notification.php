<?php

defined('BASEPATH') OR die('Direct access not allowed.');

/**
 * Description of notification
 *
 * @author nitins
 */
class Notification extends Common_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function zencoder()
    {
        $this->load->helper('file');
        $data = file_get_contents('php://input');
        write_file(APPPATH . '../upload/zencoder.txt', $data, 'a');
    }

    public function settings()
    {
        if (!$this->session->userdata('UserGUID'))
        {
            redirect('/');
        }
        $this->data['title'] = 'Notification Settings';
        $this->page_name = 'myaccount';
        $this->data['pname'] = 'myaccount';
        $this->data['sub'] = 'notification';
        $this->data['content_view'] = 'notifications/settings';

        $this->load->view($this->layout, $this->data);
    }

    public function testPushNotification()
    {
//        $headers = [
//                        'Authorization' => 'AIzaSyAXWHX3AbJjNQFtVd97s2395hhVuPBg4nQ',
//                        'Content-Type' => 'application/json',
//                        'Encryption' => 'WqqJqRBRfJZt5uNQ9JJaEA',
//                        'Crypto-Key' => 'BOSC5oWzKExloYyjbGw83DeM9oiqFfh9McLU-hvjqQNtlysBrJlmpYY5fCeYcE17jZzu70oZ7Ivt7cxqP2hbW6c',
//                        'Content-Encoding' => 'aesgcm'
//        ];
//
//        $jsondata = [
//                        'to' => 'dnb_vKfZrGo:APA91bHpsVHP_lkzkRE8jtBqTgOhxSWTBzlSeuKCaEkvc76-MZ0D4_6IyF-vRqjLr-_WLXBZ7c-Y8GOdQLEA6VxEgPsXwvX5UhushoUmIjXgN7dzREdy13DrOb1X0mqCQc3JnmhZ1yFw',
//                        'raw_data' => 'Zxr5CDOJXbUs4mhUJAa3aQhq+wwFXxjKnUA='
//        ];
//
//        $data = ExecuteCurl('https://android.googleapis.com/gcm/send', $jsondata, '',$headers);
//        echo json_encode($data);
//        die;
        // API access key from Google API's Console
        define('API_ACCESS_KEY', 'AIzaSyAXWHX3AbJjNQFtVd97s2395hhVuPBg4nQ');
        $registrationIds = array("dnb_vKfZrGo:APA91bHpsVHP_lkzkRE8jtBqTgOhxSWTBzlSeuKCaEkvc76-MZ0D4_6IyF-vRqjLr-_WLXBZ7c-Y8GOdQLEA6VxEgPsXwvX5UhushoUmIjXgN7dzREdy13DrOb1X0mqCQc3JnmhZ1yFw");
        // prep the bundle
        $msg = array
            (
            'message' => 'here is a message. message',
            'title' => 'This is a title. title',
            'subtitle' => 'This is a subtitle. subtitle',
            'tickerText' => 'Ticker text here...Ticker text here...Ticker text here',
            'vibrate' => 1,
            'sound' => 1,
            'largeIcon' => 'large_icon',
            'smallIcon' => 'small_icon'
        );
        $fields = array
            (
            'registration_ids' => $registrationIds,
            'data' => $msg
        );

        $headers = array
            (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE)
        {
            echo 'Push msg send failed in curl: ' . curl_error($ch);
        } else
        {
            echo $result;
        }
        curl_close($ch);
    }
}