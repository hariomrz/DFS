<?php

/**
 * Description of Category
 *
 * @author nitins
 */
class Category extends MY_Controller
{

    public $page_name = "";

    public function __construct()
    {
        parent::__construct();
        $this->base_controller = get_class($this);
        $this->show_date_filter = false;
        if ($this->session->userdata('AdminLoginSessionKey') == '')
        {
            redirect('admin');
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
        $data['content_view'] = 'admin/category/list';
        $this->page_name = "category";

        $this->load->view($this->layout, $data);
    }

    public function download_category_format() {
        $fileName = 'CategoryDataSampleFormat.xls';
        //echo $result->MediaType;
        $url = base_url() . 'upload/csv_file/' . $fileName;
        set_time_limit(0);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $r = curl_exec($ch);
        curl_close($ch);
        $this->load->helper('download'); //load helper
        force_download($fileName, $r);
    }

}
