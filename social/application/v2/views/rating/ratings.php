<div bindonce data-ng-controller="PageCtrl" id="PageCtrl" ng-init="initialize('<?php echo $auth["LoginSessionKey"]; ?>', '<?php echo $auth["UserGUID"]; ?>')" ng-cloak>
    <div id="RatingCtrl" ng-init="GetPageDetails('<?php echo $PageGUID; ?>')" ng-controller="ratingController as rating">
        <?php $this->load->view('profile/profile_banner'); ?>
        <div class="container wrapper">
            <div class="row">
                <!-- Right Wall-->
                <aside class="col-sm-4 col-sm-push-8 sidebar">
                    <div ng-if="TotalReview > 0" data-type="avrageRating" class="panel-w">
                        <div ng-cloak ng-init="getOverallRating()" class="avrg-rate-view">
                            <h4 ng-bind="avgRateValue"></h4>
                            <ul class="avrg-star-icon">
                                <li ng-class="{'rated':(avgRateValue > 0),'half':(avgRateValue > 0 && avgRateValue < 1)}" class="star-icon">&nbsp;</li>           
                                <li ng-class="{'rated':(avgRateValue > 1),'half':(avgRateValue > 1 && avgRateValue < 2)}" class="star-icon">&nbsp;</li>           
                                <li ng-class="{'rated':(avgRateValue > 2),'half':(avgRateValue > 2 && avgRateValue < 3)}" class="star-icon">&nbsp;</li>           
                                <li ng-class="{'rated':(avgRateValue > 3),'half':(avgRateValue > 3 && avgRateValue < 4)}" class="star-icon">&nbsp;</li>           
                                <li ng-class="{'rated':(avgRateValue > 4),'half':(avgRateValue > 4 && avgRateValue < 5)}" class="star-icon">&nbsp;</li>           
                            </ul>

                            <div class="color-999" ng-if="overallRating.TotalRecords>1" ng-bind="overallRating.TotalRecords + ' reviews'"></div>
                            <div class="color-999" ng-if="overallRating.TotalRecords<=1" ng-bind="overallRating.TotalRecords + ' review'"></div>

                        </div>
                        <ul ng-cloak ng-init="getStarCount()" class="total-rated">
                            <li>
                                <div class="pull-left"><?php echo lang('star5') ?></div>
                                <div class="pull-right" ng-bind="starCount.FiveStarRating"></div>
                                <div class="ratedbar-5"></div> 
                            </li>
                            <li>
                                <div class="pull-left"><?php echo lang('star4') ?></div>
                                <div class="pull-right" ng-bind="starCount.FourStarRating"></div>
                                <div class="ratedbar-4"></div> 
                            </li>
                            <li>
                                <div class="pull-left"><?php echo lang('star3') ?></div>
                                <div class="pull-right" ng-bind="starCount.ThreeStarRating"></div>
                                <div class="ratedbar-3"></div> 
                            </li>
                            <li>
                                <div class="pull-left"><?php echo lang('star2') ?></div>
                                <div class="pull-right" ng-bind="starCount.TwoStarRating"></div>
                                <div class="ratedbar-2"></div> 
                            </li>
                            <li>
                                <div class="pull-left"><?php echo lang('star1') ?></div>
                                <div class="pull-right" ng-bind="starCount.OneStarRating"></div>
                                <div class="ratedbar-1"></div>
                            </li>
                        </ul>
                        <div class="rating-summary">
                            <h3 class="panel-subtitle"><?php echo lang('rating_summary') ?></h3>
                            <ul ng-cloak ng-init="getParameterSummary()">
                                <li ng-repeat="ps in parameterSummary">
                                    <div class="pull-left" ng-bind="ps.ParameterName"></div>
                                    <div class="pull-right">
                                        <span ng-class="{'badgerate-1':(ps.RateValue < 1.6),'badgerate-2':(ps.RateValue > 1.5 && ps.RateValue < 2.6),'badgerate-3':(ps.RateValue > 2.5 && ps.RateValue < 3.6),'badgerate-4':(ps.RateValue > 3.5 && ps.RateValue < 4.6),'badgerate-5':(ps.RateValue > 4.5)}" ng-bind="' ' + ps.RateValue + ' '"></span>
                                    </div> 
                                </li>
                            </ul>
                        </div> 
                    </div>
                    <?php if (!$RatingGUID) { ?>
                        <div ng-if="TotalReview > 0" ng-init="hideSearch();" class="panel-w rate-filterby"  data-type="filterby">
                            <div class="filter-header">
                                <h3 class="panel-subtitle"><?php echo strtoupper(lang('filter_by')); ?></h3>
                                <a ng-if="IsSFilter == '1'" class="pull-right" ng-click="clearFilters();"><?php echo lang('clear_all') ?></a>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for=""><?php echo lang('age_group') ?></label>
                                        <div class="text-field-select">
                                            <select data-placeholder="<?php echo lang('any') ?>" id="AgeGroupFilter" ng-change="getResetRatingList();" ng-model="filter.AgeGroup" ng-options="age.AgeGroupID as age.Name for age in ageGroupList" data-chosen="" data-disable-search="true">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for=""><?php echo lang('gender') ?></label>
                                        <div class="text-field-select">
                                            <select data-placeholder="<?php echo lang('any') ?>" ng-model="filter.Gender" ng-change="getResetRatingList();" data-chosen="" data-disable-search="true">
                                                <option></option>
                                                <option value="0"  selected=""><?php echo lang('any') ?></option>
                                                <option value="1"><?php echo lang('male') ?></option>
                                                <option value="2"><?php echo lang('female') ?></option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for=""><?php echo lang('location') ?></label>
                                        <div class="text-field"><input id="location" ng-init="autoSuggestLocation()" type="text" value="" placeholder="Type location"></div>
                                    </div>  
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for=""><?php echo lang('duration') ?></label>
                                        <div class="text-field-select">
                                            <select data-placeholder="<?php echo lang('any') ?>" data-chosen="" ng-model="filter.Duration" ng-change="changeDuration();" data-disable-search="true">
                                                <option></option>
                                                <option value=""></option>
                                                <option value=""><?php echo lang('any') ?></option>
                                                <option value="This Week"><?php echo lang('this_week') ?></option>
                                                <option value="This Month"><?php echo lang('this_month') ?></option>
                                                <option value="Custom"><?php echo lang('custom') ?></option>
                                            </select>
                                        </div>
                                    </div>  
                                </div>
                                <div ng-if="filter.Duration == 'Custom'" ng-init="initDatePicker();" class="custom-filter">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for=""><?php echo lang('from') ?></label>
                                            <div class="text-field"><input type="text" class="form-control" placeholder="__ /__ /__" id="startDatePicker" /></div>
                                        </div>  
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for=""><?php echo lang('to') ?></label>
                                            <div class="text-field"><input type="text" class="form-control" placeholder="__ /__ /__" id="endDatePicker" /></div>
                                        </div>  
                                    </div>
                                </div>
                                <!-- <div class="col-md-6">
                                   <div class="form-group">
                                    <label for="">Pages</label>
                                    <div class="text-field-select">
                                        <select ng-model="filter.AdminOnly" ng-change="getResetRatingList();" data-chosen="" data-disable-search="true">
                                          <option></option>
                                          <option value="0"  selected="">All</option>
                                          <option value="1">Only Pages</option>
                                        </select>
                                    </div>
                                  </div>
                                </div> -->
                            </div>
                            <div ng-if="IsFilter == '1'" class="section-footer">
                                <div class="footer-inner">
                                    <div class="pull-left"><?php echo lang('filtering_rating') ?></div> <div class="pull-right"><span ng-class="{'badgerate-1':(FilterRateValue < 1.6),'badgerate-2':(FilterRateValue > 1.5 && FilterRateValue < 2.6),'badgerate-3':(FilterRateValue > 2.5 && FilterRateValue < 3.6),'badgerate-4':(FilterRateValue > 3.5 && FilterRateValue < 4.6),'badgerate-5':(FilterRateValue > 4.5)}" ng-bind="' ' + FilterRateValue + ' '"></span></div> 
                                </div>
                            </div>
                        </div>  
                    <?php } ?>
                </aside>
                <!-- //Right Wall-->
                <!-- Left Wall-->
                <aside class="col-sm-8 col-sm-pull-4 reivew-section" ng-init="getRatingList();getEntityListPage();">

                    <div id="avrageRating" class="visible-xs panel-w"></div>
                    <?php if (!$RatingGUID)
                    { ?>
                        <div class="panel panel-info" ng-if="IsFollow==1"  ng-cloak>
                            <div class="panel-heading">
                                <h3 class="panel-title"> 
                                  <span class="text"><?php echo lang('my_review_caps') ?></span>
                                </h3>        
                            </div>

                            <div class="panel-body">
                                <div ng-cloak id="writeReview" class="panel-content" style="display: block;">
                                    <div class="feed-block">
                                        <a class="user-thumbnail"><img  ng-src="{{ImageServerPath + 'upload/profile/220x220/' + LoggedInProfilePicture}}"></a>
                                        <button onclick="$('#writeReview').hide(); $('.eidt-write-review').show();" ng-click="writeReview();" type="button" class="btn btn-primary pull-right"><?php echo lang('write_review_caps') ?></button>
                                        <a class="hidden-xs"><?php echo lang('rate_and_write') ?></a>
                                        <span class="feed-time overflow hidden-xs"><?php echo lang('first_hand_exp') ?></span>
                                    </div>
                                </div>
                                <div style="display: none;" class="eidt-write-review">
                                    <div class="write-your-reivew"> 
                                        <aside class="review-header"> <label><?php echo lang('your_ratings') ?></label><span ng-if="RateValue > 0" ng-class="{'badgerate-1':(RateValue < 1.6),'badgerate-2':(RateValue > 1.5 && RateValue < 2.6),'badgerate-3':(RateValue > 2.5 && RateValue < 3.6),'badgerate-4':(RateValue > 3.5 && RateValue < 4.6),'badgerate-5':(RateValue > 4.5)}" class="pull-right" ng-bind="RateValue"></span> </aside>
                                        <aside class="rated-view">
                                            <ul class="rate-on-list row">
                                                <li ng-repeat="pm in parameter" ng-init="initHoverEffect()" class="col-md-6 col-sm-6">
                                                    <div ng-bind="pm.ParameterName"></div>
                                                <star-rating ng-model="pm.RateValue" max="5" ng-click="rateFunction()"></star-rating>                      
                                                <div ng-if="pm.RateValue > 0" class="rating-count-{{pm.RateValue}}">{{pm.RateValue}}</div>
                                                </li> 
                                            </ul>
                                        </aside> 
                                        <div class="content-row">
                                            <aside class="review-header"> <label><?php echo lang('your_review') ?></label></aside>
                                            <div class="write-review">
                                                <input maxLength="60" ng-model="d.Title" type="text" placeholder="Review title">
                                                <textarea maxLength="500" ng-model="d.Description" placeholder="Tell people about your experience:"></textarea>
                                                <span class="remaining-cart" ng-bind="500 - d.Description.length"></span>
                                            </div>
                                        </div>
                                        <div class="content-row">
                                            <aside class="review-header"> <label><?php echo lang('add_media') ?></label></aside>
                                            <div ng-show="UploadedMediaVal > 0" upload-media-view data-type="uploadedMediaview" class="attached-media">
                                                <ul class="attached-media-list">
                                                    <li ng-repeat="( ratingMediaIndex, ratingMedia ) in Album[0].Media">
                                                        <!--{{ratingMedia}}-->
                                                        <img ng-show="( ( ( ratingMedia.MediaType == 'PHOTO' ) || ( ratingMedia.MediaType == 'Image' ) ) && !ratingMedia.IsLoader)" ng-data-type="{{ratingMedia.MediaType}}" ng-src="{{getImagePath(ratingMedia.MediaType, ratingMedia.ImageName)}}" />
                                                        <img ng-show="( ( ( ratingMedia.MediaType == 'VIDEO' ) || ( ratingMedia.MediaType == 'Video' ) ) && ratingMedia.ConversionStatus && ( ratingMedia.ConversionStatus !== 'Pending' ) && !ratingMedia.IsLoader)" ng-data-type="{{ratingMedia.MediaType}}" ng-src="{{getImagePath(ratingMedia.MediaType, ratingMedia.ImageName)}}" />

                                                        <i ng-show="!ratingMedia.IsLoader" ng-click="removeRatingMedia(ratingMediaIndex)" class="icon-removemedia"></i>
                                                        
                                                        <!--<div ng-show="( ( ( ratingMedia.MediaType == 'PHOTO' ) || ( ratingMedia.MediaType == 'Image' ) ) && ratingMedia.IsLoader )" class="progressbar-block"> 
                                                            <div class="loader" style="font-size: 0.8em; display: block;"></div>
                                                        </div>-->
                                                        
<!--                                                        <div ng-show="ratingMedia.IsLoader" class="progressbar-block"> 
                                                            <div class="loader" style="font-size: 0.8em; display: block;"></div>
                                                        </div>-->
                                                        <div ng-if="ratingMedia.progressPercentage && ratingMedia.progressPercentage < 101" data-percentage="{{ratingMedia.progressPercentage}}" upload-progress-bar-cs></div>

                                                        <div ng-show="( ( ( ratingMedia.MediaType == 'VIDEO' ) || ( ratingMedia.MediaType == 'Video' ) ) && ( !ratingMedia.ConversionStatus || ( ratingMedia.ConversionStatus == 'Pending' ) ) && !ratingMedia.IsLoader )" ng-class="( ( !ratingMedia.ConversionStatus || ( ratingMedia.ConversionStatus == 'Pending' ) ) && !ratingMedia.IsLoader ) ? 'show-processing' : '';" class="progressbar-block">
                                                            <i class="icon-video-c"></i>
                                                            <!--<div ng-show="ratingMedia.IsLoader" class="loader" style="width:24px; height:24px; margin:-12px 0 0 -12px;"></div>-->
                                                        </div>
                                                    </li>
                                                    <li ng-show="( Album[0].Media['media-0'] && ( ( Album[0].Media['media-0'].MediaType == 'PHOTO' ) || ( Album[0].Media['media-0'].MediaType == 'Image' ) ) ) && ( UploadedMediaVal > 0 ) && ( UploadedMediaVal < 10 ) " data-type="imageIcn" class="add-more-photos" ngf-select="uploadRatingMedia($files, $invalidFiles, 'PHOTO')" ngf-accept="'image/*'" multiple ngf-validate-async-fn="validateFileSize($file);">
                                                        <div class="progressbar-block">
                                                            <i class="icon-cam" style="z-index: 0;"></i>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <i ng-show="( ( UploadedMediaVal > 0 ) && !isRatingMediaUploading )" ng-click="removeAllMedia();" class="icon-remove"></i>
                                            </div>

                                            <div ng-show="UploadedMediaVal === 0" class="attached-media media-button">
                                                <ul class="attached-button">
                                                    <li data-type="uploadMediabutton" class="col-md-6 col-sm-6" ngf-select="uploadRatingMedia($files, $invalidFiles, 'PHOTO')" ngf-accept="'image/*'" multiple ngf-validate-async-fn="validateFileSize($file);">
                                                        <i class="icon-cam-d">&nbsp;</i>
                                                        <?php echo lang('add_photos') ?>
                                                    </li>
                                                    <li data-type="orseprator" class="col-md-6 col-sm-6" ngf-select="uploadRatingMedia($file, $invalidFiles, 'VIDEO')" ngf-accept="'video/*'" ngf-validate-async-fn="validateFileSize($file);">
                                                        <div data-type="orsepratorBtn">
                                                            <i class="icon-video-d">&nbsp;</i>
                                                            <?php echo lang('add_video') ?>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div> 
                                        </div> 
                                    </div>
                                    <aside class="section-footer">
                                        <div class="footer-inner">
                                            <div class="col-md-6 col-sm-6 col-xm-6 pull-left cancel-link">
                                                <a ng-click="resetWriteReview();" onclick="$('.eidt-write-review').hide(); $('#writeReview').fadeIn();"><?php echo lang('cancel') ?></a>
                                            </div>  
                                            <div class="col-md-6 col-sm-6 col-xm-6 pull-right">
                                                <div class="pull-right wall-btns">
                                                    <span ng-if="Editing == 0 && entityList.length > 1" class="semi-bold"><?php echo lang('post_as') ?></span>
                                                    <div class="btn-group custom-icondrop m-l-5">
                                                        <button ng-if="Editing == 0 && entityList.length > 1" type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                            <span class="user-img-icon">
                                                                <img ng-src="{{ImageServerPath + 'upload/profile/220x220/' + PostAsModuleProfilePicture}}" ng-att-alt="{{PostAsModuleName}}" class="img-circle">
                                                                <span class="spacel-icon">
                                                                    <i class="caret"></i>
                                                                </span>
                                                                <input type="hidden" value="" ng-model="PostAsModuleID" />
                                                                <input type="hidden" value="" ng-model="PostAsModuleEntityGUID" />
                                                            </span>
                                                        </button>
                                                        <button ng-if="Editing == 0 && entityList.length == 1" type="button" class="btn btn-default dropdown-toggle btn-sm" aria-expanded="false">
                                                            <span class="user-img-icon">
                                                                <img ng-src="{{ImageServerPath + 'upload/profile/220x220/' + PostAsModuleProfilePicture}}" ng-att-alt="{{PostAsModuleName}}" class="img-circle">
                                                                
                                                                <input type="hidden" value="" ng-model="PostAsModuleID" />
                                                                <input type="hidden" value="" ng-model="PostAsModuleEntityGUID" />
                                                            </span>
                                                        </button>

                                                        <div class="postasDropdown mCustomScrollbar dropdown-menu" role="menu">
                                                            <ul ng-if="Editing == 0 && entityList.length > 1" role="menu" class="dropwith-img">
                                                                <li ng-repeat="entity in entityList">
                                                                    <a ng-cloak ng-click="entityProfileChange(entity)" href="javascript:void(0);">
                                                                        <span class="mark-icon"><img ng-src="{{ImageServerPath + 'upload/profile/220x220/' + entity.ProfilePicture}}" alt="User" class="img-circle"></span>
                                                                        {{entity.Name}} </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="dd-with-thumb" title="Post As" data-toggle="tooltip" data-placement="top"> 
                                                            <button ng-if="entity_list.length>1" class="btn btn-default" data-toggle="dropdown" aria-expanded="false" ng-disabled="tagsto.length>0" >
                                                                <span class="dd-thumb">
                                                                    <img ng-if="PostAsModuleID == 18" ng-cloak ng-src="{{ImageServerPath+'upload/profile/220x220/'+ PostAsModuleProfilePicture}}" >
                                                                    <img ng-if="PostAsModuleID == 3"  err-name="{{PostAsModuleName}}" src="{{ImageServerPath+'upload/profile/220x220/'+PostAsModuleProfilePicture}}"  err-SRC="<?php echo site_url() ?>assets/img/profiles/user-thumb.jpg">
                                                                </span>
                                                                <i class="ficon-arrow-down" ng-if="entity_list.length>1"></i>
                                                            </button> 
                                                            <button ng-if="entity_list.length==1" class="btn btn-default" aria-expanded="false" ng-disabled="tagsto.length>0" >
                                                                <span class="dd-thumb">                                        
                                                                    <img ng-if="PostAsModuleID == 18" ng-cloak ng-src="{{ImageServerPath+'upload/profile/220x220/'+ PostAsModuleProfilePicture}}" >
                                                                    <img ng-if="PostAsModuleID == 3"  err-name="{{PostAsModuleName}}" src="{{ImageServerPath+'upload/profile/220x220/'+PostAsModuleProfilePicture}}"  err-SRC="<?php echo site_url() ?>assets/img/profiles/user-thumb.jpg">
                                                                            
                                                                </span>
                                                                <input type="hidden" value="" ng-model="PostAsModuleID" />
                                                                <input type="hidden" value="" ng-model="PostAsModuleEntityGUID" />
                                                            </button> 
                                                            <div class="dropdown-menu dropdown-menu-left mCustomScrollbar scroll-bar scroll-240">
                                                                <ul class="thumb-listing" ng-if="entity_list.length>1">
                                                                    <li ng-repeat="entity in entity_list" data-ng-click="entityProfileChange(entity);">
                                                                        <figure>
                                                                            <img ng-if="entity.ModuleID == 18" ng-cloak ng-src="{{ImageServerPath+'upload/profile/220x220/'+ entity.ProfilePicture}}" >
                                                                            <img ng-if="entity.ModuleID == 3"  err-name="{{entity.Name}}" src="{{ImageServerPath+'upload/profile/220x220/'+entity.ProfilePicture}}"  err-SRC="<?php echo site_url() ?>assets/img/profiles/user-thumb.jpg">
                                                                        </figure>
                                                                        <div class="dd-content ellipsis">
                                                                            <a ng-bind="entity.Name"></a>
                                                                        </div>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button ng-cloak ng-if="Editing == '0'" ng-disabled="isRatingMediaUploading" ng-click="addRating()" type="button" class="btn btn-primary btn-sm m-l-5"><?php echo strtoupper(lang('post')); ?></button>
                                                    <button ng-cloak ng-if="Editing == '1'" ng-disabled="isRatingMediaUploading" ng-click="addRating()" type="button" class="btn btn-primary  btn-sm m-l-5"><?php echo lang('update') ?></button>
                                                </div>
                                            </div> 
                                        </div>
                                    </aside>
                                </div>
                                <?php /*<div ng-cloak ng-if="IsFilter==0 && ratingList.length==0">
                                    <div class="nodata-panel p-v-lg">
                                        <div class="nodata-text">
                                          <span class="nodata-media">
                                            <img src="<?php echo site_url() ?>assets/img/empty-img/empty-page-rating.png" >
                                          </span>
                                          <h5>No reviews yet!</h5>
                                          <p class="text-off">It seems like you haven't reviewed this page yet.<br>Got something to say?</p>
                                          <a ng-click="writeReview()" onclick="$('#writeReview').hide(); $('.eidt-write-review').show();">Write a review! </a>
                                        </div>
                                    </div>
                                </div>*/ ?>
                                <!--{{' - '+ratingList.length+' - '}}
                                {{' - '+IsFilter+' - '}}-->
                            </div> 
                        </div>
                        <!-- no review screen -->
                        <div ng-if="IsFilter==0 && ratingList.length==0" ng-cloak class="panel panel-info">
                            <div class="panel-body nodata-panel">
                                <div class="nodata-text">
                                  <span class="nodata-media">
                                    <img src="<?php echo site_url() ?>assets/img/empty-img/empty-page-rating.png" >
                                  </span>
                                  <h5>{{lang.no_reviews_heading}}</h5>
                                  <p class="text-off">{{lang.no_reviews_message}}</p>
                                  <a onclick="$('#writeReview').hide(); $('.eidt-write-review').show();">Write a review! </a>
                                </div>
                            </div>
                        </div> 
                        <!-- no review screen -->
                    <?php } ?>
                    <!-- <div id="filterBy" class="visible-xs panel-w rate-filterby"></div>  -->

                    <div ng-if="IsFilter == '1'" ng-cloak class="block-header">
                        <span class="semi-bold"><span ng-bind="ratingList.length"></span> <?php echo lang('review_in') ?> :</span>
                        <ul class="tag-list pull-right">
                            <li ng-if="filter.AgeGroup">
                            <div class="tag-item-remove">
                                <span class="tag-item-text"><?php echo lang('age_group') ?> {{AgeGroupName}} <i ng-click="clearSingleFilter('AgeGroup')" class="ficon-cross tag-remove"></i></span>
                            </div>
                            </li>

                            <li ng-if="filter.Location.City">
                            <div class="tag-item-remove">
                                <span class="tag-item-text">{{filter.Location.City + ', ' + filter.Location.Country}} <i ng-click="clearSingleFilter('Location')" class="ficon-cross tag-remove"></i></span>
                            </div>
                            </li>

                            <li ng-if="filter.Duration !== '' && filter.Duration !== 'Custom'">
                            <div class="tag-item-remove">
                                <span class="tag-item-text">{{filter.Duration}} <i ng-click="clearSingleFilter('Duration')" class="ficon-cross tag-remove"></i></span>
                            </div>
                            </li>

                            <li ng-if="filter.Duration !== '' && filter.Duration == 'Custom' && CustomDate !== ''">
                            <div class="tag-item-remove">
                                <span class="tag-item-text">{{CustomDate}} <i ng-click="clearSingleFilter('Duration')" class="ficon-cross tag-remove"></i></span>
                            </div>
                            </li>

                            <li ng-if="filter.Gender == '1' || filter.Gender == '2'">
                            <div class="tag-item-remove">
                                <span class="tag-item-text">{{(filter.Gender==1) ? 'Male' : 'Female' ;}} <i ng-click="clearSingleFilter('Gender')" class="ficon-cross tag-remove"></i></span>
                            </div>
                            </li>

                            <li ng-if="filter.AdminOnly == '1'">
                            <div class="tag-item-remove">
                                <span class="tag-item-text"><?php echo lang('pages_only') ?> <i ng-click="clearSingleFilter('AdminOnly')" class="ficon-cross tag-remove"></i></span>
                            </div>
                            </li>
                        </ul>
                        <!-- <ul class="tag-listing pull-right">
                            <li ng-if="filter.AgeGroup"><?php echo lang('age_group') ?> {{AgeGroupName}} <i ng-click="clearSingleFilter('AgeGroup')" class="ficon-cross">&nbsp;</i></li>
                            <li ng-if="filter.Location.City">{{filter.Location.City + ', ' + filter.Location.Country}} <i ng-click="clearSingleFilter('Location')" class="ficon-cross">&nbsp;</i></li>
                            <li ng-if="filter.Duration !== '' && filter.Duration !== 'Custom'">{{filter.Duration}} <i ng-click="clearSingleFilter('Duration')" class="ficon-cross">&nbsp;</i></li>
                            <li ng-if="filter.Duration !== '' && filter.Duration == 'Custom' && CustomDate !== ''">{{CustomDate}} <i ng-click="clearSingleFilter('Duration')" class="ficon-cross">&nbsp;</i></li>
                            <li ng-if="filter.Gender == '1' || filter.Gender == '2'">{{(filter.Gender==1) ? 'Male' : 'Female' ;}} <i ng-click="clearSingleFilter('Gender')" class="ficon-cross">&nbsp;</i></li>
                            <li ng-if="filter.AdminOnly == '1'"><?php echo lang('pages_only') ?> <i ng-click="clearSingleFilter('AdminOnly')" class="ficon-cross">&nbsp;</i></li>
                        </ul> -->
                    </div>

                    <!-- Rating Starts -->
                    <?php $this->load->view('rating/list') ?>

                    <div ng-cloak ng-if="IsFilter == '1' && ratingList.length == 0 && Busy == 0" class="panel panel-info">         
                      <div class="panel-body nodata-panel">
                        <div class="nodata-text">
                          <span class="nodata-media">
                            <img src="<?php echo site_url() ?>assets/img/empty-img/empty-page-rating.png" >
                          </span>
                          <h5>No results found.</h5>
                          <p class="text-off">We couldn't find any review. Maybe tweak your filters?</p>
                        </div>
                      </div>
                    </div>

                    <div ng-if="ratingList.length == 0 && Busy == 1" class="loader" style="width:50px;height:50px;margin:-25px 0px 0px -25px;"></div>
                </aside>
                <!-- //Left Wall-->

                
            </div>
        </div>
        
        <div ng-include="like_details_modal_tmplt"></div>
        
    </div>
</div>
<input type="hidden" id="RatingGUID" value="<?php echo $RatingGUID ?>" />
<input type="hidden" id="PageNo" value="1" />
