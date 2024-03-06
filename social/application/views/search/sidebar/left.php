<aside  class="col-sm-2 col-md-2 left-side-bar filter-fullwidth" data-scroll="fixed">
    <div class="panel-body panel-bottom-zero clear">
      <nav class="navbar navbar-vertical navbar-mobile" role="navigation"> 
        <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarStatic-rt"><?php echo ucfirst($page) ?></button>
            </div>
            <div id="navbarStatic-rt" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-block">
                    <li class="<?php if($page=='top'){ echo 'active'; } ?>">
                        <a target="_self" href="<?php echo site_url('search/top').'/'.$Keyword ?>">TOP</a>
                    </li>
                    <li class="<?php if($page=='content'){ echo 'active'; } ?>">
                        <a target="_self" href="<?php echo site_url('search').'/'.$Keyword ?>">CONTENT</a>
                    </li>
                    <li class="<?php if($page=='people'){ echo 'active'; } ?>">
                        <a target="_self" href="<?php echo site_url('search/people').'/'.$Keyword ?>">PEOPLE</a>
                    </li>
                    <li class="<?php if($page=='photo'){ echo 'active'; } ?>">
                        <a target="_self" href="<?php echo site_url('search/photo').'/'.$Keyword ?>">PHOTOS</a>
                    </li>
                    <li class="<?php if($page=='video'){ echo 'active'; } ?>">
                        <a target="_self" href="<?php echo site_url('search/video').'/'.$Keyword ?>">VIDEOS</a>
                    </li>
                    <li class="<?php if($page=='page'){ echo 'active'; } ?>" ng-if="SettingsData.m18==1">
                        <a target="_self" href="<?php echo site_url('search/page').'/'.$Keyword ?>">PAGES</a>
                    </li>
                    <li class="<?php if($page=='group'){ echo 'active'; } ?>" ng-if="SettingsData.m1==1">
                        <a target="_self" href="<?php echo site_url('search/group').'/'.$Keyword ?>">GROUPS</a>
                    </li>
                    <li class="<?php if($page=='event'){ echo 'active'; } ?>" ng-if="SettingsData.m14==1">
                        <a target="_self" href="<?php echo site_url('search/event').'/'.$Keyword ?>">EVENTS</a>
                    </li>
                    <!-- <li class="<?php if($page=='top'){ echo 'active'; } ?>">
                        <a target="_self" href="<?php echo site_url('search/top').'/'.$Keyword ?>">TOP</a>
                    </li> -->
                </ul>
            </div>
      </nav>
    </div>
</aside>