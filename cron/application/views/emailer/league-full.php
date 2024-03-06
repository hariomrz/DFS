<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
        <p style="color:#3FAFEF;font-size:18px;font-family:Calibri;margin:0px;padding:0;font-weight:bold;line-height:50px;">Dear <?php echo $user_name; ?>,</p>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">

        The contest <b><?php echo $content['contest_name']; ?></b> with <b>
            <?php if($content['entry_fee'] > 0){
                ?>

                <?php echo CURRENCY_CODE_HTML.' '; ?><?php  echo $content['entry_fee']; ?>
            <?php }
            else{ echo "Free Entry"; } ?>

           
            </b> has been already full.
            <?php 
        if($content['entry_fee'] > 0)
        {
        ?> and amount of <b><?php echo CURRENCY_CODE_HTML.' '; ?><?php echo $content['entry_fee']; ?></b> has been refunded to your account
        <?php } ?>.

    </td>
</tr>

<?php if($content['entry_fee'] > 0)
{?>

<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        League Name - <b><?php echo $content['contest_name']; ?></b>
    </td>
</tr>

<?php if($content['prize_type'] == 0)
{?>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Bonus Cash Prize  - <b><?php echo CURRENCY_CODE_HTML.' '; ?><?php echo $content['prize_pool']; ?></b>
    </td>
</tr>
<?php } ?>

<?php if($content['prize_type'] == 1)
{?>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Prize Pool  - <b><?php echo CURRENCY_CODE_HTML.' '; ?><?php echo $content['prize_pool']; ?></b>
    </td>
</tr>
<?php } ?>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Size - <b><?php echo $content['size']; ?></b>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Entry Fee - <b><?php echo CURRENCY_CODE_HTML.' '; ?><?php echo $content['entry_fee']; ?></b>
    </td>
</tr>

<?php } ?>
<tr>
    <td style="background-color:#ffffff;"></td>
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