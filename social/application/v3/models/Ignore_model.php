<?php
/**
 * This model is used for marking any entity as Ignore
 * Model class for to mark any entity as Ignore 
 * @package    Ignore_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */

class Ignore_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }
    /**
     * [ignore Used to mark an entity as ignore so that it will not show in suggestion list]
     * @param  [int] $user_id        [User ID]
     * @param  [string] $entity_type [EntityType]
     * @param  [int] $entity_id      [Entity ID]
     */
    public function ignore($user_id, $entity_type, $entity_id)
    {
    	$query = $this->db->get_where(IGNORE,array('UserID'=>$user_id,'EntityType'=>$entity_type,'EntityID'=>$entity_id));
    	if(!$query->num_rows())
        {
    		$insertArray = array('IgnoreGUID'=>get_guid(), 'EntityID'=>$entity_id, 'EntityType'=>$entity_type, 'UserID'=>$user_id, 'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));
    		$this->db->insert(IGNORE, $insertArray);
    	}
    }
    /**
     * [get_ignored_list Used to get ignore entity]
     * @param  [int] $user_id        [User ID]
     * @param  [string] $entity_type [EntityType]
     * @return [array]              [array of ignore entity]
     */
    public function get_ignored_list($user_id, $entity_type)
    {
    	$arr = array();
    	$this->db->select('EntityID');
    	$this->db->where('UserID', $user_id);
    	$this->db->where('EntityType', $entity_type);
    	$query = $this->db->get(IGNORE);
    	if($query->num_rows())
        {
    		foreach($query->result_array() as $row)
            {
    			$arr[] = $row['EntityID'];
    		}
    	}
    	return $arr;
    }
}