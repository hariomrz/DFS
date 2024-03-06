<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Scoring_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
		$this->db_fantasy = $this->load->database('db_fantasy', TRUE);
	}
	/**
     * @Summary: This function for use get scoring rule by cat_id and format
     * @access: protected
     * @param: $cat_id 
     * @return: resuly array
     */
    public function get_scoring_rules_by_con($con = array())
    {
    		$this->db_fantasy->select('master_scoring_id, score_position, score_points, format,MSC.sports_id')
                ->from(MASTER_SCORING_RULES." AS MSR")
            
            ->join(MASTER_SCORING_CATEGORY.' AS MSC','MSC.master_scoring_category_id = MSR.master_scoring_category_id','LEFT');

            if(!empty($con))
           	$this->db_fantasy->where($con);
            $this->db_fantasy->order_by("MSR.master_scoring_id","ASC");
           	$rs =   $this->db_fantasy->get()->result_array();   
            //echo $this->db->last_query();
        return $rs;
    }	
	/**
	 * @Summary: This function for use get scoring formula wich is store in master_scoring_category table    
	 * @access: protected
	 * @param: $league_id 
	 * @return: resuly array
	 */
	function get_scoring_rules($sports_id, $format = '')
	{

		/* $this->db_fantasy->select('ms.score_points, ms.master_scoring_category_id, ms.format, ms.meta_key, msc.scoring_category_name')
				->from(MASTER_SCORING_RULES . " AS ms")
				->join(MASTER_SCORING_CATEGORY . " AS msc", "msc.master_scoring_category_id= ms.master_scoring_category_id", "left")
				->where('msc.sports_id', $sports_id);

		if (!empty($format))
		{
			$this->db_fantasy->where('ms.format', $format);
		}

		$rs = $this->db_fantasy->get();

		$raw_formula_data = $rs->result_array();
		//echo $this->db_fantasy->last_query();die;

		$formula = array();
		foreach ($raw_formula_data as $val)
		{
			$formula[$val['scoring_category_name']][$val['meta_key']] = $val['score_points'];
		}

		return $formula; */
		$this->db_fantasy->select('master_scoring_id, score_position, score_points, format,MSC.sports_id')
                ->from(MASTER_SCORING_RULES." AS MSR")
            
            ->join(MASTER_SCORING_CATEGORY.' AS MSC','MSC.master_scoring_category_id = MSR.master_scoring_category_id','LEFT');

            if(!empty($con))
           	$this->db_fantasy->where($con);
            $this->db_fantasy->order_by("MSR.master_scoring_id","ASC");
           	$rs =   $this->db_fantasy->get()->result_array();   
            //echo $this->db->last_query();
		return $rs;
		
	}
	/**
	 * [update_batch description]
	 * @MethodName update_batch
	 * @param String $table
	 * @param String $key
	 * @param Array/String $update_data
	 * @Summary common function used to update batch
	 * @return     [type]
	 **/

	public function update_batch($table, $update_data, $key)
	{
		if(!empty($table) && !empty($update_data) && !empty($key))
		{
			if ($this->db_fantasy->field_exists($key, $table))
			{
				$this->db_fantasy->update_batch($table, $update_data, $key);
				return true; 
			}
			else
			{
				return false;		
			}
		}
		return false;
	}
	
}
/* End of file Scoring_model.php */
/* Location: ./application/models/Scoring_model.php */
