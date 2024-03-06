<?php
	if(empty($ActivityGUID)) {
		if(isset($IsNewsFeed) && $IsNewsFeed=='1') { 
?>
			<div>
<?php 
		} else { ?>
			<div ng-cloak="">
<?php 	} ?>
		
		<div class="theiaStickySidebar" ng-cloak="">
                    
                    <div class="panel-group">
<?php
			if(isset($IsNewsFeed) && $IsNewsFeed=='1') {
?>
                                <div ng-if="!(IsSingleActivity) || isOverlayActive" ng-cloak="" >
<?php
					if(!$IsLoggedIn) {
						//$this->load->view('widgets/non-loggedin-interest');
                                            $this->load->view('widgets/why-join');
                                            $this->load->view('widgets/non-loggedin-location');
					} else {
						$this->load->view('widgets/left_newsfeed');
					}
?>
				</div>
<?php
			} else {
				switch (trim(strtolower($this->page_name))) {
					case 'userprofile':
?>
				
<?php
					$this->load->view('widgets/intro');
?>				
<?php
					break;
				}
			}
?>
		<?php
			if(isset($IsNewsFeed) && $IsNewsFeed=='1') {

			} else {
				switch (trim(strtolower($this->page_name))) {
					case 'userprofile':
						$this->load->view('widgets/left_wall');
					break;
					case 'group':
                        if(empty($ActivityGUID)) {
                            $this->load->view('groups/about_group');
                            
                        }
					break;
				}
			}
		?>
                 </div>
                        
		</div>
	</div>
<?php
	}
?>