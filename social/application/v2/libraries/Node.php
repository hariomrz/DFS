<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Node
{
	public $postData = array();
	public $endPoint = NODE_ADDR;
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
		$curlUrl     = $this->endPoint.'/'.$this->route;
		$curl        = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_POST           => 1,
			CURLOPT_POSTFIELDS     => $data_string,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => $curlUrl,
			CURLOPT_SSL_VERIFYPEER => false
		));
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
}

/* End of file node.php */
/* Location: ./application/libraries/node.php */