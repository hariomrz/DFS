<!-- //Header -->
    <div class="secondary-fixed-nav">
        <div class="secondary-nav">
            <div class="container">
                <div class="row nav-row">
                    <div class="col-sm-12">
                        <aside class="filters search-filters visible-xs">
                            <a class="btn btn-default" data-menu="filter" slide-mobile-menu>
                                <span class="icon"><i class="ficon-filter"></i></span>
                            </a>
                        </aside>
                        <aside class="pulled-nav tabs-menus marging-0">
                            <div class="tab-dropdowns">
                                <a> <i class="icon-smallcaret"></i> <span>CONTENT</span> </a>
                            </div>
                            <ul class="nav navbar-nav small-screen-tabs hidden-xs">
                                <li class="<?php if($page=='people'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search/people').'/'.$Keyword ?>">PEOPLE</a></li>
                                <li class="<?php if($page=='photo'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search/photo').'/'.$Keyword ?>">PHOTOS</a></li>
                                <li class="<?php if($page=='video'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search/video').'/'.$Keyword ?>">VIDEO</a></li>
                                <li class="<?php if($page=='page'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search/page').'/'.$Keyword ?>">PAGES</a></li>
                                <li class="<?php if($page=='group'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search/group').'/'.$Keyword ?>">GROUPS</a></li>
                                <li class="<?php if($page=='event'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search/event').'/'.$Keyword ?>">EVENTS</a></li>
                                <li class="<?php if($page=='content'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search').'/'.$Keyword ?>">CONTENT</a></li>
                                <li class="<?php if($page=='top'){ echo 'active'; } ?>"><a target="_self" href="<?php echo site_url('search/top').'/'.$Keyword ?>">TOP</a></li>
                            </ul>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </div>