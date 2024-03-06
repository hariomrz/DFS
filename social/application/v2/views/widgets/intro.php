
<div class="panel panel-transparent" 
     ng-if="!(IsSingleActivity)" 
     ng-cloak=""     
     ng-hide="introduction.gender == '' && introduction.aboutme == '' && introduction.age == '' && introduction.current_companies.length == 0 && introduction.previous_companies.length == 0 && introduction.current_educations.length == 0 && introduction.previous_educations.length == 0 && introduction.Location == '' && !introduction.showRelation && !introduction.FacebookUrl && !introduction.TwitterUrl && !introduction.LinkedinUrl && !introduction.GplusUrl"     
>
    <div class="panel-heading">
        <?php if ($this->session->userdata('UserID') == $UserID) { ?>
            <h3 class="panel-title text-sm">           
                <a target="_self" class="btn btn-default btn-xs pull-right" ng-click="redirectUrl('<?php echo get_entity_url($this->session->userdata("UserID")) ?>/about')">
                    <span class="icon"><i class="ficon-pencil"></i></span>
                </a>          
                <span class="text" ng-bind="lang.w_intro"></span>
            </h3>
        <?php } else { ?>
            <h3 aria-expanded="false" aria-controls="collapseExample" class="panel-title text-sm">
                <span class="text" ng-bind="lang.w_intro"></span>
            </h3>
        <?php } ?>
    </div>
    <div class="panel-body transparent collapse in" id="collapseExample" >

        <p ng-if="introduction.aboutme !== ''"  ng-bind="introduction.aboutme"></p>


        <ul class="user-detail-listing">
            <li ng-if="introduction.gender !== '' || introduction.age > 0">
                <span class="icon"><i class="ficon-user"></i></span>
                <span class="text" ng-if="introduction.gender !== '' && introduction.age > 0" ng-bind="introduction.gender + ', ' + introduction.age"></span>
                <span class="text" ng-if="introduction.gender == '' && introduction.age > 0" ng-bind="introduction.age"></span>
                <span class="text" ng-if="introduction.gender !== '' && introduction.age == 0" ng-bind="introduction.gender"></span>

            </li>
            <!--            <li>
                <span class="icon"><i class="ficon-dob f-lg"></i></span>
                <span class="text">Aug 08, 1987</span>
            </li>-->
            <li ng-if="introduction.Location !== ''">
                <span class="icon"><i class="ficon-location f-lg"></i></span>
                <span class="text" ng-bind="introduction.Location"></span>
            </li>
            <li ng-repeat="cc in introduction.current_companies">
                <span class="icon"><i class="ficon-briefcase"></i></span>
                <span class="text">{{::lang.w_works_at}} <span class="text-black semi-bold" ng-bind="cc.OrganizationName"></span></span>
            </li>

            <li ng-repeat="pc in introduction.previous_companies">
                <span class="icon"><i class="ficon-briefcase"></i></span>
                <span class="text">{{::lang.w_works_at}} <span class="text-black semi-bold" ng-bind="pc.OrganizationName"></span></span>
            </li>
            <li ng-repeat="ce in introduction.current_educations">
                <span class="icon"><i class="ficon-graduation"></i></span>
                <span class="text">{{::lang.w_studied_at}} <span class="text-black semi-bold" ng-bind="ce.University"></span></span>
            </li>

            <li ng-repeat="pe in introduction.previous_educations">
                <span class="icon"><i class="ficon-graduation"></i></span>
                <span class="text">{{::lang.w_went_to}} <span class="text-black semi-bold" ng-bind="pe.University"></span></span>                
            </li>

            <li ng-if="introduction.showRelation">
                <span class="icon"><i class="ficon-relationship f-lg"></i></span>
                <span class="text">
                    <span  ng-bind="introduction.MartialStatusTxt"></span> 
                    <span  ng-if="introduction.RelationWithName !== ''">
                        <span ng-if="introduction.MartialStatusTxt == 'Married'" ng-bind="lang.w_to"></span>
                        <span ng-if="introduction.MartialStatusTxt !== 'Married'" ng-bind="lang.w_with"></span>                            
                    </span> <a target="_self"  ng-if="introduction.RelationWithURL !== ''" ng-bind="introduction.RelationWithName"></a> 
                    <a target="_self" ng-if="introduction.RelationWithURL == ''" ng-bind="introduction.RelationWithName"></a>
                </span>
            </li>
        </ul>
    </div>
    <div class="panel-footer transparent" ng-if="introduction.FacebookUrl || introduction.TwitterUrl || introduction.LinkedinUrl || introduction.GplusUrl">
        <ul class="social-btn">
            <li ng-cloak ng-if="introduction.FacebookUrl !== ''">
                <a target="_self" class="fb" ng-click="redirectTo(introduction.FacebookUrl)">
                    <span class="icon" ng-click="redirectUrl(introduction.FacebookUrl, 0, 1)">
                        <i class="ficon-facebook"></i>
                    </span>
                </a>
            </li>
            <li ng-if="introduction.LinkedinUrl !== ''">
                <a target="_self" class="in" ng-click="redirectTo(introduction.LinkedinUrl)" >
                    <span class="icon" ng-click="redirectUrl(introduction.LinkedinUrl, 0, 1)">
                        <i class="ficon-linkedin"></i>
                    </span>
                </a>
            </li>

            <li ng-if="introduction.TwitterUrl !== ''">
                <a target="_self" class="tw" ng-click="redirectTo(introduction.TwitterUrl)">
                    <span class="icon" ng-click="redirectUrl(introduction.TwitterUrl, 0, 1)">
                        <i class="ficon-twitter"></i>
                    </span>
                </a>
            </li>

            <li ng-if="introduction.GplusUrl !== ''">
                <a target="_self" class="gp"  ng-click="redirectTo(introduction.GplusUrl)">
                    <span class="icon" ng-click="redirectUrl(introduction.GplusUrl, 0, 1)">
                        <i class="ficon-googleplus"></i>
                    </span>
                </a>                            
            </li>
        </ul>
    </div>
</div>