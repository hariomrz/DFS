<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class StaticPage extends Common_Controller
{
    public $dashboard = '';
    public $layout = '';



    public function termsAndCondition()
    {
        $this->data['title'] = 'Terms and conditions';
        $this->page_name = 'terms';
        $this->data['pname'] = 'terms';
        $this->data['content_view'] = 'staticpages/terms-condition';

        $this->load->view($this->layout, $this->data);
    }

}