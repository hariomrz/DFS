<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
date_default_timezone_set('Asia/Kolkata');
$content=json_decode($content,true);
$contest_date = date("d M Y h:i A",strtotime($content['scheduled_date']));
?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        
        <p> The contest <strong><?php echo $content['contest_name']." - ".$contest_date; ?></strong> is   
            canceled <?php echo $content['cancel_reason_type'];?>.<br>
        </p>        
        <p>The contest joining fee, if any, is refunded in your wallet.
        </p>
        <p><a href="<?php echo WEBSITE_URL.'lobby#cricket#pick-fantasy'; ?>">Click Here</a> to See More Upcoming Contests.</p>
        <p>Please find the summary below:</p>
    </td>
</tr>
<?php 
if(!empty($content)){
$team_row = $content['contest_data'][0];
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
        </table>
    </td>
</tr>
<?php } ?>
<tr><td>&nbsp;</td></tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
