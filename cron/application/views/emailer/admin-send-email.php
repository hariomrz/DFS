<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hello,
        </h4>
        <p>
            <?php echo $content['message']; ?>
        </p>
    </td>
</tr>
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>