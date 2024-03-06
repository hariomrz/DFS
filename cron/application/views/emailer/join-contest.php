<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
$converted_date = get_timezone(strtotime($content['season_scheduled_date']),'d M, Y',$this->app_config['timezone']);
$contest_date = $converted_date['date'];
$timezone =  $converted_date['tz'];

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
        <?php if($content['entry_fee'] > 0){ ?>
            <p>
                You've successfully joined the contest with an entry fee of <span><?php echo $currency_type.$content['entry_fee']; ?></span> & prize pool of 
                <?php /*if($content['prize_pool'] > 0){ ?>
                    <span>
                        <?php 
                        if($content['prize_type'] == "0"){
                            echo $content['prize_pool']." Bonus cash";
                        }else if($content['prize_type'] == "1"){
                            echo CURRENCY_CODE_HTML.$content['prize_pool'];
                        }else if($content['prize_type'] == "2"){
                            echo $content['prize_pool']." Coin";
                        }
                        ?>
                    </span>
                <?php }else{ ?>
                    <span>Free</span>
                <?php }*/ ?>
                <span><?php echo $prize_pool; ?></span>
                for the 
                <span><?php echo (!empty($content['collection_name'])) ?  $content['collection_name'] : $content['contest_name']; ?></span> game on <span><?php echo $contest_date.'('.$timezone.').'; ?></span>
            </p>
        <?php }else{ ?>            
                    <?php if($content['prize_pool'] > 0){ ?>
                        <p>
                            You've successfully joined the free contest  with a prize pool of 
                            <?php if($content['prize_pool'] > 0){ ?>
                                <span>
                                    <?php 
                                    if($content['prize_type'] == "0"){
                                        echo $content['prize_pool']." Bonus cash";
                                    }else if($content['prize_type'] == "1"){
                                        echo CURRENCY_CODE_HTML.$content['prize_pool'];
                                    }else if($content['prize_type'] == "2"){
                                        echo $content['prize_pool']." Coin";
                                    }
                                    ?>
                                </span>
                            <?php }else{ ?>
                                <span>Free</span>
                            <?php } ?>
                            for the<br> 
                            <span><?php echo (!empty($content['collection_name'])) ?  $content['collection_name'] : $content['contest_name']; ?></span>on <span><?php echo $contest_date.' ('.$timezone.') '; ?></span>
                        </p>
                    <?php } else{ ?> 

                        <p>
                            You've successfully joined the free contest for<br> 
                            <span><?php echo (!empty($content['collection_name'])) ?  $content['collection_name'] : $content['contest_name']; ?></span> on <span><?php echo $contest_date.' ('.$timezone.') '; ?></span>
                        </p>                        

                    <?php } ?>
        <?php } ?>
        <p>Sit back, relax & watch your All-Star Team in action!</p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>my-contests" class="booking-btn">Check My Rank</a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>