<section class="site-header">
    <div class="hero animation">
        <div class="hero-background">
            <div class="hero-overlay"></div>
            <div class="home-banner" style="background-image:url(assets/img/home-banner.jpg);"></div>
        </div>
    </div>
    <div class="container-fluid site-container">
        <div class="table-view-home">
            <div class="table-cell-home v-middle">
                <div class="site-banner">
                    <h2 class="site-title" ng-bind="::lang.j_h_site_title"></h2>
                    <h1 class="site-subtitle" ng-bind="::lang.j_h_site_subtitile"></h1>
                    <a class="btn btn-default-outline white btn-lg btn-md-size" ng-href="{{BaseUrl + 'signup'}}" ng-bind="::lang.j_h_join_cmm"></a>    
                </div>
            </div>
        </div>
    </div>
</section>
<!-- //Header -->

<div ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl">

    <div class="wrapper" ng-controller="homeCtrl">
        
        
        <div class="container">
            <!-- Event Slider -->
            <div class="panel panel-slick" ng-cloak ng-init="loadEvents()" ng-if="hasEvents">
                <div class="panel-heading">
                    <h2 class="panel-title" ng-bind="::lang.j_h_evt_pnl_ttl"></h2>
                    <p ng-bind="::lang.j_h_evt_pnl_desc"></p>
                </div>
                <div class="panel-body" ng-if="listData.length > 0">
                    <ul class="list-slick eventSlider">

                        <slick class="slider" settings="eventSliderConfig">
                            <li class="slick-item" ng-repeat="Event in  listData" ng-cloak>
                                <div class="items">


                                    <div class="cards cards-event">
                                        <div class="cards-image">

                                            <img ng-src="{{ImageServerPath +  'upload/profilebanner/220x220/event_banner.jpg'}}"  class="img-effect" ng-if="Event.IsCoverExists == '0'">
                                            <img ng-src="{{Event.ProfileBanner}}"  class="img-effect" ng-if="Event.IsCoverExists == '1'">


                                            <div class="cards-labels" ng-show="Event.EventDay != ''">
                                                <span class="date-label date-label-md reminder-set" ng-bind="Event.EventDay"></span>
                                            </div>
                                            <div class="cards-image-desc">
                                                <div class="date-block">
                                                    <h5 ng-bind="Event.EventStartDate"></h5>
                                                    <span ng-bind="Event.EventStartMothnYear"></span>
                                                </div>

                                                <span class="invite-status" ng-if="Event.loggedUserPresence == 'ATTENDING' || Event.loggedUserPresence == 'INVITED'" ng-bind="Event.loggedUserPresence"></span>
                                            </div>
                                        </div>
                                        <div class="cards-content">
                                            <a entitytype="event" entityguid="{{Event.EventGUID}}" class="name a-link loadbusinesscard" ng-href="{{BaseUrl +Event.EventUrl}}">
                                                <h2 class="cards-title" ng-bind="Event.Title"></h2>
                                            </a>
                                            <div class="event-venue" ng-bind="Event.Venue"></div>
                                            <div class="event-location" ng-bind="Event.Location.Location"></div>

                                            <ul class="thumbnail-list">
                                                <li ng-repeat="user in Event.EventUsers| limitTo:DisplayUserCount" ng-show="Event.EventUsers.length > 0">
                                                    <a entitytype="user" entityguid="{{user.UserGUID}}" class="loadbusinesscard" ng-href="{{BaseUrl + user.ProfileLink}}"> 
                                                        <img ng-if="user.ProfilePicture != ''" ng-src="{{ImageServerPath +  'upload/profile/220x220/'  + user.ProfilePicture}}" err-SRC="{{BaseUrl + 'assets/img/profiles/user_default.jpg'}}" />

                                                        <span ng-if="user.ProfilePicture == ''" class="default-thumb">
                                                            <span ng-bind="getDefaultImgPlaceholder(user.FullName)"></span>
                                                        </span>
                                                    </a>  
                                                </li>
                                                <li class="more" ng-show="Event.MemberCount > DisplayUserCount" ng-show="Event.EventUsers.length > 0">
                                                    <a href="#">+{{Event.MemberCount - Event.EventUsers.length}}</a>
                                                </li>
                                                <li ng-show="Event.EventUsers.length == 0"></li>
                                            </ul>
                                            <ul class="event-tags">
                                                <li ng-bind="'#' + Event.CategoryName"></li>
                                            </ul>
                                            <div class="cards-action-toolbar">
                                                <a class="btn btn-primary btn-block btn-lg" ng-href="{{BaseUrl + Event.EventUrl}}">
                                                    <span class="sml" ng-bind="::lang.view_detail"></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>                        
                        </slick>


                    </ul>
                </div>
                <div class="panel-footer">
                    <a class="btn btn-default-outline btn-lg btn-md-size" ng-href="{{BaseUrl + 'events'}}" ng-bind="::lang.j_h_evt_pnl_link"></a>
                </div>
            </div>
            
            
            
            <!-- Trip Journal Slider -->
            <div class="panel panel-slick" ng-cloak ng-init="GetJournalList()" ng-if="hasJournals">
                <div class="panel-heading">
                    <h2 class="panel-title" ng-bind="::lang.j_h_jrnl_pnl_ttl"></h2>
                    <p ng-bind="::lang.j_h_jrnl_pnl_desc"></p>
                </div>
                <div class="panel-body" ng-if="journals.length > 0">
                    <ul class="list-slick journalSlider">
                        <slick class="slider" settings="journalSliderConfig">
                            <li class="slick-item" ng-repeat="(journalKey, Journal) in journals" ng-cloak>
                                <div class="items">
                                    <div class="thumbnail thumbnail-default">
                                        <figure class="img-panel">
                                            <a class="block" ng-href="{{(BaseUrl + 'journal/wall/' + Journal.TitleURL + '/' + Journal.JournalGUID)}}">
                                                <img ng-src="{{ImageServerPath + 'upload/profilebanner/220x220/journal_blank.png'}}"  class="img-full" >                                            
                                                <img 
                                                    ng-src="{{ImageServerPath + 'upload/profilebanner/220x220/' + Journal.ProfileBanner}}"  
                                                    err-SRC="{{ImageServerPath + 'upload/profilebanner/220x220/journal_blank.jpg'}}"
                                                     class="main-img"
                                                    ng-if="Journal.ProfileBanner"
                                                    />

                                                <img 
                                                    ng-src="{{journalBlankImage()}}"                              
                                                     class="main-img"
                                                    ng-if="!Journal.ProfileBanner"
                                                    />                                            
                                            </a>
                                        </figure>
                                        <div class="caption">
                                            <div class="content">
                                                <span class="time uppercase" ng-if="::getTimePlaceTxt(Journal)" ng-bind="::getTimePlaceTxt(Journal)"></span>
                                                <h4 class="title">
                                                    <a ng-href="{{(BaseUrl + 'journal/wall/' + Journal.TitleURL + '/' + Journal.JournalGUID)}}" ng-bind="::Journal.Title"></a>
                                                </h4>

                                                <span class="permission" ng-if="Journal.StatusID != 10">
                                                    <span class="text">
                                                        <span class="regular" ng-bind="::getLangStr('By', 1)"></span>
                                                        <a class="name" ng-bind="::(Journal.FirstName + ' ' + Journal.LastName)"></a>
                                                    </span>                                                
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </slick>
                    </ul>
                </div>
                <div class="panel-footer">
                    <a class="btn btn-default-outline btn-lg btn-md-size" ng-href="{{BaseUrl + 'journal'}}" ng-bind="::lang.j_h_jrnl_pnl_link"></a>
                </div>
            </div>
        </div>



        <div class="box-wrap" ng-cloak>
            <div class="box-shape ">
                <div class="panel panel-trip">
                    <div class="panel-heading">
                        <h2 class="panel-title" ng-bind="::lang.j_h_trp_pnl_ttl"></h2>
                        <p ng-bind="::lang.j_h_trp_pnl_desc"></p>
                    </div>
                    <div class="panel-body"  >
                        <div class="container work-step">
                            <div class="row">
                                <div class="col-md-4 work-step-col">
                                    <figure class="work-step-img">
                                        <span>
                                            <img src="assets/img/Identify-holiday-ideas.png" >
                                        </span>
                                    </figure>
                                    <div class="title" ng-bind="::lang.j_h_trp_pnl_cnt_ttl1"></div>
                                    <p ng-bind="::lang.j_h_trp_pnl_cnt_desc1"></p>
                                </div>
                                <div class="col-md-4 work-step-col">
                                    <figure class="work-step-img">
                                        <span>
                                            <img src="assets/img/shortlist-interesting-places.png" >
                                        </span>
                                    </figure>
                                    <div class="title" ng-bind="::lang.j_h_trp_pnl_cnt_ttl2"></div>
                                    <p ng-bind="::lang.j_h_trp_pnl_cnt_desc2"></p>
                                </div>
                                <div class="col-md-4 work-step-col">
                                    <figure class="work-step-img">
                                        <span>
                                            <img src="assets/img/build-awesome-itineraries.png" >
                                        </span>
                                    </figure>
                                    <div class="title" ng-bind="::lang.j_h_trp_pnl_cnt_ttl3"></div>
                                    <p ng-bind="::lang.j_h_trp_pnl_cnt_desc3"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" ng-if="hasPosts">
            <!-- Discusions Slider -->
            <div class="panel panel-slick" ng-cloak ng-init="getPublicPosts()">
                <div class="panel-heading">
                    <h2 class="panel-title" ng-bind="::lang.j_h_actvt_pnl_ttl"></h2>
                    <p ng-bind="::lang.j_h_actvt_pnl_desc"></p>
                </div>
                <div class="panel-body" ng-if="posts.length > 0">
                    <ul class="list-slick discussionSlider list-feed-list">
                        <slick class="slider" settings="postSliderConfig">
                            <li class="slick-item" ng-repeat="(key, data) in posts" ng-cloak>
                                <div class="items">
                                    <div class="feed-list">
                                        <div class="feed-body">


                                            <div class="list-items-xmd">
                                                <div class="list-inner">
                                                    <figure>                                                                                               
                                                        <a class="loadbusinesscard" entitytype="user" entityguid="{{data.UserGUID}}" ng-if="data.PostAsModuleID == '3' && data.ActivityType !== 'ProfilePicUpdated' && data.ActivityType !== 'ProfileCoverUpdated'" ng-href="{{data.SiteURL + data.UserProfileURL}}" target="_self">
                                                            <img   class="half-circle" ng-src="{{data.ImageServerPath + 'upload/profile/220x220/' + data.UserProfilePicture}}" err-name="{{data.UserName}}">
                                                        </a>                                                                                                        
                                                    </figure>
                                                    <div class="list-item-body">
                                                        <h4 class="list-heading-md ellipsis">
                                                            <a ng-bind-html="getTitleMessage(data)"></a>
                                                        </h4>
                                                        <div>
                                                            <small ng-bind="date_format(data.CreatedDate)">11 Dec at 9:03 AM</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="feed-scroll mCustomScrollBox">    
                                                <div class="feed-well">
                                                    <div class="feed-well-heading">
                                                        <h4 class="title" ng-if="data.PostTitle">
                                                            <a ng-bind-html="getPostTitle(data)" ng-href="{{data.ActivityURL}}"></a>
                                                        </h4>
                                                                                                            
                                                        <ul class="list-activites list-icons-disc text-base" ng-if="data.ActivityType=='JournalPost'">
                                                                <li ng-cloak ng-if="data.Params.Location.city" ng-bind="data.Params.Location.city"></li>
                                                                <li ng-cloak ng-if="!data.Params.Location.city && data.Params.Location.state" ng-bind="data.Params.Location.state"></li>
                                                                <li ng-cloak ng-if="!data.Params.Location.city && !data.Params.Location.state && data.Params.Location.country" ng-bind="data.Params.Location.country"></li>
                                                                <li ng-cloak ng-if="data.Params.Date" data-toggle="tooltip" ng-attr-data-original-title="{{getTimeFromDate(UTCtoTimeZone(data.Params.Date));}}" ng-bind="journal_date_format(data.Params.Date)"></li>
                                                        </ul>
                                                        
                                                    </div>
                                                </div>
                                                <p class="news-feed-post-body-container" ng-if="data.PostContent">
                                                    <span ng-mouseup="get_selected_text($event, data.ActivityGUID);" ng-if="data.PostContent" ng-bind-html="textToLink(data.PostContent, false, 200)"></span>
                                                </p>

                                                <p ng-if="data.PostContent.length > 200 && data.ShowFull" ng-bind="parseLink(data.PostContent, false)"></p>

                                                <div ng-if="data.Album.length > 0" ng-class="getMediaClass(data.Album[0].Media)">
                                                    <div ng-repeat="m in data.Album[0].Media| limitTo:4"  ng-class="(data.Album[0].Media.length > 2) ? 'col-sm-3' : '';">
                                                        <figure ng-click="$emit('showMediaPopupGlobalEmit', m.MediaGUID, '');" ng-class="(m.MediaType == 'Video' && m.ConversionStatus == 'Pending' && data.Album[0].Media.length > 2) ? 'processing-skyblue' : (m.MediaType == 'Video' && m.ConversionStatus == 'Pending' && (data.Album[0].Media.length == 1 || data.Album[0].Media.length == 2)) ? 'processing-red' : ''">
                                                            <img ng-if="data.Album[0].Media.length == 1 && m.MediaType !== 'Video' && m.MediaFolder !== 'profile'" ng-src="{{ImageServerPath + 'upload/' + m.MediaFolder + '/' + m.ImageName}}">
                                                            <img ng-if="data.Album[0].Media.length > 1 && m.MediaType !== 'Video' && m.MediaFolder !== 'profile'" ng-src="{{ImageServerPath + 'upload/' + m.MediaFolder + '/750x500/' + m.ImageName}}">
                                                            <img ng-if="m.MediaType !== 'Video' && m.MediaFolder == 'profile'" ng-src="{{ImageServerPath + 'upload/' + m.MediaFolder + '/220x220/' + m.ImageName}}">
                                                            <img ng-if="m.MediaType == 'Video' && m.ConversionStatus == 'Finished'" ng-src="{{ImageServerPath + 'upload/' + m.MediaFolder + '/750x500/' + m.ImageName.substr(0, m.ImageName.lastIndexOf('.')) + '.jpg'}}">
                                                            <span ng-if="m.MediaType == 'Video' && m.ConversionStatus == 'Finished'" class="video-btn">
                                                                <i class="ficon-play"></i>
                                                            </span>
                                                            <span class="video-btn" ng-if="m.MediaType == 'Video' && m.ConversionStatus == 'Pending'">
                                                                <i class="ficon-video"></i>
                                                            </span>
                                                            <span ng-if="$index == 3 && data.Album[0].TotalMedia > 4" class="more-content" ng-bind="'+' + (data.Album[0].TotalMedia - 4)"></span>
                                                        </figure>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="activity-bar">
                                                <ul class="feed-actions">
                                                    
                                                    <li>
                                                        <span class="like-btn">
                                                            <i tooltip data-placement="top" data-container="body" ng-attr-data-original-title="{{(data.IsLike == '1') ? 'Unlike' : (data.NoOfLikes=='0') ? 'Be the first to like' : 'Like' ;}}" ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);" ng-class="data.IsLike == '1' ? 'ficon-heart active' : 'ficon-heart'" ></i>
                                                            <abbr ng-if="data.NoOfLikes > 0" ng-bind="data.NoOfLikes" ng-click="likeDetailsEmit(data.ActivityGUID, 'ACTIVITY');"></abbr>
                                                        </span>
                                                    </li>
                                                    
                                                    <li ng-if="data.CommentsAllowed == 0 && data.NoOfComments > 0">
                                                        <a ng-if="data.PostType !== '2'" ng-bind="'Comments (' + data.NoOfComments + ')'"></a>
                                                        <a ng-if="data.PostType == '2'" ng-bind="'Answers (' + data.NoOfComments + ')'"></a>
                                                    </li>
                                                    <li ng-if="data.CommentsAllowed == 1">
                                                        <a ng-click="postCommentEditor(data.ActivityGUID, FeedIndex);  data.showeditor = true;" ng-if="LoginSessionKey!='' && data.NoOfComments == 0">
                                                            Be the first to comment
                                                        </a>
                                                        <a ng-click="loginRequired()" ng-if="LoginSessionKey=='' && data.NoOfComments == 0">
                                                            Be the first to comment
                                                        </a>
                                                        <a ng-if="data.PostType !== '2' && data.NoOfComments > 0" ng-bind="'Comments (' + data.NoOfComments + ')'"></a>
                                                        <a ng-if="data.PostType == '2' && data.NoOfComments > 0" ng-bind="'Answers (' + data.NoOfComments + ')'"></a>
                                                    </li>
                                                    
                                                    
                                                    <li>
                                                        <a ng-click="likeEmit(data.ActivityGUID, 'ACTIVITY', data.ActivityGUID);">
                                                            <span class="icon">
                                                                <i class="ficon-share f-mlg"></i>
                                                            </span>
                                                        </a>
                                                    </li>
                                                </ul>
                                                <ul class="feed-action-right">
                                                    <li><a class="text-primary" ng-href="{{data.ActivityURL}}" ng-bind="::lang.j_h_actvt_pnl_rd_mr_link"></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </slick>

                    </ul>
                </div>
                <div class="panel-footer">
                    <a class="btn btn-default-outline btn-lg btn-md-size" ng-href="{{BaseUrl+'feed'}}" ng-bind="::lang.j_h_actvt_pnl_link"></a>
                </div>
            </div>
        </div>


        <footer class="footer" ng-cloak>
            <div class="container">
                <div class="foot-top">
                    <h2 class="title" ng-bind="::lang.j_h_ftr_ttl"></h2>
                    <a class="btn btn-default-outline white btn-lg" ng-href="{{BaseUrl + 'signup'}}" ng-bind="::lang.j_h_ftr_jn_us"></a>
                </div>
                <div class="foot-btm">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="navbar-bottom">
                                <a  class="logo-btm" ng-href="{{BaseUrl}}">                
                                    <img src="assets/img/logo-white.svg" alt="{{::lang.web_name}}">
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <ul class="nav nav-default">
                                <li><a ng-bind="::lang.j_h_ftr_lgl_ntc_link"></a></li>
                                <li><a ng-bind="::lang.j_h_ftr_privc_link"></a></li>
                                <li class="copyright">
                                    <span class="text"><script>document.write(new Date().getFullYear())</script>&nbsp;&nbsp;<a ng-href="{{BaseUrl}}">{{::lang.web_name}}</a></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-3">
                            <ul class="social-network">
                                <li>
                                    <a>
                                        <i class="ficon-facebook"></i>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="ficon-twitter"></i>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="ficon-linkedin"></i>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="ficon-googleplus"></i>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <i class="ficon-email"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer>



    </div>

</div>


</div>