<!--Banner-->

<div data-ng-controller="EventPopupFormCtrl">
<div class="banner">
  <div class="image-cover clearfix">
    <div class="cover-inner"> <img ng-src="{{EventDetail.EventCoverImage}}" alt="" title="" /> </div>
    <div class="container cover-content">
      <div class="dropdown changecover-dropdown">
        <button type="button" class="dropdown-toggle btn change-cover hidden-xs" data-toggle="dropdown">Change Cover</button>
        <ul class="dropdown-menu">
          <li> <a href="javascript:void(0);" onclick="$('#uploadPic').trigger('click');"> <span class="space-icon"><i class="ficon-upload"></i></span>Upload New </a>
            <div class="hiddendiv">
              <input type="file" name="" id="uploadPic">
            </div>
          </li>
          <li><a href="javascript:void(0);"><span class="space-icon"><i class="ficon-cross"></i></span>Remove</a></li>
        </ul>
      </div>
      <div class="row">
        <div class="profile-container">
          <div class="row" data-ng-init="GetEventDetail('<?php echo $auth['EventGUID']?>')">
            <aside class="profile-pic col-lg-6 col-md-6 col-xs-12 col-sm-6">
              <figure class="user-wall-thumb"> <img src="<?php echo ASSET_BASE_URL.'img/profiles/user_default.jpg' ?>"  alt="User" title="User" class="img-circle"/>
                <div class="loaderbtn">
                  <div class="spinner40"></div>
                </div>
              </figure>
              <div class="profile-info">
                <label class="user-name" ng-bind="EventDetail.Title"></label>
                <span class="secured hidden-xs"> <i ng-if="EventDetail.Privacy=='PRIVATE'" class="icon-whiteLock"></i> <i ng-if="EventDetail.Privacy=='PUBLIC'" class="icon-whiteLock"></i> <i ng-if="EventDetail.Privacy=='INVITE_ONLY'" class="icon-whiteLock"></i> </span>
                <p class="profile-nametitle">Cricket</p>
              </div>
              <div class="dropdown thumb-dropdown"> <a class="edit-profilepic dropdown-toggle" href="javascript:void(0);" data-toggle="dropdown"> <i class="ficon-pencil"></i> </a>
                <ul class="dropdown-menu">
                  <li> <a href="javascript:void(0);" onclick="$('#changeThumb').trigger('click');"> <span class="space-icon"><i class="ficon-upload"></i></span>Upload New </a>
                    <div class="hiddendiv">
                      <input type="file" name="" id="changeThumb">
                    </div>
                  </li>
                  <li><a href="javascript:void(0);"><span class="space-icon"><i class="ficon-cross"></i></span>Remove</a></li>
                </ul>
              </div>
            </aside>
            <aside class="wall-actions col-lg-6 col-md-6 col-xs-12 col-sm-6">
              <div class="inner-follow-frnds">
                <div class="btn-group">
                  <button aria-expanded="false"  class="btn btn-default dropdown-toggle" type="button"  ng-if="!loggedUserPresence" data-ng-click="UpdateUsersPresence('ATTENDING');"> <span class="text"  ><span >JOIN</span></span> </button>
                  <button aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button" ng-if="loggedUserPresence"> <span class="text" >{{loggedUserPresence}}</span><i class="caret" ></i> </button>
                  <ul role="menu" class="dropdown-menu" ng-if="loggedUserPresence">
                    <li><a href="javascript:void(0);" data-ng-click="UpdateUsersPresence('MAY_BE');">May Be</a></li>
                    <li><a href="javascript:void(0);" data-ng-click="UpdateUsersPresence('NOT_ATTENDING');">Not Attending</a></li>
                  </ul>
                </div>
                <div class="dropdown">
                  <button aria-expanded="true" data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"> <span class="icon"><i class="ficon-settings"></i></span> </button>
                  <ul role="menu" class="dropdown-menu">
                    <li><a href="javascript:void(0);" data-toggle="modal" data-ng-click="OpenEditEventBox();">Edit</a></li>
                    <li><a href="javascript:void(0);" data-ng-click="DeleteEvent();">Delete</a></li>
                  </ul>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </div>
    <div class="secondary-nav">
      <div class="container">
        <div class="row nav-row">
          <div class="col-lg-7 col-xs-9 col-md-7 col-sm-8">
            <aside class="pulled-nav tabs-menus">
              <div class="tab-dropdowns"> <a href="javascript:void(0);"> <i class="icon-smallcaret"></i> <span>WALL</span> </a> </div>
              <ul class="nav navbar-nav small-screen-tabs hidden-xs">
                <li class="active"><a href="<?php echo base_url();?>events/{{EventDetail.EventGUID}}/wall">WALL</a></li>
                <li ><a href="<?php echo base_url();?>events/{{EventDetail.EventGUID}}/members">MEMBERS</a></li>
                <li><a href="<?php echo base_url();?>events/{{EventDetail.EventGUID}}/media">MEDIA</a></li>
              </ul>
            </aside>
          </div>
          <a href="javascript:void(0);" class="clear-filter">Clear Filter</a><!-- #BeginLibraryItem "/Library/filters.lbi" -->
          <div class="col-lg-5 col-xs-3 col-md-5 col-sm-4">
            <aside class="filters">
              <div class="dropdown"> <a data-toggle="dropdown" class="dropdown-toggle btn btn-default" href="javascript:void(0);"> <span class="icon"><i class="ficon-filter"></i></span> </a>
                <ul class="dropdown-menu custom-filters">
                  <li class="list-head"><span>Filter by</span> <a href="javascript:void(0);" class="clear-filter">Clear Filter</a></li>
                  <li><a href="javascript:void(0);" data-rel="user">User</a></li>
                  <li><a href="javascript:void(0);" data-rel="type">Type</a></li>
                  <li><a href="javascript:void(0);"  data-rel="reported">Reported</a></li>
                  <li><a href="javascript:void(0);"  data-rel="date">Date</a></li>
                  <li class="sortby"><span>Sort By</span></li>
                  <li class="active"><a href="javascript:void(0);">Top Stories</a> </li>
                  <li><a href="javascript:void(0);">Recent Activity</a></li>
                </ul>
              </div>
            </aside>
          </div>
          
          <!--Filters corresonding search-->
          <div class="filters-search applyed-filter">
            <div class="hide"  id="user">
              <div  class="navbar-form">
                <div class="input-group global-search">
                  <input type="text" name="srch-filters" placeholder="Search by User" class="form-control">
                  <div class="input-group-btn">
                    <button type="button" class="btn-search"><i class="icon-search-gray"></i></button>
                  </div>
                </div>
              </div>
            </div>
            <div id="type" class="hide">
              <div class="btn-group navbar-form ">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button"> <span class="text">Sort By Type</span> <i class="caret"></i> </button>
                <ul role="menu" class="dropdown-menu type-dropdown pull-left">
                  <li><a href="javascript:void(0);">Media</a></li>
                  <li><a href="javascript:void(0);">Text</a></li>
                </ul>
              </div>
            </div>
            <div id="reported" class="hide">
              <div  class="navbar-form">
                <div class="input-group global-search">
                  <input type="text" name="srch-filters" placeholder="Search By Reported" class="form-control">
                  <div class="input-group-btn">
                    <button type="button" class="btn-search"><i class="icon-search-gray"></i></button>
                  </div>
                </div>
              </div>
            </div>
            <div id="date" class="hide">
              <div class="navbar-form">
                <form class="form-inline date-picker">
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="__ /__ /__" id="datepicker9" />
                    <span class="input-group-addon">To</span>
                    <input type="text" class="form-control" placeholder="__ /__ /__" id="datepicker10" />
                  </div>
                  <div class="form-group">
                    <button type="button" class="btn btn-primary">GO</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- #EndLibraryItem --></div>
      </div>
    </div>
  </div>
</div>
<!--//Banner-->