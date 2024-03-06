<div ng-controller="GroupMemberCtrl"  ng-init="GroupDetail();GroupGUID='<?php echo $GroupGUID;?>'">
<?php $this->load->view('profile/profile_banner') ?> 
<!--Container-->

<div class="container wrapper" id="GroupMemberCtrl" ng-cloak>
  <div class="row"> 
    <!-- Left Wall-->
    <aside class="col-md-8 col-sm-8 col-xs-12" id="SkillsCtrl" ng-controller="SkillsCtrl" ng-cloak>
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
    <aside class="col-sm-4 col-xs-12 sidebar fadeInDown">
      <?php $this->load->view('groups/about_group'); ?>
    </aside>
    <!-- //Right Wall--> 
  </div>
</div>
<!--//Container--> 

 
<!-- <input type="hidden" id="hdngrpid" value="<?php echo $ModuleEntityID ; ?>" /> -->
<input type="hidden" id="memberid" value="" />

<input type="hidden" id="GroupMembersPageNo" value="1" />

<input type="hidden" id="OffsetManagers" value="0">
<input type="hidden" id="OffsetMembers" value="0">
<input type="hidden" id="OffsetAll" value="0">
<input type="hidden" id="LimitManagers" value="200">
<input type="hidden" id="LimitMembers"  value="200">
<input type="hidden" id="LimitAll"  value="200">
<input type="hidden" id="TotalRecordsManagers" value="0">
<input type="hidden" id="TotalRecordsMembers"  value="0">
<input type="hidden" id="TotalRecordsPending"  value="0">
<input type="hidden" id="TotalRecordsAll"  value="0">
<input type="hidden" id="ModuleEntityGUID" value="<?php echo $ModuleEntityGUID; ?>" />