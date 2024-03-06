<?php echo $this->load->view("emailer/header",array(),TRUE);
$content = $content['contest_data'];
?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>



<tr>
  <td style="padding:0 30px 0 30px; background-color:#ffffff;"><p style="color:#3FAFEF; font-size:30px; font-family:Calibri; margin:0px; padding:0; font-weight:bold; line-height:50px; ">Hi <?php echo $user_name; ?>,</p></td>
</tr>
    <tr>
      <td colspan="3" style="padding:0 30px 0 30px; background-color:#ffffff;"><p style="color:#3FAFEF; font-size:30px; font-family:Calibri; margin:0px; padding:0; font-weight:bold; line-height:50px; ">Congratulations!</p></td>
    </tr>
   <tr>
      <td colspan="3" style="padding:0 30px 0 30px; background-color:#ffffff;"><p>You are a winner in the <?php echo $content[0]['contest_name'];?>.</p></td>
    </tr> 
      <tr>
      <td colspan="3" style="padding:0 30px 0 30px; background-color:#ffffff;">Here is how much you have won.</p></td>
    </tr>
    <tr>
      <td colspan="3" style="padding:0 30px 10px 30px;background-color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:14px;">
        
      <?php 
      
        if($content[0]['prize_type']==3)
        {
      ?>

        <table style="width:100%; border-collapse:collapse;">
          <tr>
            <th style="padding:4px 8px; text-align:left;">Contest Name</th>
            <th style="padding:4px 8px; text-align:left;">Entry Fee</th>
            <th style="padding:4px 8px; text-align:left;">Team Name</th>
            <th style="padding:4px 8px; text-align:left;">Prize Won </th>
          </tr>
          <?php 

           foreach ($content[0] as $key => $value) { ?>
              
           <tr>
            <td style="border:solid 1px #ccc;padding:4px 8px;background:#f9f9f9; font-weight:bold;"><?php echo $value['contest_name'] ?></td>
            <td style="border:solid 1px #ccc;padding:4px 8px;"><?php echo $value['entry_fee'] ?></td>
            <td style="border:solid 1px #ccc;padding:4px 8px;"><?php echo $value['team_name'] ?></td>
            <td style="border:solid 1px #ccc;padding:4px 8px;">
              <img src="<?php echo $value['prize_image'];?>" height='100' width='100'>
            </td>
          </tr>
         
         <?php } 
         
         ?>
          
        </table>

        <?php }else{ 
            $bonus_currency_code = '<img src="'.WEBSITE_URL.'cron/assets/img/b-icon.svg">';
            if($content[0]['prize_type'] ==0)
                {
                  $label = 'Bonus Cash Won '; 
                } 
                else
                {
                  $label = 'Real Cash Won '; 
                }
                $label = 'Won Amount'; 
          ?>

          <table style="width:100%; border-collapse:collapse;">
            <tr>
              <th style="padding:4px 8px; text-align:left;">Contest Name</th>
              <th style="padding:4px 8px; text-align:left;">
                Entry Fee
              </th>
              <th style="padding:4px 8px; text-align:left;">Team Name</th>
              <th style="padding:4px 8px; text-align:left;">
                <?php echo $label; ?>
              </th>
          </tr>
            <?php 
            $winning_amount = array_column($content, 'amount');
            $winning_amount = array_sum($winning_amount);
            $total_bonus_winning = 0;
            $total_real_winning = 0;
          
            foreach ($content as $key => $value) {

             
              $contest_currency_code = CURRENCY_CODE_HTML;
              if($value['prize_type'] == 0){
                $total_bonus_winning = $total_bonus_winning + $value['amount'];
                $contest_currency_code = $bonus_currency_code;
              }else if($value['prize_type'] == 1){
                $total_real_winning = $total_real_winning + $value['amount'];
              }
            ?>
              <tr>
                <td style="border:solid 1px #ccc;padding:4px 8px;background:#f9f9f9; font-weight:bold;"><?php echo $value['contest_name'] ?></td>
                <td style="border:solid 1px #ccc;padding:4px 8px;">
                  <?php echo CURRENCY_CODE_HTML." ".$value['entry_fee'] ?>
                </td>
                <td style="border:solid 1px #ccc;padding:4px 8px;">
                  <?php echo $value['team_name'] ?>
                </td>
                <td style="border:solid 1px #ccc;padding:4px 8px;">
                  <?php echo $contest_currency_code." ".$value['amount'] ?>
                </td>
              </tr>

           <?php }
             
         ?>
            
            <tfoot>
              <tr>
              <td style="padding:4px 8px; text-align:right;"><b style="font-weight:bold; text-align:right; line-height:0;">Prize Pool:</b></td>
              <td style="padding:4px 8px;"><b><?php if($content[0]['prize_type']==0 || $content[0]['prize_type']==1){ echo CURRENCY_CODE_HTML." "; } ?><?php echo  $value['prize_pool']; ?></b></td>
              <td style="padding:4px 8px; text-align:right;"><b style="font-weight:bold; text-align:right; line-height:0;">Total Amount Won:</b></td>
              <td style="padding:4px 8px;font-weight:bold; vertical-align:top;">
                <?php 
                  if($total_real_winning > 0){
                    echo CURRENCY_CODE_HTML." ".$total_real_winning."<br/>";
                  }
                  if($total_bonus_winning > 0){
                    echo $bonus_currency_code." ".$total_bonus_winning;
                  }
               ?>
              </td>
            </tr>
            </tfoot>
          </table>

        <?php } ?>

     </td>
    </tr>  
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td colspan="3" style="padding:0 30px 10px 30px;background-color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:14px;">
      Keep Playing, Keep Winning!
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Cheers,<br>
        <?php echo SITE_TITLE; ?> Team
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td></td>
</tr>
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>