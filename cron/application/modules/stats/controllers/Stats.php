<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Stats extends MY_Controller
{
    public $status_list = array("0"=>"Upcoming","1"=>"Live","2"=>"Completed");
    public function __construct()
    {
        parent::__construct();

    }

    public function index()
    {
        echo "Welcome";die();
    }

    /**
     * Used for temporary match list
     * @param int $season_id
     * @return string
     */
    public function match_list()
    {
        $sports_id = 7;
        if(isset($_REQUEST['sports_id']) && $_REQUEST['sports_id'] != ""){
            $sports_id = $_REQUEST['sports_id'];
        }
        $status_id = 1;
        if(isset($_REQUEST['status_id']) && $_REQUEST['status_id'] != ""){
            $status_id = $_REQUEST['status_id'];
        }
        $this->load->model('Stats_model');
        $sports_list = $this->Stats_model->get_all_table_data("sports_id,sports_name",MASTER_SPORTS, array("active"=>"1"));
        $match_list = $this->Stats_model->match_list($sports_id,$status_id);

        $data_list = array();
        $data_list['status_list'] = $this->status_list;
        $data_list['status_id'] = $status_id;
        $data_list['sports_list'] = $sports_list;
        $data_list['sports_id'] = $sports_id;
        $data_list['match_list'] = $match_list;
        //echo "<pre>";print_r($data_list);die;
        $this->load->view('stats/match', $data_list);
    }

    /**
     * Used for calculate player props value
     * @param int $season_id
     * @return string
     */
    public function match_detail($collection_master_id = '')
    {
        if(!empty($collection_master_id))
        {
            $this->load->model('Stats_model');
            $result = $this->Stats_model->get_single_row('*',COLLECTION_MASTER, array("collection_master_id"=>$collection_master_id));
            if(!empty($result)){
                $result['match'] = $this->Stats_model->get_match_detail($collection_master_id,$result['league_id']);
            }
            //echo "<pre>";print_r($result);die;
            $this->load->view('stats/match_detail', array("data"=>$result));
        }else{
            die("Invalid collection id");
        }
    }

    /**
     * Used for show player past matches stats
     * @param int $season_id
     * @param int $player_id
     * @return string
     */
    public function get_match_player_score($season_id = '',$player_id='')
    {
        if(!empty($season_id) && !empty($player_id))
        {
            $post_data = $_POST;
            $this->load->model('Cron_model');
            $result = $this->Cron_model->get_match_player_props($season_id,$player_id);
            $result['column_list'] = get_columns_by_sports($result['season_info']['sports_id']);
            $result['match_list'] = $this->Cron_model->get_match_player_score($season_id,$player_id,$result['season_info']['sports_id']);
            $result['user_input'] = $post_data;
            $this->load->view('cron/player_stats', array("data"=>$result));
        }else{
            die("Invalid input params");
        }
    }



    /**
     * Used for show player past matches stats
     * @param int $season_id
     * @param int $player_id
     * @return string
     */
    public function get_match_player_score_new($season_id = '',$player_id='')
    {
        if(!empty($season_id) && !empty($player_id))
        {
            $post_data = $_POST;
            $this->load->model('Cron_model');
            $result = $this->Cron_model->get_match_player_props($season_id,$player_id);
            $result['column_list'] = get_columns_by_sports($result['season_info']['sports_id']);
            $result['match_list'] = $this->Cron_model->get_match_player_score($season_id,$player_id,$result['season_info']['sports_id']);
            $result['user_input'] = $post_data;
            $this->load->view('cron/player_stats_new', array("data"=>$result));
        }else{
            die("Invalid input params");
        }
    }

    /**
     * Used for temporary match list
     * @param int $season_id
     * @return string
     */
    public function get_match_list()
    {
        $sports_id = 4;
        if(isset($_REQUEST['sports_id']) && $_REQUEST['sports_id'] != ""){
            $sports_id = $_REQUEST['sports_id'];
        }
        $this->load->model('Cron_model');
        $match_list = $this->Cron_model->display_match_list($sports_id,1);
        header('Content-Type:application/json');
        echo json_encode($match_list);
    }

    /**
     * Used for calculate player props value
     * @param int $season_id
     * @return string
     */
    public function get_match_stats_data()
    {
        $post_data = json_decode(file_get_contents('php://input'),TRUE);
        if(empty($post_data) || $post_data['season_id'] == ""){
            header('Content-Type:application/json');
            http_response_code(500);
            echo json_encode(array("message"=>"season id field is required.","data"=>array()));
            exit;
        }else{
            $this->load->model('Cron_model');
            $result = $this->Cron_model->get_match_stats_data($post_data['season_id']);
            header('Content-Type:application/json');
            http_response_code(200);
            echo json_encode(array("message"=>"","data"=>$result));
            exit;
        }
    }

    /**
     * Used for save player props value
     * @param int $season_id
     * @return string
     */
    public function save_player_prediction()
    {
        $post_data = json_decode(file_get_contents('php://input'),TRUE);
        //echo "<pre>";print_r($post_data);die;
        if(empty($post_data) || $post_data['season_id'] == ""){
            header('Content-Type:application/json');
            http_response_code(500);
            echo json_encode(array("message"=>"season id field is required.","data"=>array()));
            exit;
        }else if(empty($post_data) || empty($post_data['player'])){
            header('Content-Type:application/json');
            http_response_code(500);
            echo json_encode(array("message"=>"players list data is required.","data"=>array()));
            exit;
        }else{
            $player_points = array();
            $season_id = $post_data['season_id'];
            $this->load->model('Cron_model');
            $sports_id = $this->Cron_model->get_sports_id_by_season_id($season_id);
            //echo "<pre>";print_r($sports_id);die;
            $columns = get_columns_by_sports($sports_id);
            foreach($post_data['player'] as $row){
                if(isset($row['player_id']) && $row['player_id'] != ""){
                    $tmp_arr = array();
                    $tmp_arr['season_id'] = $season_id;
                    $tmp_arr['player_id'] = $row['player_id'];
                    foreach ($columns as $key => $value) 
                    {
                        $tmp_arr[$value] = floatval(@$row[$value]);
                    } 

                    //add projection probability dated June 29, 2022
                    $custom_data = $this->get_all_probability_range($tmp_arr);
                    if(!empty($custom_data))
                    {
                        $tmp_arr['custom_data'] = json_encode($custom_data);
                    }    
                    //echo "<pre>";print_r($tmp_arr['custom_data']);die;
                    $player_points[] = $tmp_arr;
                }
            }
            if(empty($player_points)){
                header('Content-Type:application/json');
                http_response_code(500);
                echo json_encode(array("message"=>"Invalid player points data.","data"=>array()));
                exit;
            }
            //echo "<pre>";print_r($player_points);die;
            $this->load->model('Cron_model');
            $this->Cron_model->replace_into_batch(PLAYER_PREDICTION, $player_points);
            header('Content-Type:application/json');
            http_response_code(200);
            echo json_encode(array("message"=>"Points saved successfully.","data"=>array()));
            exit;
        }
    }

    public function get_all_probability_range($player_data = array())
    {
        $custom_data = array();
        if(empty($player_data))
        {
            return $custom_data;
        }
        $this->load->model('Cron_model');
        $result = $this->Cron_model->get_match_player_props($player_data['season_id'],$player_data['player_id']);
        $player_info = @$result['player_list'];
        //echo "<pre>";print_r($player_info);die;
        $custom_data = array();
        if(!empty($player_info))
        {
            $sports_id = $player_info['sports_id'];
            $columns = get_columns_by_sports($sports_id);
            foreach ($columns as $key => $value) 
            {
                $column_list = $value.'_list';
                //$column_list = array();
                //echo "<pre>";print_r($column_list);die;
                if($player_info[$column_list] != ""){
                    $points_list = explode(",",$player_info[$column_list]);
                }else{
                    $points_list = array();
                }
                //echo "<pre>";print_r($points_list);die;
                $pts_lmt = round(get_stand_deviation($points_list));
                $pts_lower = $pts_upper = $pts_lower2 = $pts_upper2 = 0;
                $pts_mid = $player_info[$value] + 0.5;
                if($pts_lmt > 0){
                    $pts_lower = round($player_info[$value] - $pts_lmt) + 0.5;
                    $pts_lower2 = round($pts_lower - $pts_lmt) - 0.5;
                    $pts_upper = round($player_info[$value] + $pts_lmt) + 0.5;
                    $pts_upper2 = round($pts_upper + $pts_lmt) - 0.5;
                    if($pts_lower < 0){
                        $pts_lower = 0;
                        }
                    if($pts_lower2 <= 0){
                        $pts_lower2 = 0;
                    }
                    /*if($sports_id = FOOTBALL_SPORTS_ID)
                    {
                        if($pts_upper2 > 500){
                            $pts_upper2 = 500;
                        }
                    }else{    
                        if($pts_upper2 > 100){
                            $pts_upper2 = 100;
                        }
                    } */   

                }
                $pts_lower_per = get_points_probability($points_list,$pts_lower);
                $pts_lower_per2 = get_points_probability($points_list,$pts_lower2);
                $pts_mid_per = get_points_probability($points_list,$pts_mid);
                $pts_upper_per = get_points_probability($points_list,$pts_upper);
                $pts_upper_per2 = get_points_probability($points_list,$pts_upper2);

               
                if($pts_lower != $pts_lower2)
                {
                    $$value[] = array("pts"=>$pts_lower2 ,"over"=>$pts_lower_per2,"under"=>100-$pts_lower_per2);  
                }    
                
                $$value[] = array("pts"=>$pts_lower ,"over"=>$pts_lower_per,"under"=>100-$pts_lower_per);
                $$value[] = array("pts"=>$pts_mid ,"over"=>$pts_mid_per,"under"=>100-$pts_mid_per);
                $$value[] = array("pts"=>$pts_upper ,"over"=>$pts_upper_per,"under"=>100-$pts_upper_per);
                if($pts_lower != $pts_lower2)
                {
                    $$value[] = array("pts"=>$pts_upper2 ,"over"=>$pts_upper_per2,"under"=>100-$pts_upper_per2);
                }

                $custom_data[$value] = $$value;
                //echo "<pre>";print_r($custom_data); die; 
            }    
        }    

        //echo "<pre>";print_r($custom_data); die;  
        return $custom_data; 
       
    }


    public function all_probability_range()
    {
        //exit('Temp updated in db');
        $sports_id = 4;
        $post_data = json_decode(file_get_contents('php://input'),TRUE);
        if(!empty($post_data) && $post_data['sports_id'] != "")
        {
            $sports_id = $post_data['sports_id'];
        }    
        //echo "<pre>";print_r($sports_id);die;
        $this->load->model('Cron_model');
        $match_list = $this->Cron_model->all_probability_range($sports_id);
        //echo "<pre>";print_r($match_list);die;
        foreach ($match_list as $key => $value) 
        {
            $custom_data =  $this->get_all_probability_range($value);
            //echo "<pre>";print_r($custom_data);die;
            if(!empty($custom_data))
            {
                $value['custom_data'] = $custom_data;
                $this->Cron_model->update_probability_range($value);  
            }    
            //die;
        }
        exit('All probability range updated');
    }

    public function propability()
    {
        //http://local.framework.com/props/cron/get_match_player_score/76/1544
        $usr_stat_list = array('435','382','386','196','206','341','323','296','261','215','373','249','263','236','201','255');
        $user_point_val = '255.5';
        $user_over = $this->points_probability($usr_stat_list,$user_point_val);
        echo "Over - ";echo $user_over;
        echo "<pre>Under - ";echo 100-$user_over;
        echo "<pre>";echo "-------";
        
        $user_over = $this->points_probability2($usr_stat_list,$user_point_val);
        echo "<pre>";
        echo "Over - ";echo $user_over;
        echo "<pre>Under - ";echo 100-$user_over;
        die;
    }

    function points_probability($data_list,$number){
        if(empty($data_list)){
            return 0;
        }
        $filtered_arr = array_filter($data_list, function($value) use( $number ){
                return $number > $value;
        });
        $value_count = count($filtered_arr);
        $percent = round(($value_count * 100) / count($data_list));
        return $percent;
    }

    function points_probability2($data_list,$number){
        $number = 260.5;
        if(empty($data_list)){
            return 0;
        }
        $filtered_arr = array_filter($data_list, function($value) use( $number ){
                return $number > $value;
        });
        //print_r($filtered_arr);die;
        $value_count = count($filtered_arr);
        $percent = round(($value_count * 100) / count($data_list));
        return $percent;
    }


}
 
