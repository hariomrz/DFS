<!DOCTYPE html>
<html ng-app="App" ng-strict-di >
<head>
<meta http-equiv="cache-control" content="no-cache,no-store,private" />
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<meta charset="utf-8" />
<?php if($this->session->userdata('UserID')){ ?>
    <title ng-if="TotalNotificationCount!==0 && TotalNotificationCount>0" ng-bind="'('+TotalNotificationCount+')'+' <?php echo SITE_NAME;if(isset($title) && $title!='')echo " - ".$title; ?>'" ng-cloak><?php echo SITE_NAME;if(isset($title) && $title!='')echo " - ".$title; ?></title>
    <title ng-if="TotalNotificationCount==0" ng-cloak><?php echo SITE_NAME;if(isset($title) && $title!='')echo " - ".$title; ?></title>
<?php } else { ?>
    <title><?php echo SITE_NAME;if(isset($title) && $title!='')echo " - ".$title; ?></title>
<?php } ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="author" content="{{lang.web_name}}"  />
<?php if(isset($this->meta_description) && $this->meta_description!=''): ?>
    <meta name="description" itemprop="description" content="<?php echo $this->meta_description ?>">    
<?php else: ?>    
    <meta name="description" itemprop="description" content="A framework to build rock-solid social networks to handle big volumes."  />
<?php endif; ?>
<?php if(isset($this->meta_keywords) && $this->meta_keywords!=''): ?>
    <meta name="keywords" itemprop="keywords" content="<?php echo $this->meta_keywords ?>">
<?php endif; ?>
<meta itemprop="image" content="<?php echo ASSET_BASE_URL;?>img/emailer/logo.png">
<base href="<?php echo site_url();?>" />
<link id="page_favicon" href="favicon.ico" rel="icon" type="image/x-icon" />
<link href="apple-touch-icon-precomposed.png" rel="apple-touch-icon" />
<?php $this->load->view('include/all_css'); ?>

<?php if(isset($OGHeight)){ ?>
    <meta property="og:image:height" content="<?php echo $OGHeight ?>"/>
<?php } ?>
<?php if(isset($OGWidth)){ ?>
    <meta property="og:image:width" content="<?php echo $OGWidth ?>"/>
<?php } ?>
<?php if(!empty($OGImage)){ ?>
    <meta property="og:image" content="<?php echo $OGImage ?>">
<?php }else{?>
    <meta property="og:image" content="<?php echo ASSET_BASE_URL;?>img/logo-og.png">
<?php } ?>
<?php if(isset($OGDesc)){ ?>
<meta property="og:description" content="<?php echo $OGDesc ?>">
<meta name="twitter:card" content="<?php echo $OGDesc ?>" />
<?php } ?>
<!-- For Twitter Start -->
<meta name="twitter:site" content="@vinfotech" />
<meta name="twitter:creator" content="@vinfotech" />

<!-- For Twitter Ends -->
</head>   
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
if($this->session->userdata('LoginSessionKey')!='')  {
    $this->load->view('include/after-login'); 
//    $this->load->view('include/pushNotifications');
}  else  { 
    if(in_array(trim(strtolower($this->page_name)),array('signup','signin','forgotpassword')))
    {
	   $this->load->view('include/before-login'); 
    }
    else{
       $this->load->view('include/public-login'); 
    } 
}

?>