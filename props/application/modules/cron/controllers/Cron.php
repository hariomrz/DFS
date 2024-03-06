<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends MY_Controller
{
    public $user_id = 1;
    public $user_name = "puneet";
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        echo "Welcome";die();
    }

    /**
     * Used for update score in user team lineup table
     * @param void
     * @return string
     */
    public function update_team_score()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->update_team_score();
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for prize_distribution
     * @param void
     * @return string
     */
    public function prize_distribution()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->prize_distribution();
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

     /**
     * Used for prize distribution notification
     * @param void
     * @return string
     */
    public function prize_notification()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->prize_notification();
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
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
        $status = 0;
        if(isset($_REQUEST['status']) && $_REQUEST['status'] != ""){
            $status = $_REQUEST['status'];
        }
        $this->load->model('Cron_model');
        $sports_list = $this->Cron_model->get_sports_list();
        $match_list = $this->Cron_model->get_match_list($sports_id,$status);
        $this->load->view('cron/match', array("data"=>$match_list,"sports"=>$sports_list,"sports_id"=>$sports_id,"status"=>$status));
    }

    /**
     * Used for temporary match props
     * @param int $season_id
     * @return string
     */
    public function match_props()
    {
        if(!isset($_REQUEST['season_id']) || $_REQUEST['season_id'] == ""){
        	die("Invalid season id");
        }
        $season_id = $_REQUEST['season_id'];
        $tab = isset($_REQUEST['tb']) ? $_REQUEST['tb'] : "t_props";
        $this->load->model('Cron_model');
        $match_info = $this->Cron_model->get_match_detail($season_id);
        $props = $this->Cron_model->get_master_props($match_info['sports_id']);
        $players = $this->Cron_model->get_match_props($season_id);
        $team_arr = array("sports_id"=>$match_info['sports_id'],"user_id"=>$this->user_id,"season_id"=>$season_id);
        $user_teams = $this->Cron_model->get_user_teams($team_arr);
        $return = array();
        $return['tab'] = $tab;
        $return['season_id'] = $season_id;
        $return['match_info'] = $match_info;
        $return['props'] = $props;
        $return['player_list'] = $players;
        $return['user_teams'] = $user_teams;
        $this->load->view('cron/match_props', $return);
    }

    /**
     * Used for temporary match completed props
     * @param int $season_id
     * @return string
     */
    public function complete_props()
    {
        if(!isset($_REQUEST['season_id']) || $_REQUEST['season_id'] == ""){
            die("Invalid season id");
        }
        $sports_props = array();
        $sports_props['5'] = array("goals","assists","shots_on_goal","saves","passes_completed","tackles_won","clearance");
        $season_id = $_REQUEST['season_id'];
        $tab = "t_props";
        $this->load->model('Cron_model');
        $match_info = $this->Cron_model->get_match_detail($season_id);
        $props = $this->Cron_model->get_master_props($match_info['sports_id']);
        $tmp_arr = array("season_id"=>$season_id,"sports_id"=>$match_info['sports_id'],"fields"=> implode(",",array_column($props,"fields_name")));
        $players = $this->Cron_model->get_completed_match_props($tmp_arr);
        $return = array();
        $return['tab'] = $tab;
        $return['season_id'] = $season_id;
        $return['sports_id'] = $match_info['sports_id'];
        $return['match_info'] = $match_info;
        $return['props'] = $props;
        $return['player_list'] = $players;
        //echo "<pre>";print_r($return);die;
        $this->load->view('cron/complete_props', $return);
    }

    /**
     * Used for temporary match props
     * @param int $season_id
     * @return string
     */
    public function props()
    {
        $sports_id = 7;
        if(isset($_REQUEST['sports_id']) && $_REQUEST['sports_id'] != ""){
            $sports_id = $_REQUEST['sports_id'];
        }
        $tab = isset($_REQUEST['tb']) ? $_REQUEST['tb'] : "t_props";
        $this->load->model('Cron_model');
        $sports_list = $this->Cron_model->get_sports_list();
        $props = $this->Cron_model->get_master_props($sports_id);
        $players = $this->Cron_model->get_sports_match_props($sports_id);
        $team_arr = array("sports_id"=>$sports_id,"user_id"=>$this->user_id);
        $user_teams = $this->Cron_model->get_user_teams($team_arr);
        //echo "<pre>";print_r($user_teams);die;
        $return = array();
        $return['tab'] = $tab;
        $return['sports_id'] = $sports_id;
        $return['sports'] = $sports_list;
        $return['props'] = $props;
        $return['player_list'] = $players;
        $return['user_teams'] = $user_teams;
        //echo "<pre>";print_r($return);die;
        $this->load->view('cron/props',$return);
    }

    /**
     * Used for temporary match props
     * @param int $season_id
     * @return string
     */
    public function save_team()
    {
    	$json = file_get_contents('php://input');
		$post_data = json_decode($json, true);
        //echo "<pre>";print_r($post_data);die;
		$_POST = $post_data;
		$is_valid = 0;
		$msg = "";
		if(empty($post_data['payout_type'])){
			$msg = "payout type field required.";
		}else if(empty($post_data['currency_type'])){
			$msg = "currency type field required.";
		}else if(empty($post_data['entry_fee'])){
			$msg = "entry fee field required.";
		}else if(empty($post_data['pl'])){
			$msg = "Select atleast 2 props.";
		}else{
			$is_valid = 1;
		}
		if($is_valid == 1){
			$this->load->model('Cron_model');
			$post_data['user_id'] = $this->user_id;
            $post_data['user_name'] = $this->user_name;
			$post_data['team_name'] = "Entry 1";
			$team = $this->Cron_model->get_single_row("COUNT(user_team_id) as total",USER_TEAM,array("user_id" => $post_data['user_id']));
			if(!empty($team) && $team['total'] > 0){
				$post_data['team_name'] = "Entry ".($team['total'] + 1);
			}
        	$result = $this->Cron_model->save_team($post_data);
        	if(!empty($result) && isset($result['user_team_id'])){
        		echo json_encode(array("status"=>1,"message"=>"Entry saved successfully..","data"=>$result));exit;
        	}else{
        		echo json_encode(array("status"=>0,"message"=>"Something went wrong while save team.","data"=>array()));exit;
        	}
		}else{
			echo json_encode(array("status"=>0,"message"=>$msg,"data"=>array()));exit;
		}
    }

    public function update_match_playing_data()
    {
        $this->load->helper('queue');
        $this->load->model('Cron_model');
        $server_name = get_server_host_name();
        $match_list = $this->Cron_model->get_playing_upcoming_match();
        //echo "<pre>";print_r($match_list);die;
        foreach($match_list as $match)
        {
            $sports_id = $match['sports_id'];
            $season_game_uid = $match['season_game_uid'];
            $content                  = array();
            if($sports_id == CRICKET_SPORTS_ID)
            {
                $content['url'] = $server_name."/props/feed/vinfotech_cricket/get_season_details/".$season_game_uid;
            }
            
            if($sports_id == SOCCER_SPORTS_ID)
            {
               $content['url'] = $server_name."/props/feed/vinfotech_soccer/get_season_details/".$season_game_uid;
            }
            //echo "<pre>";print_r($content);die;
            add_data_in_queue($content,'season_cron');
        }

        echo "done";
        exit();
    }

}
