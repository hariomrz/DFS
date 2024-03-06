<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php 
$contest_won_amount = array();
date_default_timezone_set('Asia/Kolkata');
$content=json_decode($content,true);
$contest_date = date("d M Y",strtotime($content['match_data']['scheduled_date']));
$winning_amount = 0;
$bonus_winning = 0;
$points_winning = 0;
$merchandise_winning = array();
foreach($content['contest_data'] as $team_row){
    if(isset($team_row['custom_data']) && isset($team_row['custom_data']['prize'])){
        foreach($team_row['custom_data']['prize'] as $win_prize){
            if($win_prize['prize_type'] == '0'){
                $bonus_winning = $bonus_winning + $win_prize['amount'];
            }else if($win_prize['prize_type'] == '1'){
                $winning_amount = $winning_amount + $win_prize['amount'];
                if(isset($contest_won_amount[$team_row['contest_id']])){
                    $contest_won_amount[$team_row['contest_id']] = $contest_won_amount[$team_row['contest_id']] + $win_prize['amount'];
                }else{
                    $contest_won_amount[$team_row['contest_id']] = $win_prize['amount'];
                }
            }else if($win_prize['prize_type'] == '2'){
                $points_winning = $points_winning + $win_prize['amount'];
            }else if($win_prize['prize_type'] == '3'){
                $merchandise_winning[] = $win_prize['name'];
            }
        }
    }else{
        if($team_row['prize_type'] == "1"){
            if(isset($contest_won_amount[$team_row['contest_id']])){
                $contest_won_amount[$team_row['contest_id']] = $contest_won_amount[$team_row['contest_id']] + $team_row['amount'];
            }else{
                $contest_won_amount[$team_row['contest_id']] = $team_row['amount'];
            }
        }
        if(!empty($contest_won_amount))
        {
            $winning_amount = array_sum($contest_won_amount);
        }
    }
}
$winning_amount = number_format($winning_amount,"2",".","");
$bonus_winning = number_format($bonus_winning,"2",".","");
$won_str = array();
if($winning_amount > 0){
    $won_str[] = CURRENCY_CODE_HTML.$winning_amount;
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
        <p>You've Won <strong><?php echo $content['match_data']['league_name'].' '.$content['match'].' </strong> scheduled at <strong>'.$content['match_data']['scheduled_date']. ' (UTC)  '.$content['contest_name'].'  </strong> joined contests.'; ?></p>
    </td>
</tr>

<?php 
foreach($content['contest_data'] as $team_row){
?>
<tr>
    <td colspan="3">
        <table class="winning-table"  border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Contest Name :
                            <span  class="result-score">
                            <?php echo $team_row['contest_name']; ?>
                            </span>
                        </p>
                        <p  class="result-head">Entry Fee :
                        <span  class="result-score">
                            <?php 
                            if($team_row['entry_fee'] > 0){
                                if($team_row['currency_type']==1){
                                    echo CURRENCY_CODE_HTML.$team_row['entry_fee'];  
                                }else if($team_row['currency_type']==2){
                                    echo "C".$team_row['entry_fee'];
                                }else{
                                    echo "Amount ".$team_row['entry_fee'];
                                }
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
                        <span  class="result-score"><?php echo $team_row['total_winner']; ?></span>
                       
                     </div>
                </td>
                <td class="text-right">
                    <div class="padding-5">
                        <p  class="result-head">Winning Team</p>
                        <p  class="result-score"><?php echo $team_row['team_name']; ?></p>
                    </div> 
                </td>
            </tr>
            <tr class="final-rank">
                <td>
                    <div class="padding-5">
                        <p class="result-head">Your Rank</p>
                        <p  class="result-score">#<?php echo $team_row['user_rank']; ?></p>
                    </div>
                </td>
                <td class="text-right">
                    <div class="padding-5">
                        <p class="result-head">Your Winnings</p>
                        <p  class="result-score green-color">
                          <?php 
                            if(isset($team_row['custom_data']) && isset($team_row['custom_data']['prize'])){
                                $tmp_prize_arr = array();
                                foreach($team_row['custom_data']['prize'] as $tmp_prize){
                                    if($tmp_prize['prize_type'] == '0'){
                                        $tmp_prize_arr[] = "B".$tmp_prize['amount'];
                                    }else if($tmp_prize['prize_type'] == '1'){
                                        $tmp_prize_arr[] = CURRENCY_CODE_HTML.$tmp_prize['amount'];
                                    }else if($tmp_prize['prize_type'] == '2'){
                                        $tmp_prize_arr[] = "C".$tmp_prize['amount'];
                                    }else if($tmp_prize['prize_type'] == '3'){
                                        $tmp_prize_arr[] = $tmp_prize['name'];
                                    }
                                }
                                echo implode(", ", $tmp_prize_arr);
                            }else{
                                if($team_row['prize_type'] == "0"){
                                  echo "B".$team_row['amount'];
                                }else if($team_row['prize_type'] == "2"){
                                  echo "C".$team_row['amount'];
                                }else{
                                  echo CURRENCY_CODE_HTML.$team_row['amount'];
                                }
                            }
                          ?>
                        </p>
                    </div> 
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php } 
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
            <?php if($content['int_version'] == "0"){ ?>
                            <tr>
                <td>
                    <div class="padding-5">
                        <p class="result-head">Taxes</p>
                        <p class="taxes-para">(Taxes are applicable only on winning above Rs. <?php echo $tds_amount; ?> in a single contest)</p>
                    </div>
                </td>
                <td class="text-right">
                    <div class="padding-5">
                        <p  class="result-score">- <?php echo CURRENCY_CODE_HTML.$tax_amount; ?></p>
                    </div> 
                </td>
            </tr>
            <?php } ?>
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
                        //echo CURRENCY_CODE_HTML.number_format(($winning_amount - $tax_amount),2,".","");
                        if($content['int_version']==1){
                            echo "C".$points_winning;
                        }
                        else{
                            echo CURRENCY_CODE_HTML.number_format(($winning_amount - $tax_amount),2,".",""); 
                          }

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
            <a href="<?php echo WEBSITE_URL; ?>lobby#prop-fantasy" class="booking-btn">
            
            <?php
                       if($content['int_version']==1){
                           echo "Win More Coins"; 
                        }
                        else{
                            echo "Win More Cash"; 
                            }
            ?>
            </a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
