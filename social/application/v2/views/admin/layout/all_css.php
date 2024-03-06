<?php
$assets_path = base_url()."assets/";

//$this->load->library('minify';
// add css files
//$this->minify->css(array('normalize.min.css', 'tipsy.css', 'main.css','fixes.css','jquery-ui.css','font-awesome.min.css'); 

// bool argument for rebuild css (false means skip rebuilding). 
//echo $this->minify->deploy_css(TRUE);
?>

<!-- All css -->
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/normalize.min.css'?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $assets_path.'admin/plugins/jquery.summernote/summernote.css'?>" type="text/css" />
<?php  if($this->page_name=='skill'){ ?> 
    <link rel="stylesheet" href="<?php echo $assets_path.'admin/css/skills.css'?>" type="text/css" />
<?php } ?>

     <link rel="stylesheet" href="<?php echo $assets_path.'admin/plugins/bootstrap/css/bootstrap.css'?>" type="text/css" />   
<?php if($this->page_name=='banner'){ ?> 
	<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/ng-img-crop.css'?>" type="text/css" />
<?php } ?>



 

<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/tipsy.css'?>" type="text/css" /> 
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/fonts-icon.css'?>">
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/jquery-ui.css'?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/font-awesome.min.css'?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $assets_path.'admin/plugins/chosen/css/chosen.css'?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/ng-tags-input.min.css'?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/angucomplete-alt.css'?>" type="text/css" /> 
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/main.css'?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $assets_path.'admin/css/fixes.css'?>" type="text/css" />
 <link rel="stylesheet" href="<?php echo $assets_path.'admin/css/dashboard.css'?>" type="text/css">
 
 <?php if($this->page_name != 'newsletter_users'): ?>
 <link rel="stylesheet" href="<?php echo $assets_path.'admin/css/newsfeed.css'?>" type="text/css">
<?php endif; ?>
 <?php if($this->page_name=='google_analytics_dash'){ ?> 
    <link rel="stylesheet" href="<?php echo $assets_path; ?>admin/css/google_analytic/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $assets_path; ?>admin/css/google_analytic/css/font-awesome.min.css">
    <!-- <link rel="stylesheet" href="assets/css/bootstrap-select.less"> -->
    <link rel="stylesheet" href="<?php echo $assets_path; ?>admin/scss/style.css">
<?php } ?>
 
<!-- Here is include modernizer.js becuase it always on top of page-->
<script src="<?php echo $assets_path.'admin/js/vendor/modernizr-2.6.2.min.js'?>"></script>


<!------------abminLTE ---------->
<?php if($this->page_name == 'google_analytics_dash') {  ?>
	<!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>dist/css/adminlte.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/iCheck/flat/blue.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/morris/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/jvectormap/jquery-jvectormap-1.2.2.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/datepicker/datepicker3.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/daterangepicker/daterangepicker-bs3.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="<?php echo $assets_path.'admin/AdminLTE/' ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

<?php } ?> 	
<!------------abminLTE ---------->
