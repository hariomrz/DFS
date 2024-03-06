<?php
	if ((isset($IsNewsFeed) && $IsNewsFeed == '1') || ( $pname == 'wall' ) || ( $pname == 'search' ) || isset($ActivityGUID)) {
		$class="col-md-3";
	}
	else
	{
		$class="col-md-4";
	}
    if($pname == 'search')
    {
        $class .= " hidden-sm";
    }
    if($this->page_name=='events')
    {
        $this->load->view('events/EventSideBar');
    }
?>
<div class="" ng-cloak="">
    <?php
        if (isset($IsNewsFeed) && ( $IsNewsFeed == '1' ) && trim(strtolower($this->page_name))!='search') {
            if ( $IsLoggedIn ) {
            //$this->load->view('widgets/my-desk');
                }
        }
    ?>
    <?php if(isset($IsNewsFeed) && $IsNewsFeed == '1' && trim(strtolower($this->page_name))!='search' && $IsLoggedIn) { ?>
    <div class="panel panel-info" ng-if="hasCreateContestPermission" ng-cloak="">
            <a class="link-contest" ng-click="updateIsContest(1); showNewsFeedPopup();"><span class="icon"><i class="ficon-trophy f-orange"></i></span><span class="text">CREATE A CONTEST</span></a>
        </div>
    <?php } ?>
    <?php
    if (isset($IsNewsFeed) && $IsNewsFeed == '1') {
        if($IsLoggedIn)
        {
            $this->load->view('widgets/reminder-calander');
        }
    }
    if($this->page_name=='pages' && in_array($pname, ['files', 'links']) )
    {
        $this->load->view('pages/about_page'); 
    }
    if ( ( isset($pname) && in_array($pname, ['wall', 'files', 'links']) ) || ( ( isset($pname) && ( $pname != 'search' ) ) && ( isset($IsNewsFeed) && $IsNewsFeed == 1 ) ) ) {
        
        if(!$this->settings_model->isDisabled(42)){
            $show_sticky = true;
            if(isset($IsNewsFeed) && $IsNewsFeed == 1)
            {
                if(!$IsLoggedIn)
                {
                    $show_sticky = false;
                }
            }
            if(!empty($ActivityGUID))
            {
                $show_sticky = false;  
            }
            if($this->page_name=='events')
            {
                $show_sticky = false;    
            }
        }else{
            $show_sticky = false;    
        }
        if($show_sticky)
        {
            $this->load->view('widgets/sticky-post');
        }
        
    }
    if($this->page_name=='pages' && in_array($pname, ['files', 'links']) )
    {
        $this->load->view('pages/create_page_html'); 
    }
    ?>
    <?php 
        if (isset($IsNewsFeed) && $IsNewsFeed == '1') {
            if(!$IsLoggedIn)
            {
               //$this->load->view('widgets/why-join');
            }
        }
    ?>
    <?php if(trim(strtolower($this->page_name))!='group'){ ?>
    <div class="" ng-if="!(IsSingleActivity)" ng-cloak="">
        <?php
        if (isset($IsNewsFeed) && $IsNewsFeed == '1' && trim(strtolower($this->page_name))!='search') {
            if($IsLoggedIn)
            {
                $this->load->view('widgets/right_newsfeed');       
            }
            else
            {
                if(!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
                    $this->load->view('widgets/people-you-may-know'); 
                } else {
                    $this->load->view('widgets/people-you-may-follow'); 
                }    
                
                $this->load->view('widgets/suggested-groups');
                $this->load->view('widgets/suggested-pages');
                $this->load->view('widgets/event-near-you');
            }
        } else {
            switch (trim(strtolower($this->page_name))) {
                case 'userprofile':
                    if ($pname == 'wall') {
                        $this->load->view('widgets/right_wall');   
                        /*
                        $this->load->view('widgets/user-connection');
                        $this->load->view('widgets/user-groups');
                        $this->load->view('widgets/user-pages');
                        $this->load->view('widgets/entities-i-follow');
                        $this->load->view('widgets/recent-activities');*/
                    } else {
                        if(!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
                            $this->load->view('widgets/people-you-may-know'); 
                        } else {
                            $this->load->view('widgets/people-you-may-follow'); 
                        }    
                    }
                    break;
                case 'search':
                        $this->load->view('widgets/new-members');
                        $this->load->view('widgets/my-events');
                        
                        if(!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
                            $this->load->view('widgets/people-you-may-know'); 
                        } else {
                            $this->load->view('widgets/people-you-may-follow'); 
                        }    
                        
                        $this->load->view('widgets/suggested-groups');
                        $this->load->view('widgets/suggested-pages');
                        if(!$this->settings_model->isDisabled(30)) {
                            $this->load->view('widgets/suggested-polls');
                        }
                    break;
                case 'group':
                        if(!$this->settings_model->isDisabled(42) && isset($pname) && $pname == 'wall')
                        {
                            $this->load->view('widgets/sticky-post'); 
                        }
                    break;
            }
        }
        ?>
    </div>
    <?php } ?>
        <?php
        if (isset($IsNewsFeed) && $IsNewsFeed == '1' && $IsLoggedIn) {            
        }
        else
        {
            $post_type='';
            if(!empty($ActivityGUID))
            {
                $activity_data = get_detail_by_guid($ActivityGUID, 0, 'PostType', 2);
                if($activity_data)
                {
                    $post_type=$activity_data['PostType'];
                }
                if($post_type!=4)
                {
                    $this->load->view('widgets/similar-discussions');
                }
            }
            switch (trim(strtolower($this->page_name)))
            {
                case 'group':
                    
                    $this->load->view('widgets/right_groupwall',array('post_type'=>$post_type));
                    
                break;
            }
            if($post_type==4)
            {
                $this->load->view('widgets/fav-article');
                $this->load->view('widgets/recommended-articles');
            }
            
        }
        ?>
</div>