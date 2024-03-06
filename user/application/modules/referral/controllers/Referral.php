<?php
class Referral extends Common_Api_Controller {

    public $limit = 10;

    function __construct() {
        parent::__construct();
        $this->load->model("Referral_model");
    }

    public function get_referral_post() {
        $post_data = $this->input->post();

        $refer_data = $this->Referral_model->get_referral_data($this->user_id, $post_data['offset'], $this->limit);
        foreach ($refer_data as &$value) {
            if ($value['friend_id'] != 0) {

                $contest_res = $this->http_post_request('contest/get_all_contest_of_user', array("user_id" => $value["friend_id"],"is_cron_data"=>"1"), false);
                $contest = $contest_res["data"];

                if (!empty($contest_res["data"])) {
                    $lineup_master_contest_ids = array_column($contest, 'lineup_master_contest_id');
                    $investData = $this->Referral_model->get_invested_amonunt($lineup_master_contest_ids, $value["friend_id"]);
                    $value['invested'] = $investData["total_real_invest"] ? $investData["total_real_invest"] : 0;
                } else {
                    $value['invested'] = 0;
                }
            } else {
                $value['invested'] = 0;
            }
            // print_r($investData);
        }
        // die;
        $output = array();
        $output['is_load_more'] = TRUE;
        $output['referral'] = $refer_data;

        if (count($refer_data) < $this->limit) {
            $output['is_load_more'] = FALSE;
        }

        $output['offset'] = $post_data['offset'] + count($refer_data);

        $this->api_response_arry['data'] = $output;
        $this->api_response_arry['message'] = "";
        $this->api_response();
    }

    public function get_referral_data_post() {
        $data = $this->Referral_model->get_single_row('*', REFERRAL_FUND);
        $data['display_msg'] = "Friend you refer, you will get " . CURRENCY_CODE . $data['referral_amount'] . " on his/her investment of " . CURRENCY_CODE . $data['invest_money'];
        $this->api_response_arry['data'] = $data;
        $this->api_response_arry['message'] = "";
        $this->api_response();
    }

    public function send_referral_post() {
        $validation_rule    =   array(                                
                                    array(
                                            'field' => 'email',
                                            'label' => $this->lang->line("email"),
                                            'rules' => 'required|valid_emails'
                                    ),
                                    array(
                                            'field' => 'note',
                                            'label' => $this->lang->line("note"), 
                                            'rules' => 'trim|max_length[500]'
                                    )
                                );
        $this->form_validation->set_rules($validation_rule); 
        if($this->form_validation->run() == FALSE)  { //validate post parameter
            $this->send_validation_errors(); 
        }
        
        $email = $this->input->post('email');
        $note = $this->input->post('note');
        $email_arr = explode(',', $email);
        $link = BASE_APP_PATH . '?referral_code=' . $this->referral_code;
        $result = $this->Referral_model->check_invited_emails($email_arr);
        $error_email = array();
        // print_r($result); die;
        foreach ($result as $value) {
            $error_email[] = $value["email"];
        }
        if (count($error_email)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['service_name'] = 'send_referral';
            $this->api_response_arry['error'] = $error_email;
            $this->api_response_arry['message'] = $this->lang->line('email_already_exists_message');
            $this->api_response();
        }

        //get invited emails
        $invited = $this->Referral_model->get_invited_emails($email_arr);

        if (!empty($invited) && count($invited) == count($email_arr)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;            
            $this->api_response_arry['error'] = $error_email;
            $this->api_response_arry['message'] = $this->lang->line('email_already_invited');
            $this->api_response();
        }

        $refferal_condition = json_encode($this->Referral_model->get_single_row('invest_money,referral_amount', REFERRAL_FUND));
        $reff_data = array();
        foreach ($email_arr as $key => $value) {
            $tmp = array();
            $content = array('name' => $this->user_name, 'link' => $link, 'referral_code' => $this->referral_code);
            $tmp["email"] = $value;
            $tmp["notification_type"] = '18';
            $tmp["source_id"] = $this->user_id;
            $tmp["notification_destination"] = '4'; //Email Only
            $tmp["user_id"] = $this->user_id;
            $tmp["to"] = $value;
            $tmp["user_name"] = $this->user_name;
            $tmp["subject"] = $this->lang->line('user_invitation_subject');
            $tmp["added_date"] = format_date();
            $tmp["modified_date"] = format_date();
            $tmp["content"] = json_encode($content);
            $this->add_notification($tmp);

            if (!empty($invited) && in_array($value, $invited)) {
                continue;
            }

            $ref_user = array();
            $ref_user["user_id"] = $this->user_id;
            $ref_user['bouns_condition'] = $refferal_condition;
            $ref_user['status'] = 0;
            $ref_user['date'] = format_date();
            $ref_user['friend_email'] = $value;

            $reff_data[] = $ref_user;
        }

        if (!empty($reff_data)) {
            $this->Referral_model->table_name = REFFERAL;
            $this->Referral_model->insert_batch($reff_data);
        }

        $this->api_response_arry['data'] = $tmp;
        $this->api_response_arry['message'] = $this->lang->line('invite_send_success');
        $this->api_response();
    }

    public function get_refferal_graph_data_post() {
        $this->form_validation->set_rules('user_id', $this->lang->line('user_id'), 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        $result = $this->Referral_model->get_refferal_graph_data($post);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }
}