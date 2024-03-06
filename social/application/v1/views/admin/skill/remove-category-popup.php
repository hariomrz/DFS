<div class="popup popup-sm animated removeCategory" id="removeCategoryPopup">
    <div class="popup-title">
        <i class="icon-close" onClick="closePopDiv('removeCategoryPopup', 'bounceOutUp');">&nbsp;</i>

        <a class="endorse-count">
            <span class="num-endorsements" ng-bind="SelectedRemoveSkill.ProfileCount"></span>
        </a>
        <span class="endorse-item-name">
            <span class="endorse-item-icon">
                <img  class="svg" src="../assets/admin/categories/soccer.svg">
            </span>
            <span ng-if="SelectedRemoveSkill.ParentCategorName != ''" ng-bind="SelectedRemoveSkill.ParentCategorName"></span>
            <span ng-if="SelectedRemoveSkill.CategoryName != ''" ng-bind="SelectedRemoveSkill.CategoryName"></span>
            <em ng-if="SelectedRemoveSkill.Name != ''" ng-bind="SelectedRemoveSkill.Name"></em>

        </span>
    </div>
    <div class="popup-content">
        <p>You are about to remove the skill, with its  <b ng-bind="RemoveSkillData.ProfileCount">7</b> profile count . <b ng-bind="RemoveSkillData.EndorsementsCount">35</b> endorsements  </p>
        <p> All users will lose the skills and endorsements associated to their profile. They will receive a notification regarding this change.</p>

        <a class="remove-btn max-w266" ng-click="RemoveSkill();">
            <b>Remove Skill </b>
        </a>    
    </div>
</div>