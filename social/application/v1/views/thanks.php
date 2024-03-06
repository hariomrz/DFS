<!--Container-->
<div class="container wrapper" ng-controller="AccountActivationCtrl">
    <div class="non-login">
        <h4 class="label-title">&nbsp;</h4>
        <div class="panel panel-default">
            <div class="modal-content">
                <div class="panel-body">
                    <div class="blank-regblock">
                        <div class="row">
                            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10">
                                <img src="<?php echo ASSET_BASE_URL ?>img/thankyou.png"  >
                                <h4><?php echo lang('thankyou');?></h4>
                                <p>
                                    <?php echo lang('signup_success');?>
                                </p>
                                <p class="m-t-20">
                                    <?php echo lang('thanks_signup') ?>
                                    <a href="javascript:void(0);">
                                        <?php echo $UserDetail['Email'];?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
    </div>
    <!--Change Email -->
    <!--//Container-->
    <div class="loader-fad" style="display: none;">
        <div class="loader-view spinner48-b" style="display: none;">&nbsp;</div>
    </div>
