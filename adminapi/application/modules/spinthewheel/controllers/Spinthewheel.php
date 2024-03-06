<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Spinthewheel extends MYREST_Controller {

    public $spinthewheel_source=322;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Spinthewheel_model');
        $_POST = $this->input->post();
    }

    /**
     * [wheel_slices_post description]
     * Summary :- wheel slices 
     * @return [type] [description]
     */
    public function wheel_slices_list_post()
    {
        $result = $this->Spinthewheel_model->get_wheel_slices_list();
        $this->api_response_arry['response_code']   = 200;
        $this->api_response_arry['data']            = $result;
        $this->api_response();
    }

    /**
     * [add_roles_post description]
     * Summary :- Use for insert add new wheel slices
     * @param   : Name ,Type,Win,ResultText
     * @return  [type]
     */
    public function add_wheel_slices_post()
    {
    
        $this->form_validation->set_rules('slice_name', 'Slice Name', 'trim|required|is_unique['.$this->db->dbprefix(SPIN_THE_WHEEL).'.slice_name]',array(
            'required'      => 'You have not provided %s.',
            'is_unique'     => 'This slice name is already as slices list.'
        ));
    
        $this->form_validation->set_rules('type', 'Type', 'trim|required');
        $this->form_validation->set_rules('win', 'Win', 'trim|required');
        $this->form_validation->set_rules('result_text', 'Result Text', 'trim|required');
        $this->form_validation->set_rules('probability', 'Probability', 'trim|required|min_length[0]|max_length[100]');
        $this->form_validation->set_rules('status', 'status', 'trim|required');
        $this->form_validation->set_rules('cash_type', 'Cash Type', 'trim|required');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required');
    
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $data = $this->input->post(); 

        
        $check_slice =  $this->Spinthewheel_model->get_single_row(
             "amount",
             SPIN_THE_WHEEL,
             array("status"=>'1',"slice_name"=> $data['slice_name'])
        );

        if(!empty($check_slice)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = "Slice name ".$data['slice_name'].' is already active';
            $this->api_response(); 
        }
        
        $data['status'] = '1';

        $insert_id = $this->Spinthewheel_model->add_slice($data);
        if($insert_id)
        {   
            $this->api_response_arry['service_name']    = 'add_wheel_slices';
            $this->api_response_arry['message']         = 'Wheel Slice added successfully!';
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['data']            = array('id'=>$insert_id);
            $this->api_response();  
        }

        $this->api_response_arry['service_name']    = 'add_wheel_slices';
        $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['global_error']    = "Slice not added";
        $this->api_response();  

    }


    /**
     * [slices_update description]
     * Summary :- Use for update slices update
     * @param  :- 
     * @return  [type]
     */
    public function slices_update_post()
    {
        //$data = array();
        /*$this->form_validation->set_rules('spinthewheel_id', 'Spinthewheel ID', 'trim|required');

        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }*/

        $data = $this->input->post();
        /*$check_spin_slice =  $this->Spinthewheel_model->get_single_row(
             "amount",
             SPIN_THE_WHEEL,
             array("spinthewheel_id"=> $data['spinthewheel_id'])
        );
        
        if(empty($check_spin_slice)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = 'Spinthewheel ID not exists';
            $this->api_response();
        }*/

        $this->db->update_batch(SPIN_THE_WHEEL,$data,'spinthewheel_id');

        //$this->Spinthewheel_model->update_slice($data['spinthewheel_id'],$slice_update_data);
        $this->api_response_arry['service_name']    = 'slices_update';
        $this->api_response_arry['message']         = 'Slices update successfully!';
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response_arry['data']            = array();
        $this->api_response();

    }

    /**
     * [setting_update description]
     * Summary :- Use for update slices update
     * @param   : *
     * @return  [type]
     */
    public function get_setting_post()
    {
        $setting_array = array();

        $setting_array['colorArray'] = array("#364C62", "#F1C40F", "#E67E22", "#E74C3C", "#ECF0F1", "#95A5A6", "#16A085", "#27AE60", "#2980B9", "#8E44AD", "#2C3E50", "#F39C12", "#D35400", "#C0392B", "#BDC3C7","#1ABC9C", "#2ECC71", "#E87AC2", "#3498DB", "#9B59B6", "#7F8C8D");

        $setting_array['svgWidth'] = 1024;
        $setting_array['svgHeight'] = 1024;
        $setting_array['wheelStrokeColor'] = "#D0BD0C";
        $setting_array['wheelStrokeWidth'] = 18;
        $setting_array['wheelSize'] = 900;
        $setting_array['wheelTextOffsetY'] = 80;
        $setting_array['wheelTextColor'] = "#EDEDED";
        $setting_array['wheelTextSize'] = "2.3em";
        $setting_array['wheelImageOffsetY'] = 40;
        $setting_array['wheelImageSize'] = 50;
        $setting_array['wheelImageSize'] = 50;
        $setting_array['centerCircleSize'] = 360;
        $setting_array['centerCircleStrokeColor'] = "#F1DC15";
        $setting_array['centerCircleStrokeWidth'] = 12;
        $setting_array['centerCircleFillColor'] = "#EDEDED";
        $setting_array['centerCircleImageUrl'] = "media/logo.png";
        $setting_array['centerCircleImageWidth'] = 400;
        $setting_array['centerCircleImageHeight'] = 400;
        $setting_array['segmentStrokeColor'] = "#E2E2E2";
        $setting_array['segmentStrokeWidth'] = 4;
        $setting_array['centerX'] = 512;
        $setting_array['centerY'] = 512;
        $setting_array['hasShadows'] = false;
        $setting_array['numSpins'] = 3;
        $setting_array['spinDestinationArray'] = array();
        $setting_array['minSpinDuration'] = 3;
        $setting_array['gameOverText'] = "I HOPE YOU ENJOYED SPIN WHEEL. :)";
        $setting_array['invalidSpinText'] = "INVALID SPIN. PLEASE SPIN AGAIN.";
        $setting_array['introText'] = "YOU HAVE TO<br>SPIN IT <span style='color:#F282A9;'>2</span> WIN IT!";
        $setting_array['hasSound'] = true;
        $setting_array['gameId'] = "9a0232ec06bc431114e2a7f3aea03bbe2164f1aa";
        $setting_array['clickToSpin'] = true;
        $setting_array['spinDirection'] = "ccw";

    }

    function get_top_gainers_post()
    {
        $this->form_validation->set_rules('filter_by', 'Filter by', 'trim|required');
    
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        if(!in_array($post['filter_by'],['coins','cash','bonus']))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array();
            $this->api_response_arry['global_error'] = "Pleaser enter valid filter";
            $this->api_response(); 
        }
        //get spin counts
       $counts = $this->Spinthewheel_model->get_entity_counts($post);
       $top_gainers = $this->Spinthewheel_model->get_top_gainers($post);
        
         $categories = array_column($top_gainers,'full_name');
         $series = array();
         $coin_arr= array();
         $bonus_arr= array();
         $cash_arr = array();
         foreach ($top_gainers as $row)
         {
            $coin_arr[] = (int)$row['coins'];
            $bonus_arr[] = (float)$row['bonus'];
            $cash_arr[] = (float)$row['cash'];
         }

         $series[] = array('name' => "Coin",'data' => $coin_arr,"color" => '#000000',);
         $series[] = array('name' => "Bonus",'data' => $bonus_arr,"color" => '#E55D6E');
         $series[] = array('name' => "Cash",'data' => $cash_arr,"color" => '#35A7FF',);

        //  echo '<pre>';
        //  print_r($categories);
        //  print_r($series);
        //  die();

         $this->api_response_arry['service_name']    = 'get_top_gainers';
         $this->api_response_arry['data']['series']  = $series;
         $this->api_response_arry['data']['categories']  = $categories;
         $this->api_response_arry['data']['counts']  = $counts;
         $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
         $this->api_response();

    }


    function get_leaderboard_by_category_post()
    {
        $this->form_validation->set_rules('filter_by', 'Filter by', 'trim|required');
    
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post = $this->input->post();
        
        $result =  $this->Spinthewheel_model->get_leaderboard_by_category($post);
      
        $this->api_response_arry['data']  = $result;
        $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
        $this->api_response();
    }

}
/* End of file Spinthewheel.php */
/* Location: ./application/controllers/Spinthewheel.php */
