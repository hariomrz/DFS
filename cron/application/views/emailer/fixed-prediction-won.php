<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
$converted_start_date = get_timezone(strtotime($content['start_date']),'d M Y',$this->app_config['timezone']);
$converted_end_date = get_timezone(strtotime($content['end_date']),'d M Y',$this->app_config['timezone']);
$start_date = $converted_start_date['date'];
$end_date = $converted_end_date['date'];
$timezone =  $converted_start_date['tz'];

$prize_str = array();
if(isset($content['custom_data'])){
    foreach ($content['custom_data'] as $key => $value) {
        if($value['prize_type'] == 0){
            $prize_str[] = "B ".$value['amount'];
        }else if($value['prize_type'] == 1){
            $prize_str[] = CURRENCY_CODE_HTML." ".$value['amount'];
        }else if($value['prize_type'] == 2){
            $prize_str[] = "C ".$value['amount'];
        }if($value['prize_type'] == 3){
            $prize_str[] = $value['amount'];
        }
    }
}
$prize_str = implode(", ", $prize_str);
?>
    <tr>
        <td colspan="3">
            <div style="padding:15px">
                <p style="font-size:15px; font-family:Arial, Helvetica, sans-serif;margin:0px;padding: 0 0 10px 0; font-weight:bold;">Dear <?php echo $user_name; ?>,</p>
                <p style="font-size:15px; font-family:Arial, Helvetica, sans-serif; margin:20px 0px 20px 0px; padding:0; ">
                <?php if($notification_type == 226){ ?>
                    Congratulations! You have won <?php echo $prize_str; ?> on Weekly Leaderboard of Week <?php echo $start_date.'('.$timezone.') '; ?> to <?php echo $end_date.'('.$timezone.') '; ?> by achieving <?php echo $content['rank_value']; ?> rank.
                <?php }else if($notification_type == 227){ ?>
                    Congratulations! You have won <?php echo $prize_str; ?> on Monthly Leaderboard of <?php echo $start_date.'('.$timezone.') '; ?> month by achieving <?php echo $content['rank_value']; ?> rank.
                <?php }else{ ?>
                    Congratulations! You have won <?php echo $prize_str; ?> on Daily Leaderboard of <?php echo $start_date.'('.$timezone.') '; ?> by achieving <?php echo $content['rank_value']; ?> rank. 
                <?php } ?>
                </p>
                <p style="margin-bottom: 0;">
                 Cheers,<br>
                 <?php echo SITE_TITLE; ?> Team
                </p>
            </div>

        </td>
    </tr>   
  
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>