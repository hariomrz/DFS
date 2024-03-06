<div ng-cloak class="panel panel-transparent" ng-init="get_widgets();">
    
    <div class="panel-heading" ng-if="SettingsData.m31==1">            
        
        <h3 class="panel-title text-sm">
            
            <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
                <a target="_self" ng-if="userInterest.length>0" class="link" href="<?php echo site_url('myaccount/interest') ?>">See All</a>

            <?php } else { ?>
                <a target="_self" ng-if="userInterest.length>0" class="link" ng-click="getUserInterest(1000)" data-toggle="modal" data-target="#allInterest" ng-bind="lang.see_all"></a>                
            <?php } ?>
            
            <span class="text" ng-bind="lang.w_interests"></span>
        </h3>
    </div>
    
    <div class="panel-body transparent" ng-if="SettingsData.m31==1">
        
        <div class="panel-body transparent">
            <ul class="list-items-group">
                <li ng-repeat='interest in userInterest' class="items">                    
                    <div class="list-items-xs">                    
                        <div class="list-inner">
                          <figure>
                            <a><img err-src="<?php echo site_url() ?>assets/img/Interest-default.jpg" ng-src="{{ImageServerPath+'upload/category/220x220/'+interest.ImageName}}" ></a>
                          </figure>
                          <div class="list-item-body">
                            <h4 class="list-heading-xs ellipsis">
                                <a target="_self" ng-bind="interest.Name"> </a>
<!--                                <span class="location" ng-bind="interest.Description"></span>-->
                            </h4>                        
                          </div>
                        </div>

                    </div>                                        
                </li>
            </ul>
            
            <ul class="list-items-group" ng-if="userInterest.length==0">
                <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
                <li class="items">{{::lang.w_not_choose_interest}} <a target="_self" href="<?php echo site_url('myaccount/interest') ?>" ng-bind="lang.w_do_it_now"></a> {{::lang.w_show_more_relevant}}</li>
                <?php } else { ?>
                    <li class="items" ng-bind="lang.w_no_interest_added"></li>
                <?php } ?>
            </ul>
            
            
            
        </div>
        
    </div>
</div>