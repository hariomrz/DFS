<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Node
{
	public $postData = array();
	public $endPoint = NODE_BASE_URL;
	public $route    = "";

	public function __construct( $param = array() )
	{
		if( $param && is_array( $param ) )
		{
			$this->init( $param );
		}
	}

	public function init( $param = array() )
	{
		if( $param && is_array( $param ) )
		{
			foreach ($param as $key => $value) {
				$this->$key = $value;
			}
			$this->index();
		}
	}

	public function index(){
		$data_string = json_encode($this->postData);
		$curlUrl     = $this->endPoint.$this->route;
		//log_message('error', 'Route => '.$this->route.' Node data => '.$data_string);		
		try {
			$header = array("Content-Type:application/json",
		 	 "Accept:application/json",
		 	  "User-Agent:Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0"
		 	);

			$curl        = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_POST           => 1,
				CURLOPT_HTTPHEADER     => $header,
				CURLOPT_POSTFIELDS     => $data_string,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL            => $curlUrl,
				CURLOPT_SSL_VERIFYPEER => false
			));
			$result = curl_exec($curl);

			if ($result === false) {
				$error = curl_error($curl);
				//log_message('error', 'Node Connection error: ' . $error);
			}

			curl_close($curl);
			return true;
		} catch (Exception $e) {
			//log_message('error', 'Node Connection Issue - ' . $e->getMessage());
			return true;
		}
	}
}

/* End of file node.php */
/* Location: ./application/libraries/node.php */