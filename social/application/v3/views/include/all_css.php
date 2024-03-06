<!-- BEGIN CORE CSS FRAMEWORK --> 
<?php 
    //ENVIRONMENT!='development'
    if(ENVIRONMENT=='development') {
?>
        <link href="<?php echo ASSET_BASE_URL ?>css/plugins.css<?php version_control(); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ASSET_BASE_URL ?>css/main.min.css<?php version_control(); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo ASSET_BASE_URL ?>css/custom.css<?php version_control(); ?>" rel="stylesheet" type="text/css"/> 
        <link href="<?php echo ASSET_BASE_URL ?>css/emoji.css" rel="stylesheet" />
<?php
    } else {
?>
        <link href="<?php echo ASSET_BASE_URL ?>css/main.min.css<?php version_control(); ?>" rel="stylesheet" type="text/css"/>
 <?php        
    }
?>       
<!-- END CSS TEMPLATE -->
<?php
/*
All css .
used to include css according to their usability on page
*/
switch(strtolower(trim($this->page_name))) {        
    case 'events' :
?>
    
<?php
    break;
        case 'betainvite' :
            if(ENVIRONMENT=='development') {
?>
                <link href="<?php echo ASSET_BASE_URL ?>css/betainvite.css<?php version_control(); ?>" rel="stylesheet" type="text/css" media="screen"/>
<?php
            } else {
?>
                <link href="<?php echo ASSET_BASE_URL ?>css/betainvite.min.css<?php version_control(); ?>" rel="stylesheet" type="text/css" media="screen"/>
<?php                
            }
    break;    
}
?>
