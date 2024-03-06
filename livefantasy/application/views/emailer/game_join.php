<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
date_default_timezone_set('Asia/Kolkata');
$contest_date = date("d M, Y",strtotime($content['season_scheduled_date']));
$prize_pool = CURRENCY_CODE_HTML.$content['prize_pool'];
if(isset($content['prize_distibution_detail']) && !empty($content['prize_distibution_detail'])){
    $is_tie_breaker = 0;
    $prizeAmount = array("bonus"=>0,"real"=>0,"point"=>0);
    foreach($content['prize_distibution_detail'] as $prize){
        $amount = $prize['amount'];
        if(isset($prize['max_value'])){
            $amount = $prize['max_value'];
        }
        if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
            $is_tie_breaker = 1;
        }else if(isset($prize['prize_type']) && $prize['prize_type'] == 0){
            $prizeAmount['bonus'] = $prizeAmount['bonus'] + $amount;
        }else if(isset($prize['prize_type']) && $prize['prize_type'] == 2){
            $prizeAmount['point'] = $prizeAmount['point'] + $amount;
        }else{
            $prizeAmount['real'] = $prizeAmount['real'] + $amount;
        }
    }
    if($is_tie_breaker == 1){
        $prize_pool = "WIN Prizes";
    }else{
        if($prizeAmount['real'] > 0){
            $prize_pool = CURRENCY_CODE_HTML.$prizeAmount['real'];
        }else if($prizeAmount['bonus'] > 0){
            $prize_pool = "B".$prizeAmount['bonus'];
        }else if($prizeAmount['point'] > 0){
            $prize_pool = "C".$prizeAmount['point'];
        }
    }
}

if(isset($content['currency_type']) && $content['currency_type'] == "0"){
    $currency_type = "B";
}else if(isset($content['currency_type']) && $content['currency_type'] == "2"){
    $currency_type = "C";
}else{
    $currency_type = CURRENCY_CODE_HTML;
}

?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>You've successfully joined the <span><?php echo $currency_type.$content['entry_fee']; ?></span> contest with a prize pool of <span><?php echo $prize_pool; ?></span> for <?php echo $content['collection_name']; ?> Innings <?php echo $content['inning']; ?> Over <?php echo $content['over']; ?> on <span><?php echo $contest_date; ?> (IST)</span>.</p>
        <p>Get in action to win big!</p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>my-contests" class="booking-btn">Check My Rank</a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>