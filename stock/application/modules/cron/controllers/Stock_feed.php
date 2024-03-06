<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Stock_feed extends Common_Api_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo "Welcome";die();
    }

    /**
     * It is used to get all stock list
     */
    public function stock_list_get() {
         $this->load->helper('queue');
        add_data_in_queue(array('action' => 'stock_list'),'stock_feed');
        /*
        $this->load->model("cron/Stock_feed_model");
        $this->Stock_feed_model->stock_list();
         */
    }

    /**
     * It is used to update stock live price
     */
    public function update_stock_latest_quote_get() {

        $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
        $hour = $d->format('H');
        $minute = $d->format('i');
        if($hour > 16 || $hour < 9) {
            return true;
        } else if(($hour == 16 && $minute > 5) || ($hour == 9 && $minute < 14)) {
            return true;
        }       

        $this->load->helper('queue');
        add_data_in_queue(array('action' => 'update_stock_latest_quote'),'stock_feed');
        /*
       $this->load->model("cron/Stock_feed_model");
       $this->Stock_feed_model->update_stock_latest_quote();*/
      
   }

   /**
     * It is used to get stock day history time wise
     */
    public function stock_historical_data_minute_wise_get() {
        $data = array();
        $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
        $data['to_date'] =  $d->format('Y-m-d H:i:00');

        $hour = $d->format('H');
        $minute = $d->format('i');
        if($hour > 15 || $hour < 9) {
            return true;
        } else if(($hour == 15 && $minute > 35) || ($hour == 9 && $minute < 15)) {
            return true;
        }

        
        $d->sub(new DateInterval('PT5M')); // 5 min before from current time
        $data['from_date']  =  $d->format('Y-m-d H:i:00');       

        $data['action'] =  'stock_historical_data_minute_wise';        
        $this->load->helper('queue');
        add_data_in_queue($data,'stock_feed');
       /* 
        $this->load->model("cron/Stock_feed_model");
        $this->Stock_feed_model->stock_historical_data_minute_wise($data);     
          */
    }

    /**
     * It is used to get stock yearly history day wise
     */
    public function stock_historical_data_day_wise_get() { die;//We will achive same data by update_stock_latest_quote
        $data = array();
        $data['action'] =  'stock_historical_data_day_wise';
        $data['from_date'] =  format_date('today', 'Y-m-d');
        $data['to_date'] =  format_date('today', 'Y-m-d');

        //echo 'from_date => '.$data['from_date'].' to_date => '.$data['to_date'];die;
       // log_message('error', 'Stock Historical Data Day Wise Cron Executed at: '.format_date());
        $this->load->helper('queue');
        add_data_in_queue($data,'stock_feed');
       /*
        $this->load->model("cron/Stock_feed_model");
        $this->Stock_feed_model->stock_historical_data_day_wise($data);
        */        
    }

    /**
     * It is used to get all holiday list
     */
    public function holiday_list_get() {
        $this->load->helper('queue');
       add_data_in_queue(array('action' => 'holiday_list'),'stock_feed');
       /*
       $this->load->model("cron/Stock_feed_model");
       $this->Stock_feed_model->holiday_list();
        */
   }

    /**
    * Stock entry minutes wise via socket
    */
    public function stock_data_socket_get() {
           
        $data = array();
        $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
        $data['to_date'] =  $d->format('Y-m-d H:i:00');

        $hour = $d->format('H');
        $minute = $d->format('i');
        if($hour > 15 || $hour < 9) {
            return true;
        } else if(($hour == 15 && $minute > 35) || ($hour == 9 && $minute < 15)) {
            return true;
        }
           

        $d->sub(new DateInterval('PT2M')); // 5 min before from current time
        $data['from_date']  =  $d->format('Y-m-d H:i:00'); 

        $data['action'] =  'stock_data_socket'; 
        $this->load->helper('queue');
        add_data_in_queue($data,'stock_feed');
       /* 
        $this->load->model("cron/Stock_feed_model");
        $this->Stock_feed_model->stock_historical_data_minute_wise($data);     
          */
    }

    public function update_last_close_price_get()
    {   
        $data =array();
        $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
        $hour = $d->format('H');
        $minute = $d->format('i');
        if($hour > 15 || $hour < 9) {
            return true;
        } else if(($hour == 15 && $minute > 35) || ($hour == 9 && $minute < 15)) {
            return true;
        }
        $data['current_date_time'] =  $d->format('Y-m-d H:i:00');
        $this->load->model('Stock_feed_model');
        $this->Stock_feed_model->update_last_close_price($data);
        $data['action'] =  'update_last_close_price';
        $data['current_date_time'] =  $d->format('Y-m-d H:i:00');
        $this->load->helper('queue');
        add_data_in_queue($data,'stock_feed');
    }

}