<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * All user registration and sign in process
 * Controller class for sign up and login  user of cnc
 * @package    signup
 
 * @version    1.0
 */

class Ajax extends Common_Controller 
{

	public function __construct()
	{
		parent::__construct();
	}

	public function change_language()
	{
		$data = $this->input->post();
		set_user_language($data['UserGUID'],$data['lang']);
		$this->session->set_userdata('language',$this->input->post('lang')); 
		echo lang('menu_dashboard');
	}

	public function change_autoplay()
	{
		$data = $this->input->post();
		set_video_autoplay($data['UserGUID'],$data['autoplay']);
	}
}