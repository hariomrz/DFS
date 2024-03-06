<div data-ng-controller="PageCtrl" ng-init="initialize('<?php echo $auth["UserGUID"];?>')" ng-cloak>
  <div ng-init="GetPageDetails('<?php echo $PageGUID;?>');GetPageFollower('<?php echo $PageGUID;?>')"> 
    <!--Header-->
    <?php $this->load->view('profile/profile_banner'); ?>
    <!--//Header--> 
    <!--Container-->
    <div class="container wrapper" id="SkillsCtrl" ng-controller="SkillsCtrl" ng-cloak>
      <div class="row"> 
        <!-- Left Wall-->
        <aside class="col-md-8 col-sm-8 col-xs-12">
          
          <div class="panel panel-default">
               <?php
            if ($IsAdmin == 1)
            {
                $this->load->view('skills/pending_skill');
            }
            else
            {
                $this->load->view('skills/endorse_skill',array('ModuleEntityGUID'=>$ModuleEntityGUID));
            }
            ?>
            <?php $this->load->view('skills/add_skill') ?>
          </div>
        </aside>
        <!-- //Left Wall--> 
        
        <!-- Right Wall-->
        <aside class="col-md-4 col-sm-4 col-xs-12 sidebar fadeInDown">
          
          <?php $this->load->view('pages/about_page'); ?>
          <?php $this->load->view('pages/create_page_html'); ?>
        </aside>
        <!-- //Right Wall--> 
      </div>
    </div>
    <!--//Container--> 
  </div>
</div>
<input type="hidden" name="Visibility" id="visible_for" value="1" />
<input type="hidden" name="Commentable" id="comments_settings" value="1" />
<input type="hidden" name="DeviceType" id="DeviceType" value="Native" />
