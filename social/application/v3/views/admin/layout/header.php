<!--Header Start-->
<?php if( $this->session->userdata('AdminLoginSessionKey') != ''){ ?>
  <div class="header-section">   
    <header>
       <section class="header-wrapper">               
          <a class="logo-block" name="logo">
              <?php if(isset($global_settings['header']['logo'])){?>
                  <img src="<?php echo $global_settings['header']['logo']?>">
              <?php }?>
          </a>
           <div class="header-right">            
              <nav class="main-nav">
                <ul>
                    <?php 
                      if(isset($global_settings['navigations']))
                          echo create_menu($global_settings['navigations']);
                    ?>
                </ul>
              </nav>
              <div class="nav-right">
                <ul class="right-control">
                  <li>
                    <a href="javascript:void(0);" class="user-setting"><i class="icon-setting"></i></a>
                        <ul class="action-dropdown userSettingdropdown">
                         
                            
                            <li><a href="javascript:void(0)" onclick="signout();">Logout</a></li>
                          </ul>
                    </li>                    
                    <?php if($this->show_date_filter){ ?>
                      <li class="date-picker">
                       <?php 
                              //Default : start and end date
                               if (isset($global_settings['date_format']))
                               {
                                  $startDate = date($global_settings['date_format'], strtotime("01/01/2014"));
                                  $endDate = date($global_settings['date_format']);

                                  $dateFilterText = "All";
                               }
                              
                              //If start and end date set in session then get and use
                              if($this->session->userdata('startDate') && $this->session->userdata('endDate'))
                              {
                                  $startDate = date($global_settings['date_format'], strtotime($this->session->userdata('startDate')));
                                  $endDate = date($global_settings['date_format'], strtotime($this->session->userdata('endDate')));
                              }
                              
                              if($this->session->userdata('dateFilterText')){
                                  $dateFilterText = $this->session->userdata('dateFilterText');
                              }else{
                                  $this->session->set_userdata('dateFilterText', $dateFilterText);
                              }
                       ?>
                       <a href="javascript:void(0);" class="month-view">
                           <span id="dateFilterText"><?php echo $dateFilterText; ?></span>
                           <input type="hidden" id="SpnFrom" value="<?php echo $startDate;?>"/>
                           <input type="hidden" id="SpnTo" value="<?php echo $endDate;?>"/> 
                            <i class="ficon-arrow-down"></i>
                       </a>

                       <div class="action-dropdown monthView wid210" id="date_dropdown">
                            <ul class="viewList">
                                  <?php 
                                        $show_custom_option = false;
                                        if(!empty($global_settings['top_navigation_date']))
                                        {
                                          //Show if visible="Y" in custom config file
                                          foreach($global_settings['top_navigation_date'] as $key => $val)
                                          {
                                            if($val['visible'] == 'Y' && $val['name'] != 'Custom')
                                            {
                                  ?>
                                              <li><a href="javascript:void(0);" onclick="return SaveDates('<?php echo $val['parameters']?>')"><?php echo $val['name']?></a></li>

                                  <?php      }
                                             //If option is 'Custom' then apply other mechanism 
                                             if($val['visible'] == 'Y' && $val['name']== 'Custom')
                                                 $show_custom_option =true;

                                          }
                                        } 
                                  ?>
             </ul>

                             <?php if($show_custom_option == true){ ?>
                                 <ul class="custom-select">
                                       <li><a href="javascript:void(0);" class="customSelect">Custom</a></li>
                                 </ul>
                             <?php }?>


                            <ul class="customView">
                               <li>
                                  <div class="form-group">
                                    <label class="label-control">From</label>
                                    <input type="text" class="form-control" id="dateFrom" value="<?php echo $startDate;?>">
                                  </div>
                               </li>
                               <li>
                                  <div class="form-group">
                                      <label class="label-control">To</label>
                                      <input type="text"  class="form-control"  id="dateTo" value="<?php echo $endDate;?>">
                                  </div>
                               </li>
                               <li>
                                  <a href="javascript:void(0);" class="btn btn-default" id="submitBtn">Submit</a>
                               </li>
                              </ul>
                        </div>  
                      </li>
                    <?php } ?>
             </ul>
            </div>
          </div>
        </section>
      </header>
    </div> 

         <!-- New Dashboard Header -->
        <?php if ( $this->page_name == 'dashboard' ): ?>
          <?php //$this->load->view('admin/dashboard/dashboardEntityList'); ?>
        <?php endif; ?>


<?php }?>
<script>
var image_path='<?php echo IMAGE_SERVER_PATH.'upload/'; ?>'
var image_server_path='<?php echo IMAGE_SERVER_PATH; ?>'
var NodeAddr = '<?php echo NODE_ADDR;?>';
var AssetBaseUrl = '<?php echo ASSET_BASE_URL ?>';
var partialsUrl = '<?php echo ASSET_BASE_URL ?>admin/js/app/partials/';
var IsAdminView = 1;
var IsFileTab = 0;
var LoginSessionKey = '';

var user_url = base_url;
var profile_picture = base_url;
var login_user_name = '';
var time_zone_offset=0;

var TomorrowDate = '';
var NextWeekDate = '';
var DisplayTomorrowDate = '';
var DisplayNextWeekDate = '';
var accept_language = '<?php echo $this->config->item("language"); ?>';
var TimeZone = "UTC";
</script>
<!--Header End-->

