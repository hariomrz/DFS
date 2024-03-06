<div class="tab-pane fade" id="Usage">
    <div ng-if="usageData.Desktop.length>0" class="section-content border-bottom clearfix">
        <h2>Desktop</h2>
        <ul class="usage-listing clearfix">
            <li ng-repeat="desktop in usageData.Desktop">
                <i> <img ng-src="../assets/admin/img/{{desktop.Icon}}.png" > </i> <span ng-bind="desktop.Percent+'%'"></span>
            </li>
        </ul>
    </div>
    <div ng-if="usageData.Tablet.length>0" class="section-content border-bottom clearfix">
        <h2>Tablet</h2>
        <ul class="usage-listing clearfix">
            <li ng-repeat="tablet in usageData.Tablet">
                <i><img ng-src="../assets/admin/img/{{tablet.Icon}}.png" ></i> <span ng-bind="tablet.Percent+'%'"></span>
            </li>
        </ul>
    </div>
    <div ng-if="usageData.Mobile.length>0" class="section-content border-bottom clearfix">
        <h2>Mobile</h2>
        <ul class="usage-listing clearfix">
            <li ng-repeat="mobile in usageData.Mobile">
                <i><img ng-src="../assets/admin/img/{{mobile.Icon}}.png" ></i> <span ng-bind="mobile.Percent+'%'"></span>
            </li>
        </ul>
    </div>
</div>