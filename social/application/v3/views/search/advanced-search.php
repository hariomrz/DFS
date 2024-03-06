<div id="SearchCtrl" ng-controller="SearchCtrl" class="clearfix">
    <div ng-controller="WallPostCtrl as WallPost" id="WallPostCtrl" ng-init="getEntityList();">
    <?php $this->load->view('search/filter/'.$page) ?>
        <?php
        if (!isset($UserID)) {
            $UserID = $this->session->userdata('UserID');
        }
        ?>
        <input type="hidden" id="ActivityGuID" value="<?php echo isset($ActivityGuID) ? $ActivityGuID : ""; ?>" />
        <?php //$this->load->view('search/search-header') ?>
        <div>
            <?php
            if (isset($IsNewsFeed)) {
                $this->load->view('include/newsfeed_header');
            }
            ?>
            <div class="container wrapper">
                <div class="row" ng-cloak>
                    <?php $this->load->view('search/sidebar/left'); ?>
                    <aside class="col-sm-3 col-md-3 pull-right filter-fullwidth">
                        <?php $this->load->view('sidebars/right'); ?>
                    </aside>
                    <?php $this->load->view('search/'.$page); ?>
                </div>
            </div>
        </div>


        <input type="hidden" id="loginUserGUID" value="<?php echo $this->session->userdata('UserGUID'); ?>" />
        <input type="hidden" id="WallPageNo" value="1" />
        <input type="hidden" id="UserID" value="<?php if (isset($UserID)) {
                            echo $UserID;
                        } ?>" />
        <input type="hidden" id="AllActivity" value="<?php if (isset($AllActivity)) {
                            echo $AllActivity;
                        } ?>" />
        <input type="hidden" id="UserWall" value="1" />
        <input type="hidden" id="FeedSortBy" value="2" />
        <input type="hidden" id="IsMediaExists" value="2" />
        <input type="hidden" id="PostOwner" value="" />
        <input type="hidden" id="ActivityFilterType" value="0" />
        <input type="hidden" id="AsOwner" value="0" />
        <input type="hidden" id="PageNo" value="1" />
        <input type="hidden" id="Keyword" value="<?php echo $Keyword ?>" />

        <script type="text/javascript">
                    function changeEntityID(value){
                    $('#ShareEntityUserGUID').val(value);
                            }
        </script>
        <?php if (isset($RedirectPage)) { ?>
            <input type="hidden" id="RedirectPage" value="1">
        <?php } ?>
    </div>
</div>