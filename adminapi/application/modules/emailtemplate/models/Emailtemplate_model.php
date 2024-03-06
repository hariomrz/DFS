<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Emailtemplate_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
	}

    /**
     * [get_all_site_user_detail description]
     * @MethodName get_all_site_user_detail
     * @Summary This function used for get all user list and return filter user list
     * @param      boolean  [User List or Return Only Count]
     * @return     [type]
     */
    public function get_all_emailtemplate($count_only=FALSE)
    {
        $sort_field	= 'ET.template_name';
        $sort_order	= 'ASC';
        $limit		= 10;
        $page		= 0;
        $post_data	= $this->input->post();


        if(isset($post_data['items_perpage']))
        {
            $limit = $post_data['items_perpage'];
        }

        if(isset($post_data['current_page']))
        {
            $page = $post_data['current_page']-1;
        }

        if(isset($post_data['sort_field']) && in_array($post_data['sort_field'],array('template_path','template_name','subject','status','ET.date_added','modified_date')))
        {
            $sort_field = $this->input->post('sort_field');
        }

        if(isset($post_data['sort_order']) && in_array($post_data['sort_order'],array('DESC','ASC')))
        {
            $sort_order = $post_data['sort_order'];
        }

        $offset	= $limit * $page;
        $status	= isset($post_data['status']) ? $post_data['status'] : "";
        

        $query = $this->db->select('ET.*,DATE_FORMAT(ET.date_added,"'.MYSQL_DATE_TIME_FORMAT.'") as date_added',FALSE)
        ->from(EMAIL_TEMPLATE." AS ET")
        ->order_by($sort_field, $sort_order);
        
        if($status != "")
        {
            $this->db->where("ET.status","$status");
        }
        
        $tempdb = clone $this->db;
        $total = 0;
        $query = $this->db->get();
        $total = $query->num_rows();
       
        $tempdb->limit($limit,$offset);
        

        $sql = $tempdb->get();
        $result	= $sql->result_array();
        //echo $tempdb->last_query(); die;

        $result = ($result) ? $result : array();
        return array('result'=>$result,'total'=>$total);
    }

    

    /**
     * [update_emailtemplate_status description]
     * @MethodName update_emailtemplate_status
     * @Summary This function used to withdraw status
     * @param      [varchar]  [withdraw_transaction_id]
     * @param      [int]  [status]
     * @return     [boolean]
     */
    public function update_emailtemplate_status($date_array)
    {
        $this->db->where("email_template_id",$date_array['email_template_id']);
        $this->db->update(EMAIL_TEMPLATE,array('status'=>$date_array['status']));
        return $this->db->affected_rows() || true;
    }

    

    /**
     * @Summary: This function is used for add notification in databse
     * @access: public
     */
    function add_notification($notification_type_id, $sender_user_id = '0', $receiver_user_id, $notification = '', $game_id=0,$game_unique_id = '')
    {
        $data = array(
            'notification_type_id' => $notification_type_id,
            'sender_user_id'       => $sender_user_id,
            'receiver_user_id'     => $receiver_user_id,
            'notification'         => $notification,
            'contest_id'	       => $game_id,
            'contest_unique_id'    => $game_unique_id,
            'is_read'              => '0',
            'created_date'         => format_date(),
        );
        $this->db->insert($this->db->dbprefix(NOTIFICATION), $data);

    }



   public function get_order_detail($order_id)
    {
        return $this->db->where('order_id',$order_id)
            ->get(ETER,1)
            ->row_array();
    }
}

/* End of file Finance_model.php */
/* Location: ./application/models/Finance_model.php */