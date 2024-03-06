<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php 
$contest_won_amount = array();
date_default_timezone_set('Asia/Kolkata');
$winning_amount = 0;
$bonus_winning = 0;
$points_winning = 0;
$merchandise_winning = array();
if(isset($content['currency_type']) && $content['currency_type'] == "0"){
    $currency_type = "B";
}else if(isset($content['currency_type']) && $content['currency_type'] == "2"){
    $currency_type = "C";
}else{
    $currency_type = $this->app_config['currency_code']['key_value'];
}

    if(isset($content) && isset($content['prize'])){
        //echo '<pre>';print_r($content['prize'] );die;
        foreach($content['prize'] as $win_prize){
            if($win_prize['prize_type'] == '0'){
                $bonus_winning = $bonus_winning + $win_prize['amount'];
            }else if($win_prize['prize_type'] == '1'){
                $winning_amount = $winning_amount + $win_prize['amount'];
                if(isset($contest_won_amount[$content['tournament_id']])){
                    $contest_won_amount[$content['tournament_id']] = $contest_won_amount[$content['tournament_id']] + $win_prize['amount'];
                }else{
                    $contest_won_amount[$content['tournament_id']] = $win_prize['amount'];
                }
            }else if($win_prize['prize_type'] == '2'){
                $points_winning = $points_winning + $win_prize['amount'];
            }else if($win_prize['prize_type'] == '3'){
                $merchandise_winning[] = $win_prize['amount'];
            }
        }
    }else{
        if($content['prize_type'] == "1"){
            if(isset($contest_won_amount[$content['tournament_id']])){
                $contest_won_amount[$content['tournament_id']] = $contest_won_amount[$content['tournament_id']] + $content['amount'];
            }else{
                $contest_won_amount[$content['tournament_id']] = $content['amount'];
            }
        }
        if(!empty($contest_won_amount))
        {
            $winning_amount = array_sum($contest_won_amount);
        }
    }



$winning_amount = number_format($winning_amount,"2",".","");
$bonus_winning = number_format($bonus_winning,"2",".","");
$won_str = array();
if($winning_amount > 0){
    $won_str[] = $currency_type.$winning_amount;
}
if($bonus_winning > 0){
    $won_str[] = "B".$bonus_winning;
}
if($points_winning > 0){
    $won_str[] = "C".$points_winning;
}
/*if(!empty($merchandise_winning)){
    $won_str[] = implode(",", $merchandise_winning);
}*/
$won_str = implode(", ", $won_str);
?>
<!--Start middle section-->
<tr class="text-center">
    <td colspan="3" class="info-td info-td_won">
        <h4>Congratulations!</h4>
        <p>You've Won tournament <strong><?php echo $content['name'].' </strong> scheduled at <strong>'.$content['start_date']. ' (UTC)  '.$content['name'].'  </strong> joined contests.'; ?></p>
    </td>
</tr>


<tr>
    <td colspan="3">
        <table class="winning-table"  border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Tournament Name :
                            <span  class="result-score">
                            <?php echo $content['name']; ?>
                            </span>
                        </p>
                        <p  class="result-head">Entry Fee :
                        <span  class="result-score">
                            <?php 
                            if($content['entry_fee'] > 0){
                                echo $currency_type.$content['entry_fee'];  
                            }else{
                                echo "Free";
                            }
                            ?>
                        </span>
                            </p>
                    </div> 
                </td>
            </tr>
            <tr>
                <td>
                    <div class="padding-5">
                        <p  class="result-head">No. of  Winners
                        <span  class="result-score"><?php echo $content['total_winner']; ?></span>
                       
                     </div>
                </td>
            </tr>
            <tr class="final-rank">
                <td>
                    <div class="padding-5">
                        <p class="result-head">Your Rank</p>
                        <p  class="result-score">#<?php echo $content['game_rank']; ?></p>
                    </div>
                </td>
                <td class="text-right">
                    <div class="padding-5">
                        <p class="result-head">Your Winnings</p>
                        <p  class="result-score green-color">
                          <?php 
                            if(isset($content['prize']) && isset($content['prize'])){
                                $tmp_prize_arr = array();
                                foreach($content['prize'] as $tmp_prize){
                                    if($tmp_prize['prize_type'] == '0'){
                                        $tmp_prize_arr[] = "B".$tmp_prize['amount'];
                                    }else if($tmp_prize['prize_type'] == '1'){
                                        $tmp_prize_arr[] = $currency_type.$tmp_prize['amount'];
                                    }else if($tmp_prize['prize_type'] == '2'){
                                        $tmp_prize_arr[] = "C".$tmp_prize['amount'];
                                    }else if($tmp_prize['prize_type'] == '3'){
                                        $tmp_prize_arr[] = $tmp_prize['amount'];
                                    }
                                }
                                echo implode(", ", $tmp_prize_arr);
                            }else{
                                  echo $currency_type.$content['amount'];
                                
                            }
                          ?>
                        </p>
                    </div> 
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php 

//echo '<pre>';print_r($content);die;

$tax_amount = 0;
$tds_percent = isset($this->app_config['allow_tds']['custom_data']['percent']) ? $this->app_config['allow_tds']['custom_data']['percent'] : 0;
$tds_amount = isset($this->app_config['allow_tds']['custom_data']['amount']) ? $this->app_config['allow_tds']['custom_data']['amount'] : 0;
foreach($contest_won_amount as $contest_winning){
  if($contest_winning > $tds_amount){
    $tmp_tax = ($contest_winning * $tds_percent)/100;
    $tax_amount = $tax_amount + $tmp_tax;
  }
}
?>
<tr>
    <td colspan="3">
        <table class="summary-table"  cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="padding-5">
                        <h5>Summary</h5>
                        <p class="result-head">Total Winnings</p>
                    </div>
                </td>
                <td class="text-right">
                    <div class="padding-5">
                        <p  class="result-score"><?php echo $won_str; ?></p>
                    </div> 
                </td>
            </tr>

            <tr>
                <td>
                    <div class="padding-5">
                        <p class="result-head">Net Winnings</p>
                    </div>
                </td>
                <td class="text-right">
                    <div class="padding-5">
                        <p  class="result-score green-color">
                        <?php 
                            echo $currency_type.number_format(($winning_amount - $tax_amount),2,".",""); 
                        ?>
                        </p>
                    </div> 
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td colspan="3">
        <div class="booking-btn-wrapper">
            <a href="<?php echo WEBSITE_URL; ?>lobby#pickem" class="booking-btn">
            
            <?php echo "Win More Cash"; 
            ?>
            </a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
