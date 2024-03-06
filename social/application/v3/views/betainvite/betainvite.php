
<aside class="content-wrapper" ng-controller="BetainviteCtrl" id="BetainviteCtrl">
    <h2 class="beta_h2">Beta Invitation</h2>
    <div class="note note-info">
        <h4 class="block">Our site is currently undergoing beta phase, If you have received a beta invitation email then you can access our site by clicking the link from email or by entering invitation code below.</h4>
    </div>
    <div class="info-row row-flued">
        <div class="from-subject">
            <form id="FrmBetaInvitation" method="post" name="FrmBetaInvitation">
                <div class="form-control">
                    <div class="text-field large" data-type="focus">
                        <input id="txtInviteCode" name="Code" type="text" value="" placeholder="Please enter private BETA invitation code">
                    </div>
                    <div class="error-holder errorbox">{{errorMsg}}</div>
                    <div class="clear"></div>
                </div>
                <div class="savebtndiv">
                    <input id="btnSubmit" type="button" class="button" value="Submit" ng-click="validateInviteCode();"/>
                </div>
            </form>
        </div>
    </div>
</aside>
