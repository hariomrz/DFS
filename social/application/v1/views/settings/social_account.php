<aside id="socialInfo" class="content-block-region">
    <div class="title">
        <!-- <div class="editSave">
        <a class="btn btn-default btn-icon show"  title="Edit"
            data-ng-class="{'show': !socialInfoEdit, 'hide': socialInfoEdit}"
            data-ng-init="socialInfoEdit = false"
            data-ng-click="socialInfoEdit= !socialInfoEdit"
            ng-cloak >
            <i class="ficon-pencil"></i>Edit
        </a>
        <div class="save-cancel hide" data-ng-class="{'show': socialInfoEdit, 'hide': !socialInfoEdit}" ng-cloak>
          <a class="cancelEdit btn btn-link gray-clr"  title="Cancel"
              data-ng-init="socialInfoEdit = false"
              data-ng-click="socialInfoEdit= !socialInfoEdit">Cancel</a>
          <input type="submit" class="saveAccount btn btn-default" title="Save" onClick="return checkstatus('socialInfo');" value="Save" />
        </div>
      </div>    -->
        <span class="title-text">Attach Accounts</span>
    </div>
    <div on="socialInfoEdit" data-ng-switch="" class="ng-scope">
        <!-- ngSwitchDefault:  -->
        <div data-ng-switch-default="" class="table-content">
            <aside class="row">
                <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div ng-cloak ng-if="facebookURL!==''" class="form-group social-row">
                        <a href="javascript:void(0);" class="remove-current" ng-click="detachAccount('Facebook API')" data-ng-hide="showthisfb">
                            <i class="icon-smremove"></i></a>
                        <figure>
                            <a href="{{facebookURL}}" target="_blank"> <img title="" alt="" class="img-circle" ng-src="{{facebookProfilePicture}}">
                                <span class="btn social-btn fb"><i class="icon-smfb"></i></span>
                            </a>
                        </figure>
                        <div class="description">
                            <a target="_blank" ng-href="{{facebookURL}}" ng-bind="facebookURL"></a>
                        </div>
                    </div>
                    <div ng-cloak ng-if="facebookURL==''" class="form-group social-row">
                        <figure>
                            <a href="javascript:void(0);" onClick="fb_obj.FbLoginStatusCheck();"> <img title="" alt="" class="img-circle" ng-src="{{facebookProfilePicture}}">
                                <span class="btn social-btn fb"><i class="icon-smfb"></i></span>
                            </a>
                        </figure>
                        <div class="description">
                            <a href="javascript:void(0);" onClick="fb_obj.FbLoginStatusCheck();" class="add-more"><i class="icon-smadd"></i> Add Facebook Account</a>
                        </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div ng-cloak ng-if="twitterURL!==''" class="form-group social-row">
                        <a href="javascript:void(0);" class="remove-current" ng-click="detachAccount('Twitter API')" data-ng-hide="showthistw">
                            <i class="icon-smremove"></i></a>
                        <figure>
                            <a target="_blank" href="{{twitterURL}}"> <img title="" alt="" class="img-circle" ng-src="{{twitterProfilePicture}}">
                                <span class="btn social-btn tw"><i class="icon-smtw"></i></span>
                            </a>
                        </figure>
                        <div class="description">
                            <a target="_blank" ng-href="{{twitterURL}}" ng-bind="twitterURL"></a>
                        </div>
                    </div>
                    <div ng-cloak ng-if="twitterURL==''" class="form-group social-row">
                        <figure>
                            <a href="javascript:void(0);" onClick="$('#twitterloginbtn').trigger('click')"> <img title="" alt="" class="img-circle" ng-src="{{twitterProfilePicture}}">
                                <span class="btn social-btn tw"><i class="icon-smtw"></i></span>
                            </a>
                        </figure>
                        <div class="description">
                            <a href="javascript:void(0);" id="twitterloginbtn" class="add-more"><i class="icon-smadd"></i> Add Twitter Account</a>
                        </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div ng-cloak ng-if="linkedinURL!==''" class="form-group social-row">
                        <a href="javascript:void(0);" class="remove-current" ng-click="detachAccount('LinkedIN API')" data-ng-hide="showthisli">
                            <i class="icon-smremove"></i></a>
                        <figure>
                            <a target="_blank" href="{{linkedinURL}}"> <img title="" alt="" class="img-circle" ng-src="{{linkedinProfilePicture}}">
                                <span class="btn social-btn in"><i class="icon-smin"></i></span>
                            </a>
                        </figure>
                        <div class="description">
                            <a target="_blank" ng-href="{{linkedinURL}}" ng-bind="linkedinURL"></a>
                        </div>
                    </div>
                    <div ng-cloak ng-if="linkedinURL==''" class="form-group social-row">
                        <figure>
                            <a href="javascript:void(0);" onClick="in_obj.InLogin();"> <img title="" alt="" class="img-circle" ng-src="{{linkedinProfilePicture}}">
                                <span class="btn social-btn in"><i class="icon-smin"></i></span>
                            </a>
                        </figure>
                        <div class="description">
                            <a href="javascript:void(0);" onClick="in_obj.InLogin();" class="add-more"><i class="icon-smadd"></i> Add LinkedIn Account</a>
                        </div>
                    </div>
                </aside>
                <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div ng-cloak ng-if="gplusURL!==''" class="form-group social-row">
                        <a href="javascript:void(0);" class="remove-current" ng-click="detachAccount('Google API')" data-ng-hide="showthisgp">
                            <i class="icon-smremove"></i></a>
                        <figure>
                            <a target="_blank" href="{{gplusURL}}"> <img title="" alt="" class="img-circle" ng-src="{{gplusProfilePicture}}">
                                <span class="btn social-btn gp"><i class="icon-smgp"></i></span>
                            </a>
                        </figure>
                        <div class="description">
                            <a target="_blank" ng-href="{{gplusURL}}" ng-bind="gplusURL"></a>
                        </div>
                    </div>
                    <div ng-cloak ng-show="gplusURL==''" class="form-group social-row">
                       
                          <figure id="gplusimage">
                              <a href="javascript:void(0);"> <img title="" alt="" class="img-circle" ng-src="{{gplusProfilePicture}}"> <span class="btn social-btn gp"><i class="icon-smgp"></i></span>
                              </a>
                          </figure>
                        
                        <div class="description" id="gmailsignupbtn">
                            <a href="javascript:void(0);" class="add-more"><i class="icon-smadd"></i> Add Google+ Account</a>
                        </div>
                    </div>
                </aside>
            </aside>
        </div>
        <!-- ngSwitchWhen: true -->
        <!-- <div class="table-content hide" data-ng-switch-when="true" data-ng-class="{'show': socialInfoEdit, 'hide': !socialInfoEdit}" >
        <div class="form clearfix">
          <aside class="row">
            <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            
              <div ng-if="facebookURL!==''" class="form-group social-row">
                  <a href="javascript:void(0);" class="remove-current"  ng-click="detachAccount('Facebook API')" data-ng-hide="showthisfb">
                    <i class="icon-smremove"></i></a>
                  <figure> 
                    <a target="_blank" href="{{facebookURL}}"> <img title="" alt="" class="img-circle" ng-src="{{facebookProfilePicture}}"> 
                      <span class="btn social-btn fb"><i class="icon-smfb"></i></span> 
                    </a> 
                  </figure>
                  <div class="description"> 
                    <a target="_blank" ng-href="{{facebookURL}}" ng-bind="facebookURL"></a> 
                  </div>
              </div>
              <div ng-if="facebookURL==''" class="form-group social-row">
                <figure> 
                  <a href="javascript:void(0);" onClick="fb_obj.FbLoginStatusCheck();"> <img title="" alt="" class="img-circle" ng-src="{{facebookProfilePicture}}"> 
                    <span class="btn social-btn fb"><i class="icon-smfb"></i></span> 
                  </a> 
                </figure>
                <div class="description"> 
                  <a href="javascript:void(0);" onClick="fb_obj.FbLoginStatusCheck();" class="add-more"><i class="icon-smadd"></i> Add Facebook Account</a> 
                </div>
            </div>
            </aside>
            <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
              <div ng-if="twitterURL!==''" class="form-group social-row">
                <a href="javascript:void(0);" class="remove-current" ng-click="detachAccount('Twitter API')" data-ng-hide="showthistw">
                    <i class="icon-smremove"></i></a>
                <figure> 
                  <a target="_blank" href="{{twitterURL}}"> <img title="" alt="" class="img-circle" ng-src="{{twitterProfilePicture}}"> 
                    <span class="btn social-btn tw"><i class="icon-smtw"></i></span> 
                  </a> 
                </figure>
                <div class="description"> 
                  <a target="_blank" ng-href="{{twitterURL}}" ng-bind="twitterURL"></a> 
                </div>
              </div>
              <div ng-if="twitterURL==''" class="form-group social-row">
                <figure> 
                  <a href="javascript:void(0);" onClick="$('#twitterloginbtn').trigger('click')"> <img title="" alt="" class="img-circle" ng-src="{{twitterProfilePicture}}"> 
                    <span class="btn social-btn tw"><i class="icon-smtw"></i></span> 
                  </a> 
                </figure>
                <div class="description"> 
                  <a href="javascript:void(0);" id="twitterloginbtn" class="add-more"><i class="icon-smadd"></i> Add Twitter Account</a> 
                </div>
              </div>
            </aside>
            <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
              <div ng-if="linkedinURL!==''" class="form-group social-row">
                <a href="javascript:void(0);" class="remove-current" ng-click="detachAccount('LinkedIN API')" data-ng-hide="showthisli">
                    <i class="icon-smremove"></i></a>
                <figure> 
                  <a target="_blank" href="{{linkedinURL}}"> <img title="" alt="" class="img-circle" ng-src="{{linkedinProfilePicture}}"> 
                    <span class="btn social-btn in"><i class="icon-smin"></i></span> 
                  </a> 
                </figure>
                <div class="description"> 
                  <a target="_blank" ng-href="{{linkedinURL}}" ng-bind="linkedinURL"></a> 
                </div>
              </div>
              <div ng-if="linkedinURL==''" class="form-group social-row">
                <figure> 
                  <a href="javascript:void(0);" onClick="in_obj.InLogin();"> <img title="" alt="" class="img-circle" ng-src="{{linkedinProfilePicture}}"> 
                    <span class="btn social-btn in"><i class="icon-smin"></i></span> 
                  </a> 
                </figure>
                <div class="description"> 
                  <a href="javascript:void(0);" onClick="in_obj.InLogin();" class="add-more"><i class="icon-smadd"></i> Add LinkedIn Account</a>
                </div>
              </div>
            </aside>
            <aside class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
              <div ng-if="gplusURL!==''" class="form-group social-row">
                <a href="javascript:void(0);" class="remove-current" ng-click="detachAccount('Google API')" data-ng-hide="showthisgp">
                    <i class="icon-smremove"></i></a>
                <figure> 
                  <a target="_blank" href="{{gplusURL}}"> <img title="" alt="" class="img-circle" ng-src="{{gplusProfilePicture}}"> 
                    <span class="btn social-btn gp"><i class="icon-smgp"></i></span> 
                  </a>
                </figure>
                <div class="description"> 
                  <a target="_blank" ng-href="{{gplusURL}}" ng-bind="gplusURL"></a> 
                </div>
              </div>
              <div ng-if="gplusURL==''" class="form-group social-row">
                <figure> 
                  <a href="javascript:void(0);" onClick="$('#gmailsignupbtn').trigger('click');"> <img title="" alt="" class="img-circle" ng-src="{{gplusProfilePicture}}"> <span class="btn social-btn gp"><i class="icon-smgp"></i></span> 
                  </a> 
                </figure>
                <div class="description"> 
                  <a href="javascript:void(0);" id="gmailsignupbtn" class="add-more"><i class="icon-smadd"></i> Add Google+ Account</a> 
                </div>
              </div>              
            </aside>
          </aside>
        </div>
      </div> -->
    </div>
</aside>
