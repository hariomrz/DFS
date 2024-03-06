<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php 
$contest_won_amount = array();
date_default_timezone_set('Asia/Kolkata');
$content=json_decode($content,true);
//$start_date = date("d M Y",strtotime($content['content']['scheduled_date']));


?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td info-td_won">
        <h4>Dear <?php echo $user_name;?></h4>
        <p>We have amazing news for you. You just won  <strong><?php echo $content['winning'];?></strong> against <?php echo $content['team_name'];?> <strong> completed on <?php echo $content['end_date'];?> (UTC)    </strong></p>
    </td>
</tr>



<tr>
    <td colspan="3">
        <table class="winning-table"  border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Entry Name:
                            <span  class="result-score">
                            <?php echo $content['team_name']; ?>
                            </span>
                        </p>

                    </div> 
                </td>
                 <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Stake:
                            <span  class="result-score">
                            <?php echo $content['entry_fee']; ?>
                            </span>
                        </p>

                    </div> 
                </td>

                 <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Winning:
                            <span  class="result-score">
                            <?php echo $content['winning']; ?>
                            </span>
                        </p>

                    </div> 
                </td>

                 <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Entry Start Time:
                            <span  class="result-score">
                            <?php echo $content['start_date']; ?>
                            </span>
                        </p>

                    </div> 
                </td>

                 <td colspan="2">
                    <div class="padding-5">
                        <p  class="result-head">Entry completion time:
                            <span  class="result-score">
                            <?php echo $content['end_date']; ?>
                            </span>
                        </p>

                    </div> 
                </td>
            </tr>

        </table>
    </td>
</tr>


<!--middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
