<?php
/**
 * This model is used for making any entity as Favourite
 * Model class for to make any entity as Favourite 
 * @package    favourite_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Favourite_model extends Common_Model {
    
    protected $user_favourite = array();
    function __construct() {
        parent::__construct();
    }

    /**
     * [toggle_favourite Used to mark an entity as favourite for current session user]
     * @param [type] $Data [input data for Favourite Request]
     */
    function toggle_favourite($Data){
        $return['Message'] = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data'] = array();

        $status_id           = 2; 
        $count              = 1;
        $created_date        = get_current_date('%Y-%m-%d %H:%i:%s');
        $user_id             = $Data['UserID'];
        $entity_guid         = $Data['EntityGUID'];
        $entity_type         = $Data['EntityType'];
        $module_id           = 0;    
        $select_field        = 'ActivityID, ModuleEntityID, ModuleID';
        /*switch ($entity_type) {
            case 'PAGE':
                $module_id = 18;
                $select_field = 'PageID as ActivityID, PageID as ModuleEntityID';
                break;            
            default:
                $module_id = 0;
                break;
        }*/
                
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
        
        /* End get EntityId */
        $this->db->select('FavouriteID, StatusID');
        $this->db->where(
                            array(
                                'UserID'    => $user_id,
                                'EntityID'  => $entity_id,
                                'EntityType'  => $entity_type,
                                'ModuleID'  => $module_id, 
                                'ModuleEntityID' => $module_entity_id
                            )
                        );
        $query = $this->db->get(FAVOURITE);
        if($query->num_rows() > 0) {
            $row = $query->row_array();            
            if($row['StatusID'] == 2) {
                $status_id = 3;
                $count = -1;
            }
            $this->db->where('FavouriteID',$row['FavouriteID'])
                    ->update(FAVOURITE,array('StatusID' => $status_id, 'ModifiedDate' => $created_date));
        } else {
            $favourite_guid = get_guid();
            $input = array(
                            'FavouriteGUID' => $favourite_guid, 
                            'UserID'        => $user_id, 
                            'EntityID'      => $entity_id,
                            'EntityType'    => $entity_type, 
                            'ModuleID'      => $module_id, 
                            'ModuleEntityID'=> $module_entity_id, 
                            'CreatedDate'   => $created_date, 
                            'ModifiedDate'  => $created_date
                        );            
            $this->db->insert(FAVOURITE, $input); 
        }
        $this->update_favourite_count($entity_id, $count, $entity_type);
        $total_favourites = $this->get_favourites_count($module_id, $module_entity_id, $entity_id, $user_id);
        if (CACHE_ENABLE) {
            $this->cache->delete('user_favourite_activity' . $user_id);
        }
        $return['Data'] = array("NoOfFavourites" => $total_favourites);
        return $return;
    }

    
    /**
     * [updateUserPostCount description]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  int      $count      [Favourite Count increment/decrement]
     * @return [type]               [description]
     */
    public function update_favourite_count($entity_id, $count=1, $entity_type="ACTIVITY"){

        $set_field  = "NoOfFavourites"; 
        if($entity_type == "ACTIVITY"){
            $table_name = ACTIVITY;
            $condition  = array("ActivityID" => $entity_id);
        } else {
            $table_name = MEDIA;
            $condition  = array("MediaID" => $entity_id);
        }
        
        $this->db->where($condition);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update($table_name);  
    }
    /**
     * [get_favourites_count Used to get the count of Favourites based on inputted request ]
     * @param  [int]    $module_id     [Module ID]
     * @param  [int]    $module_entity_id     [Module Entity ID]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [int]    $user_id     [User ID]
     * @return [int]                [total count]
     */
    function get_favourites_count($module_id, $module_entity_id, $entity_id, $user_id=0){
        $total_count = 0;
        if(!empty($user_id)) {
            $this->db->select('FavouriteID');
            $this->db->from(FAVOURITE);
            $this->db->where(
                                array(
                                        'ModuleID' => $module_id, 
                                        'ModuleEntityID' => $module_entity_id, 
                                        'UserID' => $user_id, 
                                        'StatusID' => 2
                                    )
                            );
            $total_count = $this->db->count_all_results();
        } else {
            $this->db->select("NoOfFavourites");
            $this->db->from(ACTIVITY);
            $this->db->where(array('ActivityID' => $entity_id));
            $query = $this->db->get();   
            if($query->num_rows() > 0){
                $total_count = $query->row()->NoOfFavourites;      
            }     
        }        
        return $total_count;
    }

    /**
     * [is_favourite Used to check user mark entity as favourite or not]
     * @param  [int]  $entity_id   [Entity ID]
     * @param  [int]  $user_id     [User ID]
     * @param  [string]  $entity_type [Entity Type]
     * @return boolean              [True - 1/False - 0]
     */
    function is_favourite($entity_id, $user_id, $entity_type="ACTIVITY"){
        if($entity_type=="ACTIVITY"){
            if (CACHE_ENABLE) {
                $row_ids = $this->cache->get('user_favourite_activity' . $user_id);
                if(!empty($row_ids)){
                    $user_favourite = explode(',',$row_ids);
                    return (in_array($entity_id, $user_favourite)) ? '1' : '0';
                }
            }
        }
        
        $this->db->where('EntityID',$entity_id);
        $this->db->where('EntityType',$entity_type);
        $this->db->where('UserID',$user_id);
        $this->db->where('StatusID','2');
        $query = $this->db->get(FAVOURITE);
        if($query->num_rows()){
            return '1';
        } else {
            return '0';
        }
    }
    /**
     * [is_favourite Used to check user mark entity as favourite or not]
     * @param  [int]  $entity_id   [Entity ID]
     * @param  [int]  $user_id     [User ID]
     * @param  [string]  $entity_type [Entity Type]
     * @return boolean              [True - 1/False - 0]
     */
    function set_user_favourite($user_id){
        if($this->settings_model->isDisabled(16)) {
            $this->user_favourite = array();
            return;
        }
        if (CACHE_ENABLE) {
            $row_ids = $this->cache->get('user_favourite_activity' . $user_id);
        }
        if(empty($row_ids)){
            $this->db->select('GROUP_CONCAT(EntityID) as EntityIDs ');
            $this->db->where('EntityType','ACTIVITY');
            $this->db->where('UserID',$user_id);
            $this->db->where('StatusID','2');
            $query = $this->db->get(FAVOURITE);
            if ($query->num_rows() > 0)
            {
                $row_ids=$query->row_array();
                if(!empty($row_ids['EntityIDs']))
                {
                    if (CACHE_ENABLE) {
                        $this->cache->save('user_favourite_activity' . $user_id, $row_ids['EntityIDs'], CACHE_EXPIRATION);
                    }
                    $this->user_favourite=  explode(',',$row_ids['EntityIDs']);
                }
            }
        }else{
            $this->user_favourite=  explode(',',$row_ids);
        }
    }
    /**
     * 
     * @return type
     */
    public function get_user_favourite()
    {
        return $this->user_favourite;
    }
}
?>
