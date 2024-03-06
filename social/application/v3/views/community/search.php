<div class="panel panel-content">
  <div class="container">
    <div class="row">
          <div class="col-md-6 col-md-offset-3" xng-init="isTransform=false">
            <div class="search-secondary search-panel">
              <div id="search-wrapper" class="input-group">
                  <span class="input-group-addon"><i class="ficon-search"></i></span>
                  <div tabindex="-1" ng-focus="isTransform=true;" ng-blur="isTransform=false;" initial-value="'<?php echo isset($Keyword) ? str_replace('%20',' ',$Keyword) : '' ; ?>'" angucomplete-alt id="searchinput" pause="300" remote-url="<?php echo site_url() ?>api/search/entity_home/5" remote-url-data-field="Data" search-fields="FirstName,LastName" title-field="FirstName,LastName" image-field="ProfilePicture" minlength="1"></div>
                  <div xng-if="!isTransform" class="type-box"
                    start-timeout="1500"
                    end-callback=""
                    type-speed="10"
                    back-speed="10"
                    loop= true
                    type-strings="[
                    'Post Graduate Programme in Public Policy and Management',
                    'Leadership role at Ashoka Innovators',
                    'Effective Teaching: Programme for IIMB Alumni'
                    ]" typed>
                  </div>

              </div>
            </div>
          </div>
      </div>
  </div>
</div>