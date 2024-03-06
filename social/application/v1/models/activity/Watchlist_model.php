<?php
/**
 * This model is used for making any entity as watchlist
 * Model class for to make any entity as Watchlist 
 * @package    watchlist_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Watchlist_model extends Common_Model {
    
    protected $user_watchlist = array();
    function __construct() {
        parent::__construct();
    }

    /**
     * [toggle_watchlist Used to mark an entity as watchlist for current session user]
     * @param [type] $Data [input data for Watchlist Request]
     */
    function toggle_watchlist($Data){ 
        $return['Message'] = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data'] = array();

        $status_id           = 2; 
        $count              = 1;
        $created_date        = get_current_date('%Y-%m-%d %H:%i:%s');
        $user_id             = $Data['UserID'];
        $entity_guid         = $Data['ActivityGUID'];        
        $module_id           = 0;    
        $select_field        = 'ActivityID, ModuleEntityID, ModuleID';
                
        /* get EntityId and ModuleEntityID by Entity GuID & Module Entity GUID*/
        $entity_data        = get_detail_by_guid($entity_guid, $module_id, $select_field, 2); 
        $entity_id          = $entity_data['ActivityID'];
        $module_entity_id   =  $entity_data['ModuleEntityID'];
        $module_id          =  $entity_data['ModuleID'];
        
        //$module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
        if(empty($entity_id)){
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            return $return;
        }

        $is_watchlist = $this->is_watchlist($entity_id,$user_id);
        if($is_watchlist)
        {
            $this->db->where('ActivityID',$entity_id);
            $this->db->where('UserID',$user_id);
            $this->db->delete(WATCHLIST);
            $count = 0;
        }
        else
        {
            $watchlist_guid = get_guid();
            $input = array(
                            'WatchListGUID' => $watchlist_guid, 
                            'UserID'        => $user_id, 
                            'ActivityID'    => $entity_id,
                            'CreatedDate'   => $created_date
                        );            
            $this->db->insert(WATCHLIST, $input);
            //update mydesk task status 
            $this->load->model('activity/mydesk_model');
            if($this->mydesk_model->is_mydesk_task($entity_id, $user_id))
            {
                $update_array['Status'] = 'NOTDONE';       
                $update_array['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->where('ActivityID',$entity_id);
                $this->db->where('UserID',$user_id);
                $this->db->update(MYTASKSTATUS,$update_array);
            }
            $count = 1;
        }        
        
        $return['Data'] = $count;//array("NoOfWatchlists" => $total_watchlists);
        return $return;
    }

    
    /**
     * [updateUserPostCount description]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  int      $count      [watchlist Count increment/decrement]
     * @return [type]               [description]
     */
    public function update_watchlist_count($entity_id, $count=1){

        $set_field  = "NoOfWatchlists";         
        $table_name = ACTIVITY;
        $condition  = array("ActivityID" => $entity_id);
        
        $this->db->where($condition);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update($table_name);  
    }
    /**
     * [get_watchlists_count Used to get the count of watchlists based on inputted request ]
     * @param  [int]    $module_id     [Module ID]
     * @param  [int]    $module_entity_id     [Module Entity ID]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [int]    $user_id     [User ID]
     * @return [int]                [total count]
     */
    function get_watchlists_count($activity_id, $user_id=0){
        $total_count = 0;
        if(!empty($user_id)) {
            $this->db->select('WatchListID');
            $this->db->from(WATCHLIST);
            $this->db->where(
                                array(
                                        'ActivityID' => $activity_id, 
                                        'UserID' => $user_id 
                                    )
                            );
            $total_count = $this->db->count_all_results();
        } else {
            $this->db->select("NoOfWatchlists");
            $this->db->from(ACTIVITY);
            $this->db->where(array('ActivityID' => $entity_id));
            $query = $this->db->get();   
            if($query->num_rows() > 0){
                $total_count = $query->row()->NoOfWatchlists;      
            }     
        }        
        return $total_count;
    }

    /**
     * [is_watchlist Used to check user mark entity as watchlist or not]
     * @param  [int]  $entity_id   [Entity ID]
     * @param  [int]  $user_id     [User ID]
     * @param  [string]  $entity_type [Entity Type]
     * @return boolean              [True - 1/False - 0]
     */
    function is_watchlist($activity_id, $user_id){
        $this->db->where('ActivityID',$activity_id);
        $this->db->where('UserID',$user_id);        
        $query = $this->db->get(WATCHLIST);
        if($query->num_rows()){
            return '1';
        } else {
            return '0';
        }
    }
    /**
     * [is_watchlist Used to check user mark entity as watchlist or not]
     * @param  [int]  $entity_id   [Entity ID]
     * @param  [int]  $user_id     [User ID]
     * @param  [string]  $entity_type [Entity Type]
     * @return boolean              [True - 1/False - 0]
     */
    function set_user_watchlist($user_id){
        $this->db->select('GROUP_CONCAT(ActivityID) as ActivityIDs ');        
        $this->db->where('UserID',$user_id);
        $query = $this->db->get(WATCHLIST);
       if ($query->num_rows() > 0)
        {
            $row_ids=$query->row_array();
            if(!empty($row_ids['ActivityIDs']))
            {
                $this->user_watchlist=  explode(',',$row_ids['ActivityIDs']);
            }
            /*foreach ($query->result_array() as $result)
            {
                $this->user_favourite[] = $result['EntityID'];
            }*/
        }
    }
    /**
     * 
     * @return type
     */
    public function get_user_watchlist()
    {
        return $this->user_watchlist;
    }
}
?>