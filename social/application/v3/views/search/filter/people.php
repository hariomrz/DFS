<div class="secondary-fixed-nav">
    <div class="secondary-nav">
        <div class="container">
            <div class="row nav-row">
                <div class="col-sm-12 main-filter-nav"> 
                    <nav class="navbar navbar-default navbar-static">
                        <div class="navbar-header visible-xs">
                             <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#filterNav"> 
                                    <span class="icon"><i class="ficon-filter"></i></span>
                                </button>
                        </div>
                        <div class="collapse navbar-collapse" id="filterNav">
                            <ul class="nav navbar-nav filter-nav" ng-init="getFilterDetails();">
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Location 
                                        <span>
                                            <span ng-if="city_list_checked.length==0">Any City</span>
                                            <span ng-if="city_list_checked.length>0" ng-bind="city_list_checked[0].Name"></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li class="mCustomScrollbar filter-height no-padding">
                                            <ul class="p-l-sm">
                                                <li>
                                                    <label class="checkbox">
                                                        <input type="checkbox" ng-checked="city_list_checked.length==0" ng-click="emptyArr('city_list_checked','city_list'); callUserList();" value="0">
                                                        <span class="label">Any City</span>
                                                    </label>
                                                </li>
                                                <li ng-repeat="city in city_list_checked" ng-cloak>
                                                    <label class="checkbox">
                                                        <input ng-click="remove_from_city(city.CityID); callUserList();" type="checkbox" checked="checked" class="search-city" ng-value="city.CityID" value="0">
                                                        <span class="label" ng-bind="city.Name"></span>
                                                    </label>  
                                                </li>
                                                <li ng-repeat="city in city_list" ng-cloak>
                                                    <label class="checkbox">
                                                        <input ng-click="add_to_city(city); callUserList();" type="checkbox" class="search-city" ng-value="city.CityID" value="0">
                                                        <span class="label" ng-bind="city.Name"></span>
                                                    </label>  
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <div class="input-search form-control right">
                                                <input ng-keyup="get_cities(city)" ng-model="city" type="text" name="srch-filters" placeholder="Look for more city" class="form-control">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown" ng-if="SettingsData.m31==1" ng-cloak>
                                    <a class="" data-toggle="dropdown" role="button">Interest 
                                        <span>
                                            <span ng-cloak ng-if="interest_list_checked.length==0">All Categories</span>
                                            <span ng-cloak ng-if="interest_list_checked.length>0" ng-bind="interest_list_checked[0].Name"></span>
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li class="mCustomScrollbar filter-height no-padding">
                                            <ul class="p-l-sm">
                                                <li>
                                                    <label class="checkbox">
                                                        <input type="checkbox" ng-checked="interest_list_checked.length==0" ng-click="emptyArr('interest_list_checked','interest_list'); callUserList(500);" value="0">
                                                        <span class="label">Any Interest</span>
                                                    </label>
                                                </li>
                                                <li ng-repeat="interest in interest_list_checked">
                                                    <label class="checkbox">
                                                        <input ng-click="remove_from_interest(interest,interest.CategoryID); callUserList();" ng-checked="interest.IsChecked" class="interest-check" type="checkbox" ng-value="interest.CategoryID" >
                                                        <span class="label" ng-bind="interest.Name"></span>
                                                    </label>
                                                    <ul class="sub-categories">
                                                        <li ng-repeat="subinterest in interest.Subcategory">
                                                            <label class="checkbox">
                                                                <input ng-checked="subinterest.IsChecked" ng-click="callUserList();" type="checkbox" ng-value="subinterest.CategoryID" class="interest-check">
                                                                <span class="label" ng-bind="subinterest.Name"></span>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li ng-repeat="interest in interest_list">
                                                    <label class="checkbox">
                                                        <input ng-click="add_to_interest(interest,interest.CategoryID); callUserList();" class="interest-check" type="checkbox" ng-value="interest.CategoryID" >
                                                        <span class="label" ng-bind="interest.Name"></span>
                                                    </label>
                                                    <ul class="sub-categories">
                                                        <li ng-repeat="subinterest in interest.Subcategory">
                                                            <label class="checkbox">
                                                                <input type="checkbox" ng-click="add_to_interest(interest,subinterest.CategoryID); callUserList();" ng-value="subinterest.CategoryID" class="interest-check">
                                                                <span class="label" ng-bind="subinterest.Name"></span>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <div class="input-search form-control right">
                                                <input type="text" ng-keyup="get_interest(interest)" ng-model="interest" name="srch-filters" placeholder="Look for more Interests" class="form-control">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a class="" data-toggle="dropdown" role="button">Skills 
                                        <span>
                                            <span ng-cloak ng-if="skills_list_checked.length==0">All Skills</span>
                                            <span ng-cloak ng-if="skills_list_checked.length>0" ng-bind="skills_list_checked[0].Name"></span>
                                            &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">                                         
                                        <li class="mCustomScrollbar filter-height no-padding">
                                        <ul class="p-l-sm">
                                            <li>
                                                <label class="checkbox">
                                                    <input type="checkbox" ng-checked="skills_list_checked.length==0" ng-click="emptyArr('skills_list_checked','skills_list'); callUserList();" value="0">
                                                    <span class="label">All Skill</span>
                                                </label>  
                                            </li>
                                            <li ng-repeat="skills in skills_list_checked">
                                                <label class="checkbox">
                                                    <input ng-click="remove_from_skills(skills.SkillID); callUserList();" type="checkbox" checked="checked" class="search-city" ng-value="skills.SkillID" value="0">
                                                    <span class="label" ng-bind="skills.Name"></span>
                                                </label> 
                                            </li>
                                            <li ng-repeat="skills in skills_list" ng-cloak>
                                                <label class="checkbox">
                                                    <input ng-click="add_to_skills(skills); callUserList();" type="checkbox" class="search-city" ng-value="skills.SkillID" value="0">
                                                    <span class="label" ng-bind="skills.Name"></span>
                                                </label>  
                                            </li> 
                                        </ul>
                                        </li>


                                        <li>
                                            <div class="input-search form-control right">
                                                <input ng-keyup="get_skills(skills)" ng-model="skills" type="text" name="srch-filters" placeholder="Look for more Skill" class="form-control">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> School/college 
                                        <span>
                                            <span ng-cloak ng-if="school_list_checked.length==0">Any School</span>
                                            <span ng-cloak ng-if="school_list_checked.length>0" ng-bind="school_list_checked[0].University"></span>
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li class="mCustomScrollbar filter-height no-padding">
                                            <ul class="p-l-sm">
                                        <li>
                                            <label class="checkbox">
                                                <input type="checkbox" ng-checked="school_list_checked.length==0" ng-click="emptyArr('school_list_checked','school_list'); callUserList();" value="0">
                                                <span class="label">Any School</span>
                                            </label>  
                                        </li>
                                        <li ng-repeat="school in school_list_checked" ng-cloak>
                                            <label class="checkbox">
                                                <input ng-click="remove_from_school(school.EducationID); callUserList();" type="checkbox" checked="checked" class="search-city" ng-value="city.EducationID" value="0">
                                                <span class="label" ng-bind="school.University"></span>
                                            </label>  
                                        </li>
                                        <li ng-repeat="school in school_list" ng-cloak>
                                            <label class="checkbox">
                                                <input ng-click="add_to_school(school); callUserList();" type="checkbox" class="search-city" ng-value="school.EducationID" value="0">
                                                <span class="label" ng-bind="school.University"></span>
                                            </label>  
                                        </li>
                                        </ul></li>
                                        <li>
                                            <div class="input-search form-control right">
                                                <input ng-keyup="get_schools(school)" ng-model="school" type="text" name="srch-filters" placeholder="Look for more school/college" class="form-control">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Workplace 
                                        <span>
                                            <span ng-cloak ng-if="company_list_checked.length==0">Any Company</span>
                                            <span ng-cloak ng-if="company_list_checked.length>0" ng-bind="company_list_checked[0].OrganizationName"></span>
                                        &nbsp;
                                        </span>
                                    </a>
                                    <ul data-type="stopPropagation" class="dropdown-menu dropdown-menu-left filters-dropdown">
                                        <li class="mCustomScrollbar filter-height no-padding">
                                            <ul class="p-l-sm">
                                        <li>
                                            <label class="checkbox">
                                                <input type="checkbox" ng-checked="company_list_checked.length==0" ng-click="emptyArr('company_list_checked','company_list'); callUserList();" value="0">
                                                <span class="label">Any Company</span>
                                            </label>  
                                        </li>
                                        <li ng-repeat="company in company_list_checked" ng-cloak>
                                            <label class="checkbox">
                                                <input ng-click="remove_from_company(company.WorkExperienceID); callUserList();" type="checkbox" checked="checked" class="search-city" ng-value="company.WorkExperienceID" value="0">
                                                <span class="label" ng-bind="company.OrganizationName"></span>
                                            </label>  
                                        </li>
                                        <li ng-repeat="company in company_list" ng-cloak>
                                            <label class="checkbox">
                                                <input ng-click="add_to_company(company); callUserList();" type="checkbox" class="search-city" ng-value="company.WorkExperienceID" value="0">
                                                <span class="label" ng-bind="company.OrganizationName"></span>
                                            </label>  
                                        </li>
                                        </ul>
                                        </li>
                                        <li>
                                            <div class="input-search form-control right">
                                                <input ng-keyup="get_companies(company)" ng-model="company" type="text" name="srch-filters" placeholder="Look for more school/college" class="form-control">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn">
                                                        <span class="icon">
                                                            <i class="ficon-search f-lg"></i>
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li class="dropdown"> 
                                    <a class="" data-toggle="dropdown" role="button"> Sort By 
                                        <span>
                                            <span ng-cloak ng-if="sort_by_label==''">Network</span>
                                            <span ng-cloak ng-if="sort_by_label!==''" ng-bind="sort_by_label"></span>
                                        &nbsp;
                                        </span> 
                                    </a>
                                    <ul data-type="stopPropagation" class="active-with-icon dropdown-menu dropdown-menu-left">
                                        <li ng-if="sort_by_label2!=='NameAsc'" ng-class="(sort_by_label2 == 'NameDesc') ? 'active' : ''"><a ng-click="changeSortBy('NameAsc');">By Name</a></li>
                                        <li ng-if="sort_by_label2=='NameAsc'" ng-class="(sort_by_label2 == 'NameAsc') ? 'active' : ''"><a ng-click="changeSortBy('NameDesc');">By Name</a></li>
                                        <li ng-class="(sort_by_label2 == 'Followers') ? 'active' : ''"><a ng-click="changeSortBy('Followers')">By No. of Followers</a></li>
                                        <li ng-class="(sort_by_label2 == '' || sort_by_label2 == 'Network') ? 'active' : ''"><a ng-click="changeSortBy('Network')">By Network</a></li>
                                        <li ng-class="(sort_by_label2 == 'ActivityLevel') ? 'active' : ''"><a ng-click="changeSortBy('ActivityLevel')">By Activity Level</a></li>
                                        <!-- <li><a>By Ratings</a></li> -->
                                    </ul>
                                </li>
                                
                                
                                <li  ng-cloak="" ng-if="!isDefaultFilterPeopleSearch()">
                                    <div class="reset-button" >
                                        <button class="btn btn-default" ng-click="ResetFilterPeopleSearch()">Reset</button>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                    </nav> 

                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="sortby" value="" />
<input type="hidden" id="CurrentPage" value="User" />