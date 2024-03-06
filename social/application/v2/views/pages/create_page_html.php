<!-- New -->
<?php if($this->session->userdata('UserID')){ ?>
  <div class="panel panel-widget">
    <div class="panel-heading">
      <h3 class="panel-title">                       
        <span class="text" ng-bind="::lang.page_create_new_caps"></span>
      </h3>        
    </div>
    <div class="panel-body">
      <ul class="list-items-group">
        <li>
          <div class="list-items-sm">                           
            <div class="list-inner">
              <figure>
                <a><img ng-src="{{AssetBaseUrl}}img/crt-newpage.png" class="img-circle"  ></a>
              </figure>
              <div class="list-item-body">
                <p><?php echo lang('create_page_content') ?></p>                              
                <a class="btn btn-primary" href="<?php echo base_url()."pages/types";?>"><span class="icon"><i class="ficon-plus f-lg"></i></span><span class="text" ng-bind="::lang.page_create_caps"></span></a>
              </div>
            </div>
            
          </div>
        </li>
      </ul>
    </div>
  </div>
<?php } ?>
<!-- New -->