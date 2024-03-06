<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<meta charset="utf-8" />
<title><?php echo SITE_NAME;if(isset($title) && $title!='')echo " - ".$title; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta content="" name="description" />
<meta content="" name="author" />
<base href="<?php echo site_url();?>" />
<?php //print_r($this->session->all_userdata()) ?>
<script type="text/javascript">
var base_url='<?php echo base_url(); ?>';
var accept_language = '<?php echo $this->config->item("language") ?>';
var image_server_path = '<?php echo IMAGE_SERVER_PATH ?>';
var site_name = '<?php echo SITE_NAME ?>';
</script>
<link id="page_favicon" href="favicon.ico" rel="icon" type="image/x-icon" />
<link href="apple-touch-icon-precomposed.png" rel="apple-touch-icon" />
<?php $this->load->view('include/all_css'); ?>
<script src="<?php echo ASSET_BASE_URL ?>js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
</head>   
<script type="text/javascript">				 
    var FacebookAppId             = '<?= defined('FACEBOOK_APP_ID') ? FACEBOOK_APP_ID : null; ?>';
    var site_url                = '<?= site_url(); ?>';
    var google_client_id         = '<?= defined('CLIENT_ID') ? CLIENT_ID : null; ?>';
    var google_scope             = '<?= defined('SCOPE') ? SCOPE : null; ?>';
    var google_api_key          = '<?= defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : null; ?>';
</script>
<?php 
//For add analytcs provider code in header 
$AnalyticsCode = $this->config->item("AnalyticsCode");
if(isset($AnalyticsCode)){
    foreach($AnalyticsCode as $code){
        echo $code;
    }
}

?>
<?php 
    $this->load->view('include/before-login'); 
?>