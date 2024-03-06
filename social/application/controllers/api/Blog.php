<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Blog extends Common_API_Controller {
    function __construct() {
        parent::__construct();
        $this->check_module_status(16);
        $this->load->model(array(
            'blog/blog_model',
            'activity/activityrule_model',
        ));    
    }

    /**
    * @Function - function to list blog
    * @Output   - JSON
    */
    public function list_post()
    {
        
        $return = $this->return;
        $data   = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data


        $page_size              = isset($data['PageSize'])?$data['PageSize']:PAGE_SIZE;
        $page_no                = isset($data['PageNo'])?$data['PageNo']:1;
        $search_keyword         = isset($data['SearchKeyword'])?$data['SearchKeyword']:'';
        $sort_by                = isset($data['SortBy'])?$data['SortBy']:'CreatedDate';
        $order_by               = !empty($data['OrderBy'])?$data['OrderBy']:'DESC';
        $list_type              = !empty($data['ListType'])?$data['ListType']:'';
        $entity_type         = (@$data['EntityType'])?@$data['EntityType']:array('1');  
         
         // Check if user belongs to any rule
        if(isset($entity_type[0]) && in_array(3, $entity_type)) {
            $rules = $this->activityrule_model->getActivityRules($UserID, NULL, true); 
            if(!empty($rules['Welcome'])) {
                $return['Data'] = array(
                    0 => array(
                        'Author' => '',
                        'BlogGUID' => '',
                        'CoverMedia' => [],
                        'CreatedDate' => '',
                        'Description' => $rules['Welcome'],
                        'EntityType' => '4',
                        'Media' => [],
                        'ModifiedDate' => '',
                        'NoOfComments' => '0',
                        'NoOfLikes' => '0',
                        'Status' => 'Status',
                        'Title' => 'Welcome',
                    )
                );
                
                $return['TotalRecords'] = 2;
                $this->response($return);
            } 
        } 
        
        $return['Data']         = $this->blog_model->blog_list($search_keyword, FALSE, $page_no, $page_size, $sort_by, $order_by,$list_type,$entity_type);
        $return['TotalRecords'] = $this->blog_model->blog_list($search_keyword, TRUE,'','','','',$list_type);
        $this->response($return);        
    }

    /**
    * @Function - function to get detail evaluations
    * @Input    - EvaluationGUID(STRING)
    * @Output   - JSON
    */
    public function details_post()
    {
        
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data
        $validation_rule         =    $this->form_validation->_config_rules['api/blog/detail'];
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string(); 
            $return['ResponseCode'] =   500;
            $return['Message']      =   $error; 
        }
        else 
        {
            $blog_guid        = $data['BlogGUID'];
            $return['Data']   = $this->blog_model->details($blog_guid);
            
            if(empty($return['Data']))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message']      = sprintf(lang('valid_value'), "blog guid");          
            }
            $this->response($return);       
        }
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
