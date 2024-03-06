<?php echo $this->load->view("emailer/header",array(),TRUE); ?>

<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <span><?php echo $content['name']; ?>,</span>
        </h4>
        <p>Congratulations, Your password is changed successfully. below is the details.</p>
             
        <p>Login URL    : <span><a href="<?php echo $content['url']; ?>"><?php echo $content['url']; ?></a></span> </p>
        <p>Email        : <span><?php echo $content['email']; ?></span> </p>
        <p>Password     : <span><?php echo $content['pwd']; ?></span> </p>
        
        <br><br>
        <p>Thank You !</p>
        <p><span><?php echo $content['site_title']; ?></span> Team</p>
        
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>