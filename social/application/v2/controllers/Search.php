<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All user registration and sign in process
* Controller class for sign up and login  user of cnc
* @package    signup

* @version    1.0
*/
class Search extends Common_Controller
{
    public $page_name= 'search';

    /*
    * All user registration and sign in process
    * Controller class for sign up and login  user of cnc
    * @package    signup
    * @author      
    * @version    1.0
    */
    public function __construct()
    {
        parent::__construct();
            if($this->session->userdata('LoginSessionKey')==''){
            redirect('/');
        }
        
    }

    /*
    * All user registration and sign in process
    * Controller class for sign up and login  user of cnc
    * @package    user
    * @version    1.0
    */
    public function index($keyword='')
    {
        if (empty($this->login_session_key))
        {
            redirect('/');
        }
        $user_id = get_user_id_by_loginsessionkey($this->login_session_key);
        $keyword = explode('/',current_url());
        $keyword = end($keyword);
        $this->data['title'] = 'Search';
        $this->page_name = 'search';
        $this->data['pname'] = 'search';
        $this->data['IsNewsFeed'] = '1';
        $this->data['isFileTab'] = false;
        $this->data['isLinkTab'] = false;
        $this->dashboard = '';
        $this->data['ModuleEntityGUID'] = get_guid_by_id($user_id, 3);
        $this->data['ModuleID'] = '3';
        $this->data['UserID'] = $user_id;
        $this->data['Keyword'] = urldecode(trim($keyword));
        $this->data['page'] = 'content';
        $this->load->model(array('activity/activity_model'));
        
        $this->UserID = $this->session->userdata('UserID');

        if($this->activity_model->is_new_user($this->session->userdata('UserID')))
        {
            $this->data['IsNewUser'] = 1;
        }

        $this->data['content_view'] = 'search/advanced-search';
        //$this->data['content_view'] = 'search/people-search';
        $this->load->view($this->layout, $this->data);
    }
    
//    public function index($keyword='')
//    {
//        $keyword = explode('/',current_url());
//        $keyword = end($keyword);
//        $this->data['title'] = 'Search User';
//        $this->page_name = 'search';
//        $this->dashboard = '';
//        $this->data['Keyword'] = $keyword;
//        $this->data['content_view'] = 'search/people-search';
//        $this->load->view($this->layout, $this->data);
//    }

    public function group($keyword='')
    {        
        $this->data['title'] = 'Search Group';
        $this->page_name = 'search';
        $this->dashboard = '';
        $this->data['pname'] = 'search';
        $this->data['Keyword'] = $keyword;
        $this->data['content_view'] = 'search/advanced-search';
        $this->data['page'] = 'group';
        $this->load->view($this->layout,$this->data);
    }

    public function page($keyword='')
    {        
        $this->data['title'] = 'Search Group';
        $this->page_name = 'search';
        $this->dashboard = '';
        $this->data['pname'] = 'search';
        $this->data['Keyword'] = $keyword;
        $this->data['content_view'] = 'search/advanced-search';
        $this->data['page'] = 'page';
        $this->load->view($this->layout,$this->data);
    }

    public function event($keyword='')
    {        
        $this->data['title'] = 'Search Event';
        $this->page_name = 'search';
        $this->dashboard = '';
        $this->data['pname'] = 'search';
        $this->data['Keyword'] = $keyword;
        $this->data['content_view'] = 'search/advanced-search';
        $this->data['page'] = 'event';
        $this->load->view($this->layout,$this->data);
    }

    public function people($keyword='')
    {
        $this->data['title'] = 'Search People';
        $this->page_name = 'search';
        $this->dashboard = '';
        $this->data['pname'] = 'search';
        $this->data['Keyword'] = $keyword;
        $this->data['content_view'] = 'search/advanced-search';
        $this->data['page'] = 'people';
        $this->load->view($this->layout,$this->data);
    }

    public function photo($keyword='')
    {
        $this->data['title'] = 'Search Photo';
        $this->page_name = 'search';
        $this->dashboard = '';
        $this->data['pname'] = 'search';
        $this->data['Keyword'] = $keyword;
        $this->data['content_view'] = 'search/advanced-search';
        $this->data['page'] = 'photo';
        $this->load->view($this->layout,$this->data);
    }

    public function video($keyword='')
    {
        $this->data['title'] = 'Search Video';
        $this->page_name = 'search';
        $this->dashboard = '';
        $this->data['pname'] = 'search';
        $this->data['Keyword'] = $keyword;
        $this->data['content_view'] = 'search/advanced-search';
        $this->data['page'] = 'video';
        $this->load->view($this->layout,$this->data);
    }

    public function top($keyword='')
    {
        $this->data['title'] = 'Search Top';
        $this->page_name = 'search';
        $this->dashboard = '';
        $this->data['pname'] = 'search';
        $this->data['Keyword'] = $keyword;
        $this->data['content_view'] = 'search/advanced-search';
        $this->data['page'] = 'top';
        $this->load->view($this->layout,$this->data);
        
        // Log this search
        $this->load->model('log/user_activity_log_score_model');
        $user_id    = $this->session->userdata('UserID');
        $this->user_activity_log_score_model->log_search_data($keyword, $user_id);
    }


/**
* [use to add/edit/delete search filters]
* @return [json] [return json boject]
*/
public function AddEditDeleteFilter_post()
{
    /* Define variables - starts */
    $return         = $this->return;     
    $data           = isset($this->post_data) ? $this->post_data : [];
    /* Define variables - ends */

    /* Validation - starts */
    $validation_rule[]  =   array('field' => 'FilterName', 'label' => 'Filter Name', 'rules' => '');

    $this->form_validation->set_rules($validation_rule);
    if ($this->form_validation->run() == FALSE) 
    {
        $return['ResponseCode'] = 511;
        $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
    } /* Validation - ends */ else{    

        /*Define post variables - starts*/
        $Input['UserID']    = $this->UserID;
        $Input['FilterGUID']  = isset($data['FilterGUID']) ? $data['FilterGUID'] : '' ;        
        $Input['FilterName']  = isset($data['FilterName']) ? $data['FilterName'] : '' ;
        $Input['FilterValues']      = isset($data['FilterValues']) ? $data['FilterValues'] : '' ;
        /*Define post variables - ends*/

        /*Get Final Records*/
        $Records = $this->search_model->AddEditDeleteFilter($Input);
        if($Records){
            $return['Data'] = $Records;
        }
    }

    $this->response($return);
}






/**
* [use to get Search filter records]
* @return [json] [return json boject]
*/
public function GetSearchFilters_post()
{
    /* Define variables - starts */
    $return         = $this->return;     
    $data           = isset($this->post_data) ? $this->post_data : [];
    /* Define variables - ends */

    /* Validation - starts */
   // $validation_rule[]  =   array('field' => 'FilterName', 'label' => 'Filter Name', 'rules' => '');

  /*  $this->form_validation->set_rules($validation_rule);
    if ($this->form_validation->run() == FALSE) 
    {
        $return['ResponseCode'] = 511;
        $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
    }*/ /* Validation - ends *//* else{ */   

        /*Define post variables - starts*/
        $Input['UserID']    = $this->UserID;
        /*Define post variables - ends*/


        /*Get Final Records*/
        $Records = $this->search_model->GetSearchFilters($this->UserID,'UserID',10);
        if($Records){
            $return['Data'] = $Records;
        }
   /* }*/

    $this->response($return);
}







    

}