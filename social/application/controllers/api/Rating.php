<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Rating extends Common_API_Controller {

    function __construct() 
    {
        parent::__construct();
        $this->check_module_status(23);
        $this->load->language('rating');
        $this->lang->load('rating', $this->config->item('language'));
        $this->load->model(array('ratings/rating_model'));
    }

    /**
     * [add - Add new rating]
     * @param  $module_id
     * @param  $module_entity_guid
     * @param  $rate_value
     * @param  $title
     * @param  $description
     * @return [RatingGUID, ReviewGUID, CreatedDate, ModifiedDate]
     */
    function add_post()
    {
    	$return         = $this->return;
    	$data           = $this->post_data;
        $user_id         = $this->UserID;  
        if($this->form_validation->run('api/rating/add') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
        	$rating_guid 		= '';
        	$module_id 			= $data['ModuleID'];
        	$module_entity_guid = $data['ModuleEntityGUID'];
        	$rate_value 		= $data['RateValue'];
        	$title 				= $data['Title'];
        	$description 		= $data['Description'];

            $module_entity_id   = get_detail_by_guid($module_entity_guid, $module_id);

        	$media 				= isset($data['Media']) 				 ? $data['Media'] 					: '' ;
        	$post_as_module_id 	= isset($data['PostAsModuleID']) 		 ? $data['PostAsModuleID'] 			: '' ;
        	$rating_parameter_value 	= isset($data['RatingParameterValue']) 	 ? $data['RatingParameterValue'] 	: '' ;
        	$post_as_module_entity_guid = isset($data['PostAsModuleEntityGUID']) ? $data['PostAsModuleEntityGUID'] 	: '' ;
            $post_as_module_entity_id   = '';
        	
        	if(!$this->rating_model->check_permission($user_id, $module_id, $module_entity_id))
            {
        		$return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
        		$return['Message'] = lang('permission_denied');
        		$this->response($return);
        	}

            if(!empty($post_as_module_id) && !empty($post_as_module_entity_guid))
            {               
                $post_as_module_entity_id = get_detail_by_guid($post_as_module_entity_guid, $post_as_module_id);
            }

        	if($this->rating_model->is_rated($user_id, $module_id, $module_entity_id, $post_as_module_id, $post_as_module_entity_id))
            {
        		$return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
        		$return['Message'] = lang('already_rated');
        		return $this->response($return);	
        	}

        	$return['Data'] = $this->rating_model->add_edit_rating($user_id, $rating_guid, $module_id, $module_entity_id, $rate_value, $title, $description, $media, $post_as_module_id, $post_as_module_entity_id, $rating_parameter_value);
        }
    	$this->response($return);
    }

    /**
     * [edit - Edit existing rating]
     * @param  $rating_guid
     * @param  $module_id
     * @param  $module_entity_guid
     * @param  $rate_value
     * @param  $title
     * @param  $description
     * @return [RatingGUID, ReviewGUID, CreatedDate, ModifiedDate]
     */
    function edit_post()
    {
    	$return         = $this->return;
    	$data           = $this->post_data;
        $user_id        = $this->UserID;  
        if($this->form_validation->run('api/rating/edit') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
        	$rating_guid 		= $data['RatingGUID'];
        	$rate_value 		= $data['RateValue'];
        	$title 				= $data['Title'];
        	$description 		= $data['Description'];

        	$media 				= isset($data['Media']) 				 ? $data['Media'] 					: '' ;
        	$rating_parameter_value 	= isset($data['RatingParameterValue']) 	 ? $data['RatingParameterValue'] 	: '' ;
        	
        	if(!$this->rating_model->check_permission($user_id, '', '', $rating_guid))
            {
        		$return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
        		$return['Message'] = lang('permission_denied');
        		return $this->response($return);
        	}
        	
        	$return['Data'] = $this->rating_model->add_edit_rating($user_id, $rating_guid, '', '', $rate_value, $title, $description, $media, '', '', $rating_parameter_value);
        }
    	$this->response($return);
    }

    /**
     * [parameter - list of parameter according to category]
     * @param  $category_id
     * @return [list of parameters]
     */
    function parameter_post()
    {
        $return         = $this->return;
        $data           = $this->post_data;
        $user_id         = $this->UserID;  
        if($this->form_validation->run('api/rating/parameter') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $category_id = $data['CategoryID'];
            $return['Data'] = $this->rating_model->get_rating_parameters($category_id);
        }
        $this->response($return);
    }

    /**
     * [vote - vote for helpful rating]
     * @param  $vote
     * @param  $entity_guid
     * @param  $entity_type
     * @return Boolean
     */
    function vote_post()
    {
        $return         = $this->return;
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        if($this->form_validation->run('api/rating/vote') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $vote        = strtoupper($data['Vote']);
            $entity_guid = $data['EntityGUID'];
            $entity_type = $data['EntityType'];

            if($vote!='YES' && $vote!='NO')
            {
                $return['Message'] = sprintf(lang('valid_value'), "vote");
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->response($return);
            }

            if($entity_type!='RATING')
            {
                $return['Message'] = sprintf(lang('valid_value'), "entity type"); 
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->response($return);
            }

            $status = $this->rating_model->vote($user_id, $entity_guid, $entity_type, $vote);
            if(!$status)
            {
                $return['Message'] = lang('voted');
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            }
        }
        $this->response($return);   
    }

    /**
     * [details - detail of particular rating]
     * @param  $rating_guid
     * @return [Rating Details]
     */
    function details_post()
    {
        $return         = $this->return;
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        if($this->form_validation->run('api/rating/details') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $rating_guid = $data['RatingGUID'];
            $return['Data'] = $this->rating_model->get_rating_list($user_id,'','','','','','','','',1,1,10,0,$rating_guid);
        }
        $this->response($return);   
    }

    /**
     * [overall - overall rating of particular entity]
     * @param  $module_id
     * @param  $module_entity_guid
     * @return [overall rating]
     */
    function overall_post()
    {
        $return         = $this->return;
        $data           = $this->post_data;
        if($this->form_validation->run('api/rating/overall') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $module_id           = $data['ModuleID'];
            $module_entity_guid  = $data['ModuleEntityGUID'];
            $return['Data'] = $this->rating_model->get_overall_ratings($module_id, $module_entity_guid);
        }
        $this->response($return);   
    }

    /**
     * [star_count - overall star count rating of entity]
     * @param  $module_id
     * @param  $module_entity_guid
     * @return [OneStarRating=2,TwoStarRating=6,...]
     */
    function star_count_post()
    {
        $return         = $this->return;
        $data           = $this->post_data; 
        if($this->form_validation->run('api/rating/star_count') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $module_id           = $data['ModuleID'];
            $module_entity_guid  = $data['ModuleEntityGUID'];
            $return['Data'] = $this->rating_model->get_star_count($module_id, $module_entity_guid);
        }
        $this->response($return);   
    }

    /**
     * [parameter_summary - average rating based on parameter]
     * @param  $module_id
     * @param  $module_entity_guid
     * @return [rating count according to parameter]
     */
    function parameter_summary_post()
    {
        $return         = $this->return;
        $data           = $this->post_data;   
        if($this->form_validation->run('api/rating/parameter_summary') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $module_id           = $data['ModuleID'];
            $module_entity_guid  = $data['ModuleEntityGUID'];
            $return['Data'] = $this->rating_model->get_parameter_summary($module_id,$module_entity_guid);
        }
        $this->response($return);   
    }

    function entitylist_post(){
        $return         = $this->return;
        $data           = $this->post_data;  
        $user_id        = $this->UserID;  
        if($this->form_validation->run('api/rating/entitylist') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $module_id           = $data['ModuleID'];
            $module_entity_guid  = $data['ModuleEntityGUID'];
            $module_entity_id = get_detail_by_guid($module_entity_guid,$module_id);
            if(!$this->rating_model->check_permission($user_id,$module_id,$module_entity_id))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $return['Data'] = $this->rating_model->entity_list($user_id,$module_id,$module_entity_id);
        }   
        $this->response($return);
    }

    /**
     * [list - list of ratings of particular entity]
     * @param  $module_id
     * @param  $module_entity_guid
     * @return [rating list]
     */
    function list_post()
    {
        $return         = $this->return;
        $data           = $this->post_data;  
        $user_id        = $this->UserID;  
        if($this->form_validation->run('api/rating/list') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $module_id           = $data['ModuleID'];
            $module_entity_guid  = $data['ModuleEntityGUID'];

            $module_entity_id    = get_detail_by_guid($module_entity_guid, $module_id);
            
            if(empty($module_id) || empty($module_entity_id))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                return $this->response($return);
            }

            $location       = isset($data['Location'])      ? $data['Location']     : '' ;
            $rating_guid    = isset($data['RatingGUID'])    ? $data['RatingGUID']   : '' ;
            $start_date     = isset($data['StartDate'])     ? $data['StartDate']    : '' ;
            $end_date       = isset($data['EndDate'])       ? $data['EndDate']      : '' ;
            $age_group      = isset($data['AgeGroup'])      ? $data['AgeGroup']     : '' ;
            $gender         = isset($data['Gender'])        ? $data['Gender']       : '' ;
            $admin_only     = isset($data['AdminOnly'])     ? $data['AdminOnly']    : 0 ;
            $sort_by        = isset($data['SortBy'])        ? $data['SortBy']       : '' ;
            $page_no        = isset($data['PageNo'])        ? $data['PageNo']       : 1 ;
            $page_size      = isset($data['PageSize'])      ? $data['PageSize']     : PAGE_SIZE ;

            $return['Data'] = $this->rating_model->get_rating_list($user_id, $module_id, $module_entity_id, $location, $start_date, $end_date, $age_group, $gender,$admin_only, $sort_by, $page_no, $page_size, 0,$rating_guid);
            $return['TotalRecords'] = $this->rating_model->get_rating_list($user_id, $module_id, $module_entity_id, $location, $start_date, $end_date, $age_group, $gender,$admin_only, $sort_by, $page_no, $page_size, 1,$rating_guid);
        }
        $this->response($return);   
    }

    function delete_post()
    {
        $return         = $this->return;
        $data           = $this->post_data;  
        $user_id        = $this->UserID;  
        if($this->form_validation->run('api/rating/delete') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $rating_guid = $data['RatingGUID'];
            if(!$this->rating_model->check_permission($user_id,'','',$rating_guid))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                return $this->response($return);
            }
            $this->rating_model->delete($user_id,$rating_guid);
        }
        $this->response($return);
    }
}
