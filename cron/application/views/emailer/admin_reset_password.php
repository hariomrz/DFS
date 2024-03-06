<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $username; ?>,
        </h4>
        <p>&nbsp;</p>
        <p>Here's the new login detail for your account click to <a href="<?php echo WEBSITE_URL; ?>/admin">Login</a> .</p>
        <p>
            Email: <b><?php echo $content['email']; ?></b>
        </p>
        <p>
            Password: <b><?php echo $content['password']; ?></b>
        </p>
    </td>
</tr>

<tr>
    <td colspan="3" style="padding:0 30px 10px 30px;background-color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:14px;">
        Cheers,<br>
        <?php echo SITE_TITLE; ?> Team
    </td>
</tr>
<tr>
    <td colspan="3" style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td  colspan="3" style="background-color:#ffffff;"></td>
</tr>
<tr>
    <td colspan="3"></td>
</tr>



<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
