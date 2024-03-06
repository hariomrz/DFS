<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Project_info extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo "Welcome";die();
    }

    

    /**
     * Used for get WL project system user and deposit information
     * @param 
     * @return string
     */
    public function get_project_info()
    {   
        $this->load->model('Cron_model');
        $this->load->model('Project_info_model');
        $user_count =  $this->Cron_model->get_user_count();
        //deposit
        $limit = @$_GET['limit'];//die;
        $deposit =  $this->Project_info_model->get_project_deposit($limit);
        //system user report
        $from_date = @$_GET['from_date'];//die;
        $collection_info =  $this->get_system_user_reports($from_date);
        //echo "<pre>";print_r($collection_info);die;

        $project_info = array();
        $project_info['user_count']         = json_decode($user_count);
        $project_info['deposit']            = ($deposit);
        $project_info['collection_info']   = ($collection_info);
        echo json_encode($project_info);
        exit();
    }

    private function get_system_user_reports($from_date ='')
    {
            $this->load->model('Project_info_model');
            $data_arr = $this->input->post();
            $data_arr['sports_id'] = 7;
            $data_arr['from_date'] = $from_date;
            //echo "<pre>";print_r($data_arr);die;
            $result = $this->Project_info_model->get_system_user_reports($data_arr);
           // echo "<pre>";print_r($result);die;
            $collection_master_ids = array_column($result['result'],"collection_master_id");
            $cmid_arr = array();
            
            foreach($collection_master_ids as $cmid)
            {
                $cmid_arr[$cmid] = $this->Project_info_model->get_contest_ids($cmid);
            }
           // echo "<pre>";print_r($cmid_arr);die;
            $bot_prize_data = array(
                                        "realuser_winnings"=>0,
                                        "systemuser_winnings"=>0
                                        );
            $contest_data = array();
            foreach($cmid_arr as $key=>$contest_id_arr)
            {

                if(!empty($contest_id_arr))
                {
                    $con_det[$key] = $this->Project_info_model->get_contest_details($contest_id_arr);
                    $real_amount[$key] = array_sum(array_column($con_det[$key],'real_amount'));
                    $bonus_amount[$key] = array_sum(array_column($con_det[$key],'bonus_amount'));
                    $bot_prize[$key] = $this->Project_info_model->get_bot_prize($contest_id_arr);
                    $sys_user_winning[$key] = array_sum(array_column($bot_prize[$key],'systemuser_winnings'));
                    $real_user_winning[$key] = array_sum(array_column($bot_prize[$key],'realuser_winnings'));
                }
                
            }

            $data = array();
            $final_result = array();
            foreach($result['result'] as $key=>$res)
            {
                $data[$res['collection_master_id']]=$res;
                $data[$res['collection_master_id']]['bonus_loss']= 0;
                $data[$res['collection_master_id']]['real_amount']= 0;
                $data[$res['collection_master_id']]['net_profit']= 0;
                $data[$res['collection_master_id']]['site_rake']= 0;
                $data[$res['collection_master_id']]['realuser_winnings']=floatval(round($real_user_winning[$res['collection_master_id']],2));
                $data[$res['collection_master_id']]['systemuser_winnings']=floatval(round($sys_user_winning[$res['collection_master_id']],2));
                $data[$res['collection_master_id']]['bonus_loss']=floatval(round($bonus_amount[$res['collection_master_id']],2));
                $data[$res['collection_master_id']]['real_amount']=floatval(round($real_amount[$res['collection_master_id']],2));
                $data[$res['collection_master_id']]['net_profit']=floatval(round($real_amount[$res['collection_master_id']]-$real_user_winning[$res['collection_master_id']],2));
                $data[$res['collection_master_id']]['site_rake']=floatval(round($data[$res['collection_master_id']]['net_profit']-$sys_user_winning[$res['collection_master_id']],2));
            }
            foreach($data as $key=>$value){
                if($value['net_profit']>0 ){
                    if($value['systemuser_winnings'] >= $value['net_profit']){
                            $value['systemuser_winnings'] = $value['net_profit'];
                    }
                }
                else{
                        $value['systemuser_winnings']=0;
                }
            if($value['site_rake'] < 0){
            $value['site_rake']=0;
            }
            $final_result[]=$value;
            }
        $total_net_profit = array_sum(array_column($final_result,'net_profit'));
        // $total_site_rake = array_sum(array_column($final_result,'site_rake'));
        $data['balance'] = floatval(round($total_net_profit,2));
        
        return $system_user_info  = $final_result;
        //echo "<pre>";print_r($system_user_info);
        //exit();

        
    }

    /**
     * Used for get WL project system user and deposit information
     * @param 
     * @return string
     */
    public function get_balance_info()
    {   
        $this->load->model('Project_info_model');
        $record_info = $this->Project_info_model->get_balance_info();
        echo json_encode($record_info);
        exit();
    }



}