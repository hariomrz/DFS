<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php 
$contest_won_amount = array();
$converted_date = get_timezone(strtotime($content['match_data']['season_scheduled_date']),'d M Y',$this->app_config['timezone']);
$contest_date = $converted_date['date'];
$timezone =  $converted_date['tz'];
?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <?php if(isset($content['notification_type']) && $content['notification_type'] == "125"){ ?>
            <p>We regret to inform you that the contests have been canceled due to <?php echo $content['cancel_reason']; ?></p>
        <?php }else{ ?>
            <p>We regret to inform you that the contests have been canceled due to insufficient participation.</p>
        <?php } ?>
        <p>Don't worry though, you can join other contests to win more<?php if($content['contest_data'][0]['currency_type']==2){ echo " coins."; }else{ echo " prizes."; } ?></p>
        <p>Please find the summary below:</p>
    </td>
</tr>
<tr class="fixture winning-font">
        <td colspan="3">
            <table class="teams-table">
                <?php if($content['season_game_count']<=1) { ?> 
                <tr>
                    <td>
                        <img class="kol-logo" src="<?php echo $content['match_data']['home_flag']; ?>"/>
                    </td>
                    
                    <td class="team-name">
                        <div style="margin-bottom: -14px;">
                            <p><?php echo $content['match_data']['league_name']; ?></p>
                            <span class="text1"><?php echo $content['match_data']['home']; ?></span>
                            <span class="text2">vs </span>
                            <span class="text1"><?php echo $content['match_data']['away']; ?></span>
                        </div>
                        <p class="fixturetime">
                            <span class="text4"><?php echo $contest_date.'('.$timezone.') '; ?> </span>
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
                        <p  class="result-head">Contest Name</p>
                        <p  class="result-score">
                            <?php echo $team_row['contest_name']; ?>
                        </p>
                    </div> 
                </td>
                <td>
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
                        <p  class="result-head">No. of Teams</p>
                        <p  class="result-score"><?php echo $team_row['total_teams']; ?></p>
                    </div> 
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php } ?>
<tr><td>&nbsp;</td></tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>