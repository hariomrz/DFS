<div class="global-navigation">
        <nav class="global-navigation-inner  title visible-xs" id="naviTrigger">
            <i class="fa fa-angle-down"></i>
            <a class="active" id="current-tab"></a>
        </nav>
        <nav class="global-navigation-inner hidden-xs" id="naviDropdown">
            <?php
                $UGuID = '';
                if(isset($UserID)){
                    $UGuID = get_detail_by_id($UserID, 3, 'UserGUID' , 1);
                }
                if(isset($UID)){
                    $UGuID = get_detail_by_id($UID, 3, 'UserGUID' , 1);
                }
                
            ?>
            <a target="_self" href="<?php echo $wall_url ; ?>"><?php echo lang('wall');?></a>
            <a target="_self" href="<?php echo $wall_url.'/friends'; ?>"><?php echo lang('friends');?></a>
            <a target="_self" href="<?php echo $wall_url.'/followers'; ?>"><?php echo lang('followers');?></a>
            <a target="_self" href="<?php echo $wall_url.'/following'; ?>"><?php echo lang('following');?></a>            
        </nav>
    </div>