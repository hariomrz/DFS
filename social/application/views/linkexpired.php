<div class="container">
  <div class="row login-container animated fadeInUp">
    <div class="col-md-4 col-md-offset-4 no-padding  tiles white boxshadow">
      <div class="tiles grey p-l-25 p-r-25 p-t-5 p-b-5">
        <h1><?php echo lang('link_expired');?></h1>
      </div>
      <div class="content tiles white col-lg-12">
        <div class="row">
            <div class="col-md-12 p-t-15 p-b-15">
            <?php echo lang('request_new_link') ?>
           </div>  
        </div>
        <div class="row">
          <div class="col-md-12 p-b-15">
            <a href="<?php echo base_url("signin"); ?>" class="btn btn-orange btn-small"><?php echo lang('login_button_text');?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
