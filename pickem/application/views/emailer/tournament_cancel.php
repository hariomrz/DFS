<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
date_default_timezone_set('Asia/Kolkata');
$content=json_decode($content,true);
$start_date = date("d M Y h:i A",strtotime($content['start_date']));
$end_date = date("d M Y h:i A",strtotime($content['end_date']));
$currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:'';
?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        
        <p> The tournament <strong><?php echo $content['name']." from ".$start_date." to ".$end_date; ?></strong> is   
            canceled <?php echo $content['cancel_reason'];?>.<br>

            Joining fee, if any, will be refunded back to your account. 
            <br/><br/>
        </p>        
        <p><a href="<?php echo WEBSITE_URL.'lobby#cricket#pickem'; ?>">Click Here</a> to See More Upcoming Tournament.</p>
        <p>Please find the summary below:</p>
    </td>
</tr>

<tr>
    <td colspan="3">
        <table class="winning-table"  border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Tournament Name</p>
                        <p  class="result-score">
                            <?php echo $content['name']; ?>
                        </p>
                    </div> 
                </td>
                <td>
                    <div class="padding-5">
                        <p  class="result-head">Entry Fee</p>
                        <p  class="result-score">
                            <?php 
                            if($content['entry_fee'] > 0){
                                if($content['currency_type']==1){
                                    echo $currency_code.$content['entry_fee'];  
                                }else if($content['currency_type']==2){
                                    echo "C".$content['entry_fee'];
                                }else{
                                    echo "Amount ".$content['entry_fee'];
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

<tr><td>&nbsp;</td></tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
