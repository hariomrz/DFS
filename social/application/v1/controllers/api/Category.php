<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Category module
* @category		Controller
* @author		Vinfotech Team
*/
class Category extends Common_API_Controller 
{

	// Class Constructor
	function __construct()
	{
		parent::__construct();
                $this->check_module_status(12);
                $this->load->model(array('category/category_model'));
		//$this->lang->load('category');
	}
	
	/*------------------------------------------
	| @Method : To get category list 
	| @Params : ModuleID(Int),categoryLevelID(Int)
	| @Output : array
	------------------------------------------*/
	public function get_categories_post()
	{
		$data = $this->post_data; // Get post data
		$validation_rule         =    $this->form_validation->_config_rules['api/category/get_categories'];
		$user_id = $this->UserID;
		$this->form_validation->set_rules($validation_rule); 
		if($this->form_validation->run() == FALSE) // Check for empty request
		{
			$error = $this->form_validation->rest_first_error_string(); 
			$return['ResponseCode']=500;
			$return['Message'] = $error; 
		}
		else 
		{
			$parent_category_id = (!empty($data['categoryLevelID'])?$data['categoryLevelID']:0);
			$data = $this->category_model->get_categories($data['ModuleID'], $parent_category_id,$user_id);
			$return['ResponseCode']=self::HTTP_OK;
			$return['Message']=lang('success');
			$return['ServiceName']='category/get_categories';
			$return['Data']=$data;
		}
		$this->response($return);  // Final Output 
	}

	/*------------------------------------------
	| @Method : To get category list 
	| @Params : ModuleID(Int),categoryLevelID(Int)
	| @Output : array
	------------------------------------------*/
	public function get_interests_post()
	{
		$data = $this->post_data; // Get post data
		$validation_rule         =    $this->form_validation->_config_rules['api/category/get_categories'];
		$user_id = $this->UserID;
		$this->form_validation->set_rules($validation_rule); 
		if($this->form_validation->run() == FALSE) // Check for empty request
		{
			$error = $this->form_validation->rest_first_error_string(); 
			$return['ResponseCode']=500;
			$return['Message'] = $error; 
		}
		else 
		{
			$parent_category_id = (!empty($data['categoryLevelID'])?$data['categoryLevelID']:0);
			$data = $this->category_model->get_interests($data['ModuleID'],$parent_category_id,$user_id);
			$return['ResponseCode']=self::HTTP_OK;
			$return['Message']=lang('success');
			$return['ServiceName']='category/get_interests';
			$return['Data']=$data;
		}
		$this->response($return);  // Final Output 
	}

	/*------------------------------------------
	| @Method : To update entities category 
	| @Params : ModuleID(Int),categoryLevelID(Int)
	| @Output : array
	------------------------------------------*/
	public function update_entity_category_post()
	{
		$data = $this->post_data; // Get post data
		$validation_rule         =    $this->form_validation->_config_rules['api/category/updatecategories'];
		$this->form_validation->set_rules($validation_rule); 
		if($this->form_validation->run() == FALSE) // Check for empty request
		{
			$error = $this->form_validation->rest_first_error_string(); 
			$return['ResponseCode']=500;
			$return['Message'] = $error; 
		}
		else 
		{
			$response = $this->category_model->update_entity_category($data['CategoryIDS'], $data['EntityID']);
			if($response){
				$return['ResponseCode']=self::HTTP_OK;
				$return['Message']=lang('success');
				$return['ServiceName']='category/update_entity_category';
			}
			else
			{
				$return['ResponseCode']=501;
				$return['Message']= "Due to technical error, Unable to process currently  !!!";
				$return['ServiceName']='category/update_entity_category';
			}
		}
		$this->response($return);  // Final Output 
	}
        
        public function directory_post() {
            
            $data = $this->post_data;    
            $user_id = $this->UserID;     
            $this->load->model(array(
                'locality/locality_model', 
                'users/user_model'));
            
            $is_admin = $this->user_model->is_super_admin($user_id, 1);
            
            
            //$locality = $this->locality_model->get_locality($this->LocalityID);
            
            $total_records = $this->user_model->directory(1, 100, '', $is_admin, 1, $user_id);
            $directory_data[0]['Title'] = 'सदस्य';
            $directory_data[0]['ModuleID'] = 3;
            $directory_data[0]['TotalRecords'] = $total_records;
            $directory_data[0]['Description'] = "भोपू ऐप से जुड़े वार्ड 37 के निवासियों की सूची";
                                    
        /*    $total_records = $this->category_model->utility(46, '', 1, -1);
            $directory_data[1]['Title'] = 'दुकान और कारीगर';
            $directory_data[1]['ModuleID'] = 46;
            $directory_data[1]['TotalRecords'] = $total_records;
            $directory_data[1]['Description'] = $locality['HindiName']." की दुकान, डॉक्टर, प्लम्बर, इलेक्ट्रीशियन आदि की सूची";         
         * 
         */   
            $total_records = $this->category_model->utility(45, '', 1, -1);
            $directory_data[1]['Title'] = 'जरुरी और आपातकालीन';
            $directory_data[1]['ModuleID'] = 45;
            $directory_data[1]['TotalRecords'] = $total_records;
            $directory_data[1]['Description'] = "नगर निगम, पुलिस, एम्बुलेंस, फायर ब्रिगेड आदि की सूची";
                  
            $this->return['Data'] = $directory_data;
            $this->return['ContactNumber'] = '8982725467';
            
            $this->return['IsAdmin'] = $is_admin; 
            $this->response($this->return); /* Final Output */
            
        }
        
        public function utility_post() {
            
            $data = $this->post_data;    
            $user_id = $this->UserID;     
            
            $this->form_validation->set_rules('ModuleID', 'Module', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
                $this->response($return);
            }
            
            $module_id = trim($data['ModuleID']);
            //$page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            //$page_size = isset($data['PageSize']) ? $data['PageSize'] : 100;
            $search_keyword = isset($data['Keyword']) ? $data['Keyword'] : '';   
            $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'Name';
            $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'ASC';
            
            $this->return['Data'] = $this->category_model->utility($module_id, $search_keyword, 0, 0, $order_by, $sort_by);
            
            $this->response($this->return); /* Final Output */
            
        }
}
