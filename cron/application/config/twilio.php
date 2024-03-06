<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	* Name:  Twilio
	*
	* Author: Ben Edmunds
	*		  ben.edmunds@gmail.com
	*         @benedmunds
	*
	* Location:
	*
	* Created:  03.29.2011
	*
	* Description:  Twilio configuration settings.
	*
	*
	*/

	/**
	 * Mode ("sandbox" or "prod")
	 **/
	$config['mode']   = 'sandbox';

	/**
	 * Account SID
	 **/
	$config['account_sid']   = 'AC516d66e571f73fc64ec1de0e4ec01808'; //akhilesh
	// $config['account_sid']   = 'AC9d3f9bfada5e6efb3bf61f4ee31519a1'; // client test

	/**
	 * Auth Token
	 **/
	$config['auth_token']    = 'a1759eebb95e38d69719540a7132dd03'; //akhilesh
	// $config['auth_token']    = 'a1759eebb95e38d69719540a7132dd03'; // client test

	/**
	 * API Version
	 **/
	$config['api_version']   = '2010-04-01';

	/**
	 * Twilio Phone Number
	 **/
	$config['number']        = '+12018013620';


/* End of file twilio.php */