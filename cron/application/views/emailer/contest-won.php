<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php 
$contest_won_amount = array();
$converted_date = get_timezone(strtotime($content['match_data']['season_scheduled_date']),'d M Y',$this->app_config['timezone']);
$contest_date = $converted_date['date'];
$timezone =  $converted_date['tz'];

$winning_amount = 0;
$bonus_winning = 0;
$points_winning = 0;
$merchandise_winning = array();
foreach($content['contest_data'] as $team_row){
    $winning_amount = $winning_amount + $team_row['amount'];
    $bonus_winning = $bonus_winning + $team_row['bonus'];
    $points_winning = $points_winning + $team_row['points'];

    $contest_id = $team_row['contest_id'];
    $contest_won_amount[$contest_id]['amount'] = $contest_won_amount[$contest_id]['amount'] + $team_row['amount'];
    $contest_won_amount[$contest_id]['bonus'] = $contest_won_amount[$contest_id]['bonus'] + $team_row['bonus'];
    $contest_won_amount[$contest_id]['points'] = $contest_won_amount[$contest_id]['points'] + $team_row['points'];
    if(isset($team_row['custom_data']['merchandise'])){
        $merchandise_winning[] = $team_row['custom_data']['merchandise'];
        $contest_won_amount[$contest_id]['merchandise'][] = $team_row['custom_data']['merchandise'];
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
if(!empty($merchandise_winning)){
    $won_str[] = implode(",", $merchandise_winning);
}
$won_str = implode(", ", $won_str);
?>
<!--Start middle section-->
<tr class="text-center">
    <td colspan="3" class="info-td info-td_won">
        <h4>Congratulations!</h4>
        <h5>You've Won 
          <span><?php echo $won_str; ?></span>
        </h5>
        <p><?php echo $content['match_data']['league_name']; ?></p>
    </td>
</tr>
<tr class="fixture winning-font">
        <td colspan="3">
            <table class="teams-table">
                <?php if(isset($content['match_data']['is_tour_game']) && $content['match_data']['is_tour_game'] == 1 && $content['season_game_count']<=1) { ?>
                    <tr>
                        <td class="team-name">
                            <p><b><?php echo $content['match_data']['tournament_name']; ?></b></p>
                            <p class="fixturetime">
                                <span class="text4"><?php echo $contest_date.' ('.$timezone.') '; ?></span>
                            </p>
                        </td>
                    </tr>
                <?php }else if($content['season_game_count']<=1) { ?> 
                    <tr>
                        <td>
                            <img class="kol-logo" src="<?php echo $content['match_data']['home_flag']; ?>"/>
                        </td>
                        
                        <td class="team-name">
                            <div style="margin-bottom: -14px;">
                                <span class="text1"><?php echo $content['match_data']['home']; ?></span>
                                <span class="text2">vs </span>
                                <span class="text1"><?php echo $content['match_data']['away']; ?></span>
                            </div>
                            <p class="fixturetime">
                                <span class="text4"><?php echo $contest_date.' ('.$timezone.') '; ?></span>
                            </p>
                            
                        </td>
                        
                        <td>
                            <img class="hyd-logo" src="<?php echo $content['match_data']['away_flag']; ?>"/>
                        </td>
                    </tr>
                <?php } ?>
            </table>
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
                        <p  class="result-head">Entry Fee</p>
                        <p  class="result-score">
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
                            </p>
                    </div> 
                </td>
            </tr>
            <tr>
                <td>
                    <div class="padding-5">
                        <p  class="result-head">No. of  Winners</p>
                        <p  class="result-score"><?php echo $team_row['total_winner']; ?></p>
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
                            $tmp_prize_arr = array();
                            if($team_row['amount'] > 0){
                                $tmp_prize_arr[] = CURRENCY_CODE_HTML.$team_row['amount'];
                            }
                            if($team_row['bonus'] > 0){
                                $tmp_prize_arr[] = "B".$team_row['bonus'];
                            }
                            if($team_row['points'] > 0){
                                $tmp_prize_arr[] = "C".$team_row['points'];
                            }
                            if(isset($team_row['custom_data']['merchandise'])){
                                $tmp_prize_arr[] = $team_row['custom_data']['merchandise'];
                            }
                            echo implode(", ", $tmp_prize_arr);
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
$indian = isset($this->app_config['allow_tds']['custom_data']['indian']) ? $this->app_config['allow_tds']['custom_data']['indian'] : 0;
$is_tds = $this->app_config['allow_tds']['key_value'] ? $this->app_config['allow_tds']['key_value'] : 0;

if($is_tds == 1 && $indian != 1){
    foreach($contest_won_amount as $contest_winning){
        if($contest_winning > $tds_amount){
          $tmp_tax = ($contest_winning * $tds_percent)/100;
          $tax_amount = $tax_amount + $tmp_tax;
        }
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
            <?php if($is_tds==1 && $indian!=1){ ?>
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
                        echo CURRENCY_CODE_HTML.number_format(($winning_amount - $tax_amount),2,".","");
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
            <a href="<?php echo WEBSITE_URL; ?>lobby" class="booking-btn">
            
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