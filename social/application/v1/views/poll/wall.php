<div ng-controller="PollCtrl" id="PollCtrl">
    <?php $this->load->view('poll/wall_filter'); ?>
    <div class="container wrapper">
        <div class="row" ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="is_poll=1;GetwallPostTime();">
            <aside class="col-md-3 col-sm-4 col-md-push-9 col-sm-push-8" >
            <?php $this->load->view('poll/wall_right'); ?>
            </aside>
            <aside class="col-md-9 col-sm-8 col-md-pull-3 col-sm-pull-4" ng-init="add_more=true;">
                <div class="panel wall-content poll-post" id="Wallpostform" ng-cloak>
                    <div class="polls-creation" data-poll="creation">
                        <div class="panel-body">
                            <div class="poll-body">
                                <figure class="thumb-49 thumb-left">
                                    <a class="thumb-left">
                                        <img ng-cloak="" err-name="{{LoginUserName}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+PostAsModuleProfilePicture}}"  />                                    
                                    </a>
                                </figure>    
                                <div class="rightPoll-section">
                                    <div class="create-poll" id="CreatePoll">
                                        <h3 class="panel-title" id="CreatePoll">Create a Poll</h3>
                                        <span class="location">What would you like to ask the world of social network?</span>
                                    </div>
                                    <div class="question-wrap">
                                        <div id="askQuestion" class="askQuestion">
                                            <div class="ask-question border-r" id="wallpostform">
                                                <div class="comments">
                                                    <textarea name="ask question" id="PostContent" data-ng-model="Description" class="form-control createPoll" placeholder="Ask a question" autofocus="true"></textarea>
                                                </div>
                                                <div class="attached-panel ">
                                                    <div class="attached-thumbs scrollbox-horizontal">
                                                        <div class="">
                                                            <div class="upload-media" style="display:none;">
                                                                <ul class="upload-listing" id="listingmedia">
                                                                    <li class="selected selected-capt all-con" style="display:none;">
                                                                        <div data-rel="allshow" class="active media-holder">
                                                                            <a id="m-default" onClick="toggleMediaCaption('default')" class="active" data-rel="allshow">
                                                                      <div class="alltext">ALL
                                                                          <label class="capt-num"></label>
                                                                      </div>
                                                                  </a>
                                                                        </div>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="choice-block">
                                                    <ul class="choice-listing">
                                                        
                                                        
                                                        
                                                        
                                                        <li >
                                                            <div class="input-group choice-group poll-list-group">
                                                                <span class="input-group-addon bullet-icon">
                                                                      <small></small>
                                                                      <small></small>
                                                                      <small></small>
                                                                      <small></small>
                                                                      <small></small>
                                                                      <small></small>
                                                                  </span>
                                                                      <input type="text" maxlength="25" class="form-control" placeholder="Choice 1">
                                                                      <span class="input-group-addon">
                                                                      <div class="attach-on-comment fine-upload-unique" title="Attach photo or video" fine-uploader-poll upload-destination="api/cluster" unique-id="1" image-type="poll" section-type="poll">
                                                                      <span class="icon">
                                                                           <i class="ficon-attachment"></i>
                                                                      </span>
                                                                  </div>
                                                                  </span>
                                                            </div>
                                                            <div class="image-uploaded-view" id="upload-view-poll1">
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </li>
                                                        
                                                        
                                                        
                                                        
                                                        
                                                        
                                                        <li>
                                                            <div class="input-group choice-group poll-list-group">
                                                                <span class="input-group-addon bullet-icon">
                                                                    <small></small>
                                                                    <small></small>
                                                                    <small></small>
                                                                    <small></small>
                                                                    <small></small>
                                                                    <small></small>
                                                                </span>
                                                                <input type="text" maxlength="25" class="form-control" placeholder="Choice 2">
                                                                <span class="input-group-addon">
                                                                <div class="attach-on-comment fine-upload-unique" title="Attach photo or video" fine-uploader-poll upload-destination="api/cluster" unique-id="2" image-type="poll" section-type="poll">
                                                                    <span class="icon">
                                                                        <i class="ficon-attachment"></i>
                                                                    </span>
                                                                </div>
                                                            </span>
                                                            </div>
                                                            <div class="image-uploaded-view" id="upload-view-poll2"></div>
                                                            <div class="clearfix"></div>
                                                      </li>
                                                    </ul>
                                                    <ul class="add-more-link" ng-show="add_more">
                                                        <li>
                                                            <a ng-click="poll_desc_add_more();">+ Add more choices</a>
                                                        </li>
                                                    </ul>
                                                <div class="dummy_poll_desc" style="display:none;">
                                                    <div class="input-group choice-group poll-list-group">
                                                        <span class="input-group-addon bullet-icon">
                                                            <small></small>
                                                            <small></small>
                                                            <small></small>
                                                            <small></small>
                                                            <small></small>
                                                            <small></small>
                                                        </span>
                                                        <input type="text" maxlength="25" class="form-control" placeholder="Choice 2">
                                                        <span class="input-group-addon">
                                                            <div class="attach-on-comment fine-upload-unique" title="Attach photo or video" fine-uploader-poll upload-destination="api/cluster" unique-id="2" image-type="poll" section-type="poll">
                                                                <span class="icon">
                                                                     <i class="ficon-attachment"></i>
                                                                </span>
                                                          </div>
                                                    </span>
                                                </div>
                                                <div class="image-uploaded-view" id="upload-view-poll">
                                                </div>
                                                <div class="upload-view2 remove_description ">
                                                    <a class="removeChoice removeView">
                                                        <i class="ficon-cross"></i>
                                                    </a>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                        <!-- <div class="add-more-link" ng-show="add_more"><a class="btn-link" ng-click="poll_desc_add_more();"> + Add a choice</a></div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="pollExpiry clearfix">
                                <div class="cell">
                                    <span class="text-secondary">Poll expire in</span>
                                    <div class="dropdown inline">
                                        <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" ng-bind="expire_duration_text">Never</a>
                                        <ul class="dropdown-menu expire-dropdown">
                                            <li ng-repeat="ExpireDate in ExpireDateArr" ng-cloak="">
                                                <a data-ng-click="set_expire_date(ExpireDate.duration);">{{ExpireDate.duration}} <span ng-if="ExpireDate.duration==1">Day</span><span ng-if="ExpireDate.duration>1">Days</span><small class="pull-right text-secondary">{{ExpireDate.date}}</small></a>
                                            </li>
                                            <li>
                                                <a data-ng-click="set_expire_date('');">Never</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="cell">
                                    <div class="form-group">
                                        <div class="checkbox check-primary">
                                            <input type="checkbox" value="" id="Anonymous-Votes" data-ng-model="is_anonymous">
                                            <label for="Anonymous-Votes">Anonymous Votes</label>
                                            <i class="icon-n-info pull-right" title="Votes casted on this poll will all
                                                    be anoymous. Your identity will be hidden,only vote will be counted." data-toggle="tooltip" data-placement="top">&nbsp;</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                                                                                                                                              
                                <div class="form-group" >
                                <div class="input-group">
                                  <div class="input-tag">  
                                          <tags-input ng-disabled="PostAsModuleID==18" ng-model="tagsto" add-from-autocomplete-only="true" display-property="name" placeholder="Post for Group/Friends" replace-spaces-with-dashes="false" on-tag-added="tagAddedPoll($tag)" on-tag-removed="tagRemovedPoll($tag)" limit-tags="1">
                                            <auto-complete source="loadGroupAndFriends($query)" min-length="0" load-on-focus="true" load-on-empty="true" max-results-to-show="1000" template="userlistTemplate"></auto-complete>
                                        </tags-input>
                                        <script type="text/ng-template" id="userlistTemplate">
                                            <a href="javascript:void(0);" class="m-conv-list-thmb">
                                                    <img class='angucomplete-image' ng-if='data.ProfilePicture!==""' ng-src="{{ImageServerPath + 'upload/profile/220x220/'+data.ProfilePicture}}" >
                                            <img class='angucomplete-image' ng-if='data.ProfilePicture==""' ng-src="{{AssetBaseUrl+'img/profiles/user_default.jpg'}}" >
                                                </a>
                                            <a href="javascript:void(0);" class="m-u-list-name" ng-bind-html="$highlight($getDisplayText())"></a>
                                            <span><i class="icon-lock" ng-if="data.ModuleID==1" ng-class="{'icon-n-closed':data.Privacy==0,'icon-n-group-secret':data.Privacy==2,'icon-n-global':data.Privacy==1}"></i></span>
                                        </script>                                
                                  </div>
                                                                    
                                </div>
                            </div>        
                                        
                        </div>
                                </div>
                            </div>
                    </div>
                </div>
                    <div class="post-content-block">
        <!-- Upload Media-->
        <div class="uploaded-items" style="display: none;">
            <div class="owl-content">
                <div id="Thumb" class="owl-carousel tabFn tab-with-thumb">
                    <a data-rel="allshow" class="active"> <span><img src="img/thumb-default.jpg"   /></span>
                        <div class="alltext">ALL 4</div>
                    </a>
                    <a data-rel="Team1" class="active"> <span><img src="img/thumb.jpg"   /></span>
                        <mark class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                    </a>
                    <a data-rel="Team2" class="active"> <span><img src="img/thumb.jpg"   /></span>
                        <mark class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                    </a>
                    <a data-rel="upload" class="active"> <span class="upload"> <span class="loaderbtn"> <span class="spinner48"></span>
                        <mark>Uploading</mark>
                        </span>
                        </span>
                    </a>
                    <a data-rel="Team3" class="active"> <span><img src="img/thumb.jpg"   /></span>
                        <mark class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                    </a>
                    <a data-rel="Team4" class="active"> <span><img src="img/thumb.jpg"   /></span>
                        <mark class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                    </a>
                    <a data-rel="Team5" class="active"> <span><img src="img/thumb.jpg"   /></span>
                        <mark class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                    </a>
                    <a data-rel="Team6" class="active"> <span><img src="img/thumb.jpg"   /></span>
                        <mark class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                    </a>
                    <a data-rel="Team7" class="active"> <span><img src="img/thumb.jpg"   /></span>
                        <mark class="btn btn-default removed-thumb"><i class="ficon-cross"></i></mark>
                    </a>
                </div>
            </div>
        </div>
        <!-- //Upload Media-->
        <!-- About Media Comments-->
        <div class="comments about-media" style="display: none;">
            <textarea class="form-control" placeholder="Say something about these pictures"></textarea>
        </div>
        <!-- //About Media Comments-->
        <!-- Wall Actions-->
        <div class="wall-actions clearfix" ng-init="get_entity_list();">
            <div class="row">
                <div class="col-md-6">
                    <ul class="wall-action-left">
                        <li>    
                            <div class="dd-with-thumb" title="Post As" data-toggle="tooltip" data-placement="top"> 
                                <button ng-if="entity_list.length>1" class="btn btn-default" data-toggle="dropdown" aria-expanded="false" ng-disabled="tagsto.length>0" >
                                    <span class="dd-thumb">
                                        <img ng-if="PostAsModuleID == 18" ng-cloak ng-src="{{ImageServerPath+'upload/profile/220x220/'+ PostAsModuleProfilePicture}}" >
                                        <img ng-if="PostAsModuleID == 3"  err-name="{{PostAsModuleName}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+PostAsModuleProfilePicture}}"  >
                                    </span>
                                    <i class="ficon-arrow-down" ng-if="entity_list.length>1"></i>
                                    <input type="hidden" value="" ng-model="PostAsModuleID" />
                                    <input type="hidden" value="" ng-model="PostAsModuleEntityGUID" />
                                </button> 
                                <button ng-if="entity_list.length==1" class="btn btn-default" aria-expanded="false" ng-disabled="tagsto.length>0" >
                                    <span class="dd-thumb">                                        
                                        <img ng-if="PostAsModuleID == 18" ng-cloak ng-src="{{ImageServerPath+'upload/profile/220x220/'+ PostAsModuleProfilePicture}}" >
                                        <img ng-if="PostAsModuleID == 3"  err-name="{{PostAsModuleName}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+PostAsModuleProfilePicture}}"  >
                                                
                                    </span>
                                    <input type="hidden" value="" ng-model="PostAsModuleID" />
                                    <input type="hidden" value="" ng-model="PostAsModuleEntityGUID" />
                                </button> 
                                <div class="dropdown-menu dropdown-menu-left mCustomScrollbar scroll-bar scroll-240">
                                    <ul class="thumb-listing" ng-if="entity_list.length>1">
                                        <li ng-repeat="entity in entity_list" data-ng-click="set_entity_info(entity);">
                                            <figure>
                                                <img ng-if="entity.ModuleID == 18" ng-cloak ng-src="{{ImageServerPath+'upload/profile/220x220/'+ entity.ProfilePicture}}" >
                                                <img ng-if="entity.ModuleID == 3"  err-name="{{entity.Name}}" ng-src="{{ImageServerPath+'upload/profile/220x220/'+entity.ProfilePicture}}"  >
                                            </figure>
                                            <div class="dd-content ellipsis">
                                                <a ng-bind="entity.Name"></a>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>   
                        </li>
                        <li>
                            <label class="checkbox">
                                <input type="checkbox" value="" id="dCommenting">
                                <span class="label">Disable Commenting</span>
                            </label>
                        </li>
                    </ul>                    
                    
                </div>
                <div class="col-md-6">
                    <ul class="post-buttons" >                                                           
                        <li ng-show="is_privacy && SettingsData.m10=='1'">
                          <button class="btn btn-default" data-toggle="dropdown" ng-if="PostAsModuleID!=='18'" id="PollPrivacy">
                            <i class="ficon-globe privacy" ng-if="pollPrivacy == 1"></i>
                            <i class="ficon-friends privacy" ng-if="pollPrivacy != '1'"></i>
                            <i class="ficon-arrow-down"></i>
                          </button>
                          <ul class="dropdown-menu privacy-dd">
                            <li class="dd-text">Who should see this?</li>
                            <li><a onclick="$('#visible_for').val(1);" ng-click="pollPrivacy = 1"><i class="ficon-globe" ></i> Everyone <span>Anyone on {{lang.web_name}}</span></a></li>
                            <li><a onclick="$('#visible_for').val(3);" ng-click="pollPrivacy = 3"><i class="ficon-friends" ></i>Friends <span>Your friends</span></a></li>
                            
                          </ul>
                        </li>
                                                
                        <li>
                          <div class="btn-group">                            
                              <button class="btn btn-primary" id="ShareButton" type="button" ng-click="CreatePoll();">
                                Post          
                                <span class="loader" ng-if="pollLoader"> &nbsp; </span>
                              </button>
                          </div> 
                        </li>
                      </ul>
                    
                </div>
            </div>
        </div>
        <!-- //Wall Actions-->
    </div>
                </div>
                
                
                <?php $this->load->view('poll/wall_content'); ?>
                
            </aside>
            
            
            
            
            <?php $this->load->view('poll/invite_popup');?>
            
        </div>
    </div>
</div>






<input type="hidden" id="fileExtension" value="<?php echo site_url('api/upload_image');?>">

<input type="hidden" id="post_type" name="post_type" value="1" />
<input type="hidden" id="postGuid" name="postGuid" value="" />
<input type="hidden" id="WallPageNo" value="1" />
<input type="hidden" id="UserGUID" value="<?php echo $ModuleEntityGUID; ?>" />
<input type="hidden" id="entity_id" value="<?php echo $this->session->userdata('UserGUID') ?>" />
<input type="hidden" id="UserID" value="<?php if(isset($UserID)){ echo $UserID; } ?>" />
<input type="hidden" id="AllActivity" value="1" />
<input type="hidden" id="UserWall" value="1" />
<input type="hidden" id="module_id" value="3" />
<input type="hidden" id="FeedSortBy" value="2" />
<input type="hidden" id="comments_settings" value="1" />
<input type="hidden" id="visible_for" value="1" />
<input type="hidden" id="ActivityFilter" name="ActivityFilter" value="25" />
<input type="hidden" id="IsPoll" value="1" />
<input type="hidden" id="LikePageNo" value="1" />
