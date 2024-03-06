<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * This Class used as REST API for Signout 
* @category     Controller
* @author       Vinfotech Team
*/

class Signout extends Common_API_Controller 
{

    function __construct() 
    {
        parent::__construct();
    }

    /**
     * Function Name: index
     
     * Description: Destroy user session
     */
    function index_post() 
    {
        /* Define variables - starts */
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Message'] = lang('success');
        $return['Data'] = array();
        $return['ServiceName'] = 'api/signout';
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        /* Gather Inputs - ends */
        if (isset($data)) 
        {
            /* Check provided JSON format is valid */
            $login_session_key = '';
            if (isset($data[AUTH_KEY]))
            {
                $login_session_key = $data[AUTH_KEY];
            }
            
            /* Validation - starts */
            if ($this->form_validation->required($login_session_key) == FALSE) 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('not_authorized');
            } 
            else /* Validation - ends */
            {
                /* Delete LoginSessionKey from DB - starts */
                $this->db->where('LoginSessionKey', $login_session_key);
                $this->db->delete(ACTIVELOGINS);
                /* Delete LoginSessionKey from DB - ends */

                $this->db->where('LoginSessionKey', $login_session_key);
                $query = $this->db->get(SESSIONLOGS);

                if ($query->num_rows()) 
                {
                    $rw = $query->row();
                    $mins = ((strtotime(get_current_date('%Y-%m-%d %H:%i:%s')) - strtotime($rw->StartDate)) / 60);
                }
                $this->db->where('LoginSessionKey', $login_session_key);
                $this->db->update(SESSIONLOGS, array('EndTime' => get_current_date('%Y-%m-%d %H:%i:%s'), 'duration' => $mins));
            }
        } 
        else 
        {
            /* Error - Invalid JSON format */
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }
}