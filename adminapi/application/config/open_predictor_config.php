<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['trending_prediction'] = array();						
$config['trending_prediction'][1] = array('func' => 'get_trending_prediction','sort_key'=>'PM.added_date');						
$config['trending_prediction'][2] = array('func' => 'get_trending_prediction','sort_key'=>'PM.total_user_joined');						
$config['trending_prediction'][3] = array('func' => 'get_bid_count_prediction','bid_count' => 1);						
$config['trending_prediction'][4] = array('func' => 'get_bid_count_prediction','bid_count' => 0);						

