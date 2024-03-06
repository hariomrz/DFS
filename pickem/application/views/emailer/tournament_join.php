<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
date_default_timezone_set('Asia/Kolkata');
$content=json_decode($content,true);
$contest_date = date("d M, Y",strtotime($content['start_date']));
$currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:'';
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

}

if(isset($content['currency_type']) && $content['currency_type'] == "0"){
    $currency_type = "B";
}else if(isset($content['currency_type']) && $content['currency_type'] == "2"){
    $currency_type = "C";
}else{
    $currency_type = $this->app_config['currency_code']['key_value'];
}

?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <p>You've successfully joined the <span><?php echo $currency_type.$content['entry_fee']; ?></span> tournament </span> for <?php echo $content['name']; ?>.</span> 
            <span>Tournament start at <?php echo date('d M Y H:i',strtotime($content['start_date'])); ?> </span>.</p>
        <p>Get in action to win big!</p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>my-contests" class="booking-btn">Check My Rank</a> <!--  on <span><?php echo $contest_date; ?> (IST) -->
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
