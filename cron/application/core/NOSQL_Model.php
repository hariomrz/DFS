<?php if ( ! defined('BASEPATH')) { exit('No direct script access allowed');}
class NOSQL_Model extends CI_Model {

    function __construct()
    {
        // Load Mongo Library
        $this->load->library('mongo_db');
    }

    function __destruct()
    {
        //$this->db->close();
    }
    /**
	 * Used for insert data in nosql
	 * @param array $data
	 * @param string $table document name
	 * @return 
	 */
    public function insert_nosql($table,$data){
    	if(!$table || empty($data)){
            return false;
        }

        if(in_array($table, [MANAGE_OTP])){
            $data['insert_date_time'] = $this->normal_to_mongo_date(date('Y-m-d H:i:s'));
        }

    	$result = $this->mongo_db->insert($table,$data);
    	return $result;
    }


    /**
	 * Used for Last insert data in nosql
	 * @param array $data
	 * @param string $table document name
	 * @return 
	 */
    public function insert_id_nosql($table){
    	if(!$table){
            return false;
        }
        $this->mongo_db->order_by(array('_id' => 'desc'));	
    	$result = $this->mongo_db->find_one($table);

    	if(!empty($result)){
    		return $result[0]['_id'];
    	}else{
    	 return FALSE;
    	}
    }
    /**
	 * Used for select documents in nosql
	 * @param array $where
	 * @param string $table document name
	 * @param Int $limit
	 * @param Int $offset
	 * @return 
	 */
    public function select_nosql($table,$where="",$limit=NULL,$offset=NULL,$sort_by="",$order_by=""){
    	if(!$table){
            return false;
        }
    	if(!empty($limit)){
    		$this->mongo_db->limit($limit);
    	}
    	if(!empty($offset)){
    		$this->mongo_db->offset($offset);
    	}
    	if(!empty($where)){
    		$this->mongo_db->where($where);
    	}
        if(!empty($order_by) && !empty($sort_by))
        {
            $this->mongo_db->order_by(array($sort_by => $order_by));
        }

        //[['$match'=> [ '$expr'=> [ '$in'=> ["notification_type", '$$notification_type'] ]]]]
/*       $ag = array('$lookup' => 
                    array(  "let"       =>  array('notification_type' => '$notification_type'),
                            "from"      =>  "notification_description",
                            "pipeline"  =>  array('$match' => array('$expr' => array('$in' => array('$notification_type' , 'notification_description.notification_type'))),
                                '$project' => array('notification_description' => 1)
                        ) ,
                            "as"        =>  "notify"
                        )
                );
*/
      //echo json_encode($ag); die;

       $result =  $this->mongo_db->get($table);
    	return $result;
    }

    /**
	 * Used for select single record in nosql
	 * @param array $where
	 * @param string $table document name
	 * @return 
	 */
    public function select_one_nosql($table,$where,$sort_by="",$order_by="")
    {
    	if(!$table || empty($where)){
            return false;
        }

        if(!empty($order_by) && !empty($sort_by))
        {
            $this->mongo_db->order_by(array($sort_by => $order_by));
        }

    	$result = $this->mongo_db->where($where)->find_one($table);
    	if(!empty($result)){
    		return $result[0];
    	}else{
    	 return FALSE;
    	}
    }

    /**
	 * Used for update record of single document
	 * @param string $table document name
	 * @param array $where
	 * @param array $set
	 * @return 
	 */
    public function update_nosql($table,$where,$set)
    {
    	if(!$table || empty($where) || empty($set)){
            return false;
        }
    	$result = $this->mongo_db->where($where)->set($set)->update($table);
		return $result;
    }

        /**
     * Used for update_all_nosql record in multiple document
     * @param string $table document name
     * @param array $where
     * @param array $set
     * @return 
     */
    public function update_all_nosql($table,$where,$set)
    {
        if(!$table || empty($where) || empty($set)){
            return false;
        }
        $result = $this->mongo_db->where($where)->set($set)->update_all($table);
        return $result;
    }

    /**
	 * Used to delete record in nosql
	 * @param string $table document name
	 * @param array $where
	 * @return 
	 */
    public function delete_nosql($table,$where)
    {
    	if(!$table || empty($where)){
            return false;
        }
    	$result = $this->mongo_db->where($where)->delete($table);
		return $result;
    }

    public function get_object_id($id)
    {
        return $this->mongo_db->set_object($id);
    }


    public function num_rows($table,$where)
    {
        if(!$table || empty($where)){
            return false;
        }
        $result = $this->mongo_db->where($where)->count($table);
        return $result;
    }

    public function mongo_to_normal_date($mongo_date)
    {
         /********************retrieve time in UTC**********************************/
         $datetime = $mongo_date->toDateTime();
         $time=$datetime->format(DATE_RSS);
         /********************Convert time local timezone*******************/
         $dateInUTC=$time;
         $time = strtotime($dateInUTC.' UTC');
         $dateInLocal = date("Y-m-d H:i:s", $time);
         return $dateInLocal;
    }

    public function normal_to_mongo_date($normal_date)
    {
       return new MongoDB\BSON\UTCDatetime(strtotime($normal_date) * 1000);
    }

    public function aggregate($table,$ops)
	{
		return $this->mongo_db->aggregate($table,$ops);
	}

}