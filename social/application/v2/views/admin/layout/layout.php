<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> 
<html class="no-js" ng-app="App"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $global_settings['header']['title'];?></title>
    <link id="page_favicon" href="favicon.ico" rel="icon" type="image/x-icon" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <base url="<?php echo base_url();?>" target="_self"/>
    
    <!-- Include all css file those want to include in page -->
    <?php $this->load->view('admin/layout/all_css'); ?>

    <?php if( $this->session->userdata('AdminLoginSessionKey') != ''){?>
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $global_settings['header']['favicon']?>" />
    <?php }?>
        
   <?php 
        if(isset($global_settings['date_format']))    
            $js_date =  dateformat_php_to_jqueryui($global_settings['date_format']);

        if(isset($global_settings['page_setting']['pagination']))
            $page_size = $global_settings['page_setting']['pagination']['page_size'];

        if(isset($global_settings['page_setting']['pagination_links']))
            $pagination_links = $global_settings['page_setting']['pagination_links']['links'];
        
        if(isset($global_settings['week_start_on']))
            $week_start_on = $global_settings['week_start_on']; 
        else 
            $week_start_on = 0;
        
        if(isset($global_settings['auto_logout_time']))
            $auto_logout_time = $global_settings['auto_logout_time']; 
        else 
            $auto_logout_time = AUTO_LOGOUT_TIME;
        
        if(isset($global_settings['auto_logout']))
            $auto_logout = $global_settings['auto_logout']; 
        else 
            $auto_logout = AUTO_LOGOUT;
   ?>
  
   <script>
       var base_url = '<?php echo base_url();?>';
       var js_date = '<?php echo  $js_date?>';
       var pagination = <?php echo $page_size ?>;
       var pagination_links = <?php echo $pagination_links ?>;
       var admin_role_id = <?php echo ADMIN_ROLE_ID; ?>;
       var week_start_on = <?php echo $week_start_on; ?>;
       var auto_logout_time = <?php echo $auto_logout_time; ?>;
       var auto_logout = <?php echo $auto_logout; ?>;
       
       var settings_data = <?php echo $this->settings_model->getModuleSettings(); ?>;
       
   </script>
</head>
<body ng-controller="BaseController">

<!--Main Section start from here-->
<section class="page-wrpper" ng-cloak>    
    <!--Header-->
    <?php $this->load->view("admin/layout/header");?>
    <!--/ Header-->
    
    <!-- content-wrapper start from here-->
 
                <!-- Div for show loader-->
                <div id="divLoader" class="hide">
                    <img id="spinner" ng-src="<?php echo ASSET_BASE_URL .'admin/img/loader.gif';?>">
                    <span id="loadertext"><?php echo lang('Loading'); ?></span>
                </div>
                <!-- End Div for show loader-->
                <?php 
                      if (isset($content_view))
    		  {
                        $this->load->view("$content_view");
                      }
                 ?>
                <div class="clearfix"></div>
           

        <div class="pushfooter"></div>
    <!-- content-wrapper end here-->
    
</section>
<!--Main Section end here-->

<!-- language all js variables --->
<?php $this->load->view("admin/layout/language_all_js_variables");?>

<!--Footer-->
    <?php $this->load->view("admin/layout/footer");?>
<!-- Footer end -->

<!-- Javascripts Files -->
    <?php $this->load->view("admin/layout/all_js");?>

<div id="success_message" class="notifications success" style="display: block;">
    <div class="content">
        <span>SUCCESS!</span><span id="spn_noti">  Deleted successfully.</span>
        <div class="icon"></div>
    </div>
</div>
<div id="error_message" class="notifications fail" style="display: block;">
    <div class="content">
        <span class="defaulttext">FAILURE!</span><span id="spn_noti">  Deleted successfully.</span>
        <div class="icon"></div>
    </div>
</div>
<div id="warning_message" class="notifications warning" style="display: block;">
    <div class="content">
        <span class="defaulttext">INFORMATION!</span><span id="spn_noti">  We are working.</span>
        <div class="icon"></div>
    </div>
</div>

</body>
</html>
