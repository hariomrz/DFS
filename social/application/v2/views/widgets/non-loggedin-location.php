<div class="panel panel-transparent" id="MyAccountCtrl" ng-controller="teachManProfCtrl" ng-cloak> 
    
        <div class="panel-heading ">
            <h3 class="panel-title text-sm"><span class="text" ng-bind="lang.w_your_location"></span></h3>
        </div>
        <div class="panel-body transparent">
        <?php if(isset($location) && $location){ ?>
            <span ng-init="prefilllocation('<?php echo $City ?>','<?php echo $State ?>','<?php echo $Country ?>','<?php echo $CountryCode ?>','<?php echo $Lat ?>','<?php echo $Lng ?>');"></span>
        <?php } ?>

            <div class="form-group no-margin" >
                <div class="input-search right quick-search" ng-init="initGoogleLocation();">
                    <input type="text" id="address" name="location" ng-model="Location" placeholder="Enter your location" class="form-control" required/>
                    <input type="hidden" id="lat" value="" />
                    <input type="hidden" id="lng" value="" />
                  <div class="input-group-btn">
                    <button class="btn"><i class="ficon-search"></i></button>
                  </div>
                </div>
            </div> 
        </div> 
    
</div>