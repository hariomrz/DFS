<!--Container-->
<div class="container wrapper">
<div class="custom-modal">
<h4 class="label-title">&nbsp;</h4>
<div class="panel panel-default">
  <div class="modal-content">
    <div class="panel-body">
      <div class="blank-regblock">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-10">
                     <img src="<?php echo ASSET_BASE_URL ?>img/thankyou.png"  >
                     <h4><?php echo $heading; ?></h4>
                     <p><?php echo lang('msg'.$msg); ?></p>
                </div>
             </div>
        </div>
    </div>
  </div>
  <!-- /.modal-content --> 
</div>
<?php 
  if($msg == 1) { 
?>
<p class="not-member">Go ahead and complete your <a href="<?php echo base_url();?>">Profile</a></p>
<?php } ?>
<!-- /.modal-dialog --> 
</div>
</div>
<!--//Container-->
