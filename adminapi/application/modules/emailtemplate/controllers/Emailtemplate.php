<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Emailtemplate extends MYREST_Controller {
     public $finance_lang;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Emailtemplate_model');
		$_POST = $this->input->post();
	}

	public function index()
	{
		$this->load->view('layout/layout', $this->data, FALSE);
	}

    public function get_all_emailtemplate_post()
    {
        $result = $this->Emailtemplate_model->get_all_emailtemplate();

        if($this->input->post('csv'))
        {
            

            if(!empty($result['result'])){
                $result =$result['result'];
                $header = array_keys($result[0]);
                $camelCaseHeader = array_map("camelCaseString", $header);
                $result = array_merge(array($camelCaseHeader),$result);
                $this->load->helper('csv');
                
                $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                $this->api_response_arry['status']          = TRUE;
                $this->api_response_arry['message']         = '';
                $this->api_response_arry['data']            = array_to_csv($result);
                $this->api_response();
            }
            //$this->load->helper('download');
            //$data = $this->dbutil->csv_from_result($query);
            //$data = "Created on " . format_date('today', 'Y-m-d') . "\n\n" . "From Date $from_date\nTo Date $to_date\n\n" . html_entity_decode($data);
            //$name = 'file.csv';
            //force_download($name, $data);
            
        }
        else
        {
            $this->api_response_arry['service_name']  = "get_all_withdrawal_request";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']          = $result;
            $this->api_response();
        }      
    }

    public function update_status_post()
    {
        $this->form_validation->set_rules('email_template_id', 'Template id', 'trim|required');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');

        
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }
        $status = $this->input->post("status");


        $post = array('email_template_id'=>$this->input->post("email_template_id"),
                                "status"=>$this->input->post("status"),
                             ); 

        $result = $this->Emailtemplate_model->update_emailtemplate_status($post);

        if($result)
        {
            $this->api_response_arry['service_name']  = "update_status_post";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']          = $result;
            $this->api_response_arry['message']          = 'Status updated successfully';

            $this->api_response();
        }

        $this->api_response_arry['service_name']  = "update_status_post";
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
    }

    public function view_template_post()
    {
        $this->api_response_arry['service_name']  = "update_status_post";
        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
    }

}
/* End of file User.php */
/* Location: ./application/controllers/User.php */