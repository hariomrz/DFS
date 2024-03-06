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
     * @param [type] $data [input data for Favourite Request]
     */
    function toggle_favourite($data){

        $status_id          = 2; 
        $count              = 1;
        $created_date       = get_current_date('%Y-%m-%d %H:%i:%s');
        $user_id            = $data['UserID'];
        
        $entity_type        = $data['EntityType'];
            
        $entity_id          = $data['EntityID'];
        $module_entity_id   = $data['ModuleEntityID'];
        $module_id          = $data['ModuleID'];
        
        if(IS_ARCHIVE_DB == 1 && $entity_type == 'ACTIVITY') {
            $this->load->model(array('archival/restore_model'));
            $this->restore_model->sync_entity("ACTIVITY", $entity_id);
        }

        /* End get EntityId */
        $this->current_db->select('FavouriteID, StatusID');
        $this->current_db->where(
                            array(
                                'UserID'    => $user_id,
                                'EntityID'  => $entity_id,
                                'EntityType'  => $entity_type,
                                'ModuleID'  => $module_id, 
                                'ModuleEntityID' => $module_entity_id
                            )
                        );
        $query = $this->current_db->get(FAVOURITE);
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
        $this->update_favourite_count($entity_id, $entity_type, $count);
        
        if (CACHE_ENABLE) {
            $this->cache->delete('user_favourite_activity' . $user_id);
        }
        //$total_favourites = $this->get_favourites_count($module_id, $module_entity_id, $entity_id, $user_id);
        //$return['Data'] = array("NoOfFavourites" => $total_favourites);        
    }

    
    /**
     * [updateUserPostCount description]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [string] $entity_type   [Entity Type]
     * @param  int      $count      [Favourite Count increment/decrement]
     * @return [type]               [description]
     */
    public function update_favourite_count($entity_id, $entity_type="ACTIVITY", $count=1){

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
            $this->db->limit(1);
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
            if (CACHE_ENABLE && empty(IS_ARCHIVE_DB)) {
                $row_ids = $this->cache->get('user_favourite_activity' . $user_id);
                if(!empty($row_ids)){
                    $user_favourite = explode(',',$row_ids);
                    return (in_array($entity_id, $user_favourite)) ? '1' : '0';
                }
            }
        }
        
        $this->current_db->where('EntityID',$entity_id);
        $this->current_db->where('EntityType',$entity_type);
        $this->current_db->where('UserID',$user_id);
        $this->current_db->where('StatusID','2');
        $this->current_db->limit(1);
        $query = $this->current_db->get(FAVOURITE);
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
        $row_ids = '';
        if (CACHE_ENABLE && empty(IS_ARCHIVE_DB)) {
            $row_ids = $this->cache->get('user_favourite_activity' . $user_id);
        }
        if(empty($row_ids)){
            $this->current_db->select('GROUP_CONCAT(EntityID) as EntityIDs ');
            $this->current_db->where('EntityType','ACTIVITY');
            $this->current_db->where('UserID',$user_id);
            $this->current_db->where('StatusID','2');
            $query = $this->current_db->get(FAVOURITE);
            $fav_ids = '-1';
            if ($query->num_rows() > 0) {
                $row_ids=$query->row_array();
                $fav_ids = $row_ids['EntityIDs'];
                if(!empty($fav_ids)) {                    
                    $this->user_favourite=  explode(',',$row_ids['EntityIDs']);
                }
            }
            if (CACHE_ENABLE && empty(IS_ARCHIVE_DB)) {
                $this->cache->save('user_favourite_activity' . $user_id, $fav_ids, CACHE_EXPIRATION);
            }
        }else if($row_ids != '-1') {
            $this->user_favourite=  explode(',',$row_ids);
        }
    }
    /**
     * 
     * @return type
     */
    public function get_user_favourite() {
        return $this->user_favourite;
    }
}
?>
