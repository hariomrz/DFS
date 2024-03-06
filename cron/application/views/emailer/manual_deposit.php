<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey Admin,
        </h4>
        <p>
        There is a credit to your company wallet/account from <?php echo SITE_TITLE; ?>.<br><br>
            Username : <b><?php echo $content['user_name']; ?></b><br>
            Registered Email : <b><?php echo $content['email']; ?></b><br>
            Amount : <b><?php echo $content['amount']; ?></b><br><br>
            Transaction Link/ID: <b><?php echo $content['ref_id']; ?></b><br><br>
            Transaction Image: <b><img src="<?php echo IMAGE_PATH.$content['image'];?>"></b><br><br>
        </p>
        <p>
            Thank You ! <br><br>
            <?php echo SITE_TITLE; ?>
        </p>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
