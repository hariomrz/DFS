<div ng-if="LoginSessionKey!==''" ng-cloak class="secondary-fixed-nav" >
    <div class="secondary-nav" headroom tolerance='0' offset='50' classes="{pinned:'headroom--pinned',unpinned:'headroom--unpinned',initial:'headroom'}">
        <div class="container">
            
            <?php if($pname != 'files' && $pname != 'links'): ?>
            <div class="row nav-row">
                <div class="col-sm-12 main-filter-nav"> 
                    <nav class="navbar navbar-default navbar-static">
                        <div class="navbar-header visible-xs">
                             <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#filterNav"> 
                                <span class="icon"><i class="ficon-filter"></i></span>
                            </button>
                        </div>
                        <div class="collapse navbar-collapse" id="filterNav">
                            <?php $this->load->view('include/filter-options') ?>
                        </div>
                    </nav>  
                </div>
            </div>
           <?php  endif; ?>  
           
        </div>
    </div>
</div>