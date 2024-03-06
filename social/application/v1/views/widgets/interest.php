<div ng-cloak class="panel panel-default" ng-if="SettingsData.m31==1" ng-init="getUserInterest(3)">
    <div class="panel-body">
        <div class="panel-heading p-heading">
            <h3 ng-bind="lang.w_interests"></h3>
        </div>
        <div class="panel-body">
            <ul class="list-group thumb-36 m-t-10">
                <li ng-repeat='interest in userInterest'>
                    <figure>
                        <a><img err-src="<?php echo site_url() ?>assets/img/Interest-default.jpg" ng-src="{{ImageServerPath+'upload/category/220x220/'+interest.ImageName}}" ></a>
                    </figure>
                    <div class="description">
                        <a target="_self" class="a-link" ng-bind="interest.Name"></a>
                        <span class="location" ng-bind="interest.Description"></span>
                    </div>
                </li>
            </ul>
            <ul class="list-group" ng-if="userInterest.length==0">
                <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
                <li>{{::lang.w_not_choose_interest}} <a target="_self" href="<?php echo site_url('myaccount/interest') ?>" ng-bind="lang.w_do_it_now"></a> {{::lang.w_show_more_relevant}}</li>
                <?php } else { ?>
                    <li ng-bind="lang.w_no_interest_added"></li>
                <?php } ?>
            </ul>
        </div>
        <?php  if ($this->session->userdata('UserID') == $UserID) { ?>
            <div ng-if="userInterest.length>0" class="footer-link"><a target="_self" href="<?php echo site_url('myaccount/interest') ?>" class="pull-right" ng-bind="lang.see_all"></a></div>
        <?php } else { ?>
            <div ng-if="userInterest.length>0" class="footer-link"><a target="_self" ng-click="getUserInterest(1000)" data-toggle="modal" data-target="#allInterest" class="pull-right" ng-bind="lang.see_all"></a></div>
        <?php } ?>
    </div>
</div>