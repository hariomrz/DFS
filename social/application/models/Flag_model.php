<?php
/**
 * This model is used for getting and storing user related information like login , registration
 * Model class for signup + login of Anotiste
 * @package    signup_model
 * @author     Jay Hardia  <jay.hardia@vinfotech.com>
 * @version    1.0
 *
 */
class Flag_model extends Common_Model
{

    protected $user_flagged = array();
    function __construct() 
    {
        parent::__construct();        
    }
	
    /**
     * [is_flagged Used to check the status of flag]
     * @param  [int] $user_id [User ID]
     * @param  [int] $entity_id [Entity ID]
     * @param  [string] $entity_type [Entity Type]
     * @return [boolean]         [description]
     */
    function is_flagged($user_id, $entity_id, $entity_type)
    {
        $entity_type = strtoupper($entity_type);
        
        if($entity_type=="ACTIVITY"){
            if (CACHE_ENABLE) {
                $row_ids = $this->cache->get('user_flagged_activity' . $user_id);
                if(!empty($row_ids)){
                    $user_favourite = explode(',',$row_ids);
                    return (in_array($entity_id, $user_favourite)) ? '1' : '0';
                }
            }
        }
        
        $this->db->where('UserID',$user_id);
        $this->db->where('EntityID',$entity_id);
        $this->db->where('EntityType',$entity_type);
        $this->db->where('StatusID','2');
        $sql = $this->db->get(FLAG);
        if($sql->num_rows())
        {
            return true;
        } 
        else 
        {
            return false;
        }
    }
    /**
     * [is_flagged Used to check the status of flag]
     * @param  [int] $user_id [User ID]
     * @param  [int] $entity_id [Entity ID]
     * @param  [string] $entity_type [Entity Type]
     * @return [boolean]         [description]
     */
    function set_user_flagged($user_id)
    {
        return;
        $entity_type = strtoupper('Activity');
        $this->db->select('GROUP_CONCAT(EntityID) as EntityIDs ');
        $this->db->where('UserID',$user_id);
        $this->db->where('EntityType',$entity_type);
        $this->db->where('StatusID','2');
        $query = $this->db->get(FLAG);
        if ($query->num_rows() > 0)
        {
            $row_ids=$query->row_array();
            if(!empty($row_ids['EntityIDs']))
            {
                $this->user_flagged=  explode(',',$row_ids['EntityIDs']);
            }
            /*foreach ($query->result_array() as $result)
            {
                $this->user_flagged[] = $result['EntityID'];
            }*/
        }
    }
    /**
     * 
     * @return type
     */
    public function get_user_flagged()
    {
        return $this->user_flagged;
    }
    
      /**
     * [is_flagged Used to check the status of flag]
     * @param  [int] $user_id [User ID]
     * @param  [int] $entity_id [Entity ID]
     * @param  [string] $entity_type [Entity Type]
     * @return [boolean]         [description]
     */
    function check_flagged( $entity_id, $entity_type)
    {
        $entity_type = strtoupper($entity_type);
        $this->db->where('EntityID',$entity_id);
        $this->db->where('EntityType',$entity_type);
        $this->db->where('StatusID','2');
        $sql = $this->db->get(FLAG);
        if($sql->num_rows())
        {
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /**
     * [set_flag Used to mark an entity as flag for current session user]
     * @param [array] $Data [entity details]
     */
    function set_flag($Data)
    {        
        $user_id     = $Data['UserID'];
        $entity_type = strtoupper($Data['EntityType']);
        $entity_guid = $Data['EntityGUID'];
        $flag_reason = $Data['FlagReason'];
        $flaggable  = 1;
        $return['Message']      = lang('success');
        $return['ResponseCode'] = 200;
        $created_date            = get_current_date('%Y-%m-%d %H:%i:%s');
        /*get EntityId by Entity GuID*/
        
        switch ($entity_type) 
        {
            case 'ACTIVITY':
                
                $entity = get_detail_by_guid($entity_guid, 0, "ActivityID, Flaggable, ActivityTypeID, ModuleEntityID", $ResponseType=2);
                if(empty($entity))
                {
                    $return['ResponseCode'] = 412;
                    $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
                    return $return;
                }
                $entity_id = $entity['ActivityID'];
                $flaggable = $entity['Flaggable'];
                if($entity['ActivityTypeID'] == '7')
                {
                    $this->load->model('group/group_model');
                    $admin_list = $this->group_model->get_all_group_admins($entity['ModuleEntityID']);
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $this->load->model('notification_model');
                    $this->notification_model->add_notification(65,$user_id,$admin_list,$entity['ActivityID'],$parameters);
                }
                if($entity['ActivityTypeID'] == '12')
                {
                    $this->load->model('pages/page_model');
                    $admin_list = $this->page_model->get_all_admins($entity['ModuleEntityID']);
                   // print_r($admin_list);die;
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $this->load->model('notification_model');
                    $this->notification_model->add_notification(65,$user_id,$admin_list,$entity['ActivityID'],$parameters);
                }
                break;
            case 'USER':
                $entity_id = get_detail_by_guid($entity_guid, 3);
                break; 
            case 'PAGE':
                $entity_id = get_detail_by_guid($entity_guid, 18);
                break;            
            case 'GROUP':
                $entity_id = get_detail_by_guid($entity_guid, 1);
                break;
            case 'RATING':
                $entity = get_detail_by_guid($entity_guid, 23, "RatingID, Status", $ResponseType=2);
                if(empty($entity))
                {
                    $return['ResponseCode'] = 412;
                    $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
                    return $return;
                }
                $entity_id = $entity['RatingID'];
                if($entity['Status'] == "APPROVED")
                {
                    $flaggable = 0;
                } 
                else 
                {
                    $this->load->model('ratings/rating_model');
                    $rating_admin = $this->rating_model->get_rating_entity_admin($entity_id);
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $this->load->model('notification_model');
                    $this->notification_model->add_notification(64,$user_id,$rating_admin,$entity_id,$parameters);
                }
                break;                           
            default:
                $return['ResponseCode'] = 412;
                $return['Message'] = sprintf(lang('valid_value'), "Entity Type");
                return $return;
                break;
        }    
                
        if(empty($entity_id))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            return $return;
        }
        /* End get EntityId */

        /* Check if this entiy is Flaggable or not */        
        if(empty($flaggable))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = lang('flaggable');
            return $return;
        }

        $this->db->select('FlagID');
        $this->db->where('EntityID', $entity_id);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get(FLAG);
        if($query->num_rows() == 0) 
        {
            $input = array(
                        'UserID' => $user_id, 
                        'EntityID' => $entity_id, 
                        'EntityType' => $entity_type, 
                        'FlagReason'   => $flag_reason,
                        'CreatedDate' => $created_date
                    );            
            $this->db->insert(FLAG, $input);
            if (CACHE_ENABLE) {
                $this->cache->delete('user_flagged_activity' . $user_id);
            }
        }
        return $return;
    }
}
?>
