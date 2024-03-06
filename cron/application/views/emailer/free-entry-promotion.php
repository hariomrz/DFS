<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 0 30px;background-color:#ffffff;">
        <p style="color:#3FAFEF;font-size:18px;font-family: 'MuliBold';margin:0px;padding:0;font-weight:bold;line-height:50px;">Dear <?php echo $user_name; ?>,</p>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliBold';font-size:14px;">
        <h3>Amazing News!!</h3>
    </td>
</tr>
<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
       You have been granted a free entry for upcoming
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
        <?php echo $content['contest_name']; ?><br>
    </td>
</tr>
<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
        <?php echo (!empty($content['formated_contest_date'])) ? $content['formated_contest_date'] : $content['season_scheduled_date'];  ?>
    </td>
</tr>

<tr>
    <td style="background-color:#ffffff;"></td>
</tr>
<?php if(!empty($content['lineup_link'])) { ?>
    <tr>
        <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px; text-align: center;">
            <a href="<?php echo $content['lineup_link']; ?>">Setup Lineup Now</a>
        </td>
    </tr>
    <tr>
        <td style="background-color:#ffffff;"></td>
    </tr>
<?php } ?>




<tr>
    <td style="padding:0 30px 10px 30px;background-color:#ffffff;font-family: 'MuliRegular';font-size:14px;">
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
<?php $this->load->view("emailer/footer"); ?>