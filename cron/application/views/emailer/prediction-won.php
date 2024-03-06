<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
    <tr>
        <td colspan="3">
            <div style="padding:15px">
                <p style="font-size:15px; font-family:Arial, Helvetica, sans-serif;margin:0px;padding: 0 0 10px 0; font-weight:bold;">Dear <?php echo $user_name; ?>,</p>
                <p style="font-size:15px; font-family:Arial, Helvetica, sans-serif; margin:0px; padding:0; ">Congratulations on predicting the right answer for <?php echo $content['prediction_data']['home'].' vs '.$content['prediction_data']['away'];?> match. </p>
                <p><?php echo $content['amount'] ;?> Coins <b>WON</b></p>
                <span style="font-weight:bold;">Question: </span><br>
                <p style="padding: 7px 0 15px; margin: 0;">
                <?php echo $content['prediction_data']['desc'];?>
                </p>
                <span style="font-weight:bold;">Options: </span>
                <ul style="padding: 0; list-style-type: none;margin: 7px 0;">
                <?php 
                     foreach ($content['prediction_data']['options'] as $key => $value) { ?>
                    <li style="padding-bottom: 5px;">
                    <?php echo $value['option'];
                        if($value['is_correct'] == '1')
                        {
                            echo ' (Your choice and correct answer)';
                        }
                        ?>
                    </li>
                     <?php };?>
                   
                </ul>
                <p>
                     Predict more and Redeem it for Real Cash.
                </p>
                <p style="margin-bottom: 0;">
                 Cheers,<br>
                 <?php echo SITE_TITLE; ?> Team
                </p>
               
            </div>

        </td>
    </tr>   
  
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>