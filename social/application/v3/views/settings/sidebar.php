<aside class="col-md-4 col-sm-4 col-xs-12 sidebar fadeInDown">
  <div class="panel panel-default">
    
    <div class="panel-body">
        <div class="banner-left">
            <div class="banner-info">
                <img  ng-src="{{CoverImage}}"> 
                <a class="user-name" ng-href="{{ProfileURL}}" ng-bind="FirstName+' '+LastName"></a>
                <div class="u-avatar" ng-cloak>
                    <a ng-href="{{ProfileURL}}">                    

                    <img ng-if="ProfilePicture!==''"   class="img-circle" ng-src="{{ImageServerPath+'upload/profile/220x220/'+ProfilePicture}}">

                    <img ng-if="ProfilePicture==''"   class="img-circle" ng-src="{{SiteURL+'assets/img/profiles/'}}" err-Name="{{FirstName+' '+LastName}}">

                    </a>
                </div>
            </div>
            <div class="basic-info"><!-- Basic Info <i class="icon-arrow-right"></i> --> </div>

        </div>
    </div> 
  </div>
  <div class="panel panel-default">
    <div class="panel-heading p-heading">
      <h3 ><?php echo lang('account_settings') ?></h3>
    </div>
    <div class="panel-body">
         <ul class="privacy-nav">
            <li <?php if($sub=='privacy'){ echo 'class="selected"'; } ?>><a href="<?php echo site_url('myaccount/privacy') ?>"><?php echo lang('privacy') ?></a><i class="icon-arrow-right"></i></li>
            <li <?php if($sub=='notification'){ echo 'class="selected"'; } ?>><a href="<?php echo site_url('notification/settings') ?>"><?php echo lang('notification') ?></a><i class="icon-arrow-right"></i></li>
            <li <?php if($sub=='personalize'){ echo 'class="selected"'; } ?>><a href="<?php echo site_url('myaccount/personalize') ?>">Personalize Newsfeed</a><i class="icon-arrow-right"></i></li>
            <li <?php if($sub=='language'){ echo 'class="selected"'; } ?>><a href="<?php echo site_url('myaccount/language') ?>">Language</a><i class="icon-arrow-right"></i></li>
            <li <?php if($sub=='video'){ echo 'class="selected"'; } ?>><a href="<?php echo site_url('myaccount/video') ?>">Video</a><i class="icon-arrow-right"></i></li>
            <li ng-if="SetPassword==0" <?php if($sub=='resetpassword'){ echo 'class="selected"'; } ?>>
                 <a href="<?php echo site_url('myaccount/resetpassword') ?>">
                     <?php echo lang('set_password');?></a><i class="icon-arrow-right"></i>

            </li>
            <li ng-if="SetPassword==1" <?php if($sub=='resetpassword'){ echo 'class="selected"'; } ?>>
                <a href="<?php echo site_url('myaccount/resetpassword') ?>">Reset Password</a><i class="icon-arrow-right"></i>
            </li>
            <li <?php if($sub=='interest'){ echo 'class="selected"'; } ?> ng-if="SettingsData.m31==1">
                <a href="<?php echo site_url('myaccount/interest') ?>">Area of Interest</a>
                <i class="icon-arrow-right"></i>
            </li>
         </ul>
    </div>
  </div>           

</aside>

<input type="hidden" id="isuserprofile" value="1" />