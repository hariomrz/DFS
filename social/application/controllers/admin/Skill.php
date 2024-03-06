<?php

/**
 * Description of Category
 *
 * @author nitins
 */
class Skill extends MY_Controller
{

    public $page_name = "";

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);

        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect();
        }
    }

    public function index()
    {
        //Check logged in access right and allow/denied access
        if (!in_array(getRightsId('category_admin'), getUserRightsData($this->DeviceType)))
        {
            redirect('access_denied');
        }

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/skill/manage-skill';
        $this->page_name = "skill";

        $this->load->view($this->layout, $data);
    }

    public function merge_skill()
    {
        //Check logged in access right and allow/denied access
        if (!in_array(getRightsId('category_admin'), getUserRightsData($this->DeviceType)))
        {
            redirect('access_denied');
        }

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        $data['content_view'] = 'admin/skill/merge-skill';
        $this->page_name = "skill";

        $this->load->view($this->layout, $data);
    }
    public function add_skill($id='')
    {
        //Check logged in access right and allow/denied access
        if (!in_array(getRightsId('category_admin'), getUserRightsData($this->DeviceType)))
        {
            redirect('access_denied');
        }

        $data = array();
        $data['global_settings'] = $this->config->item("global_settings");

        /* View File */
        if($id){
             $data['content_view'] = 'admin/skill/edit-skill';
        }else{
             $data['content_view'] = 'admin/skill/add-skill';
        }
       
        $data['skill_id'] =$id;
        $this->page_name = "skill";

        $this->load->view($this->layout, $data);
    }

}
