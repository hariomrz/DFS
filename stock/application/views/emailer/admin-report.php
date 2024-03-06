<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Dear Admin,
        </h4>
        <p>
            Please click on the link below to download <span><?php echo $title; ?></span>
        
        <p></p>
        <p>
           <a href="<?php echo $link; ?>" target="_blank">Download Report</a>
        </p>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>