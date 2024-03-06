<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
date_default_timezone_set('Asia/Kolkata');
$content=json_decode($content,true);
$contest_date = date("d M, Y",strtotime($content['scheduled_date']));


if(isset($content['currency_type']) && $content['currency_type'] == "0"){
    $currency_type = "B";
}else if(isset($content['currency_type']) && $content['currency_type'] == "2"){
    $currency_type = "C";
}else{
    $currency_type = CURRENCY_CODE_HTML;
}

?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Dear <?php echo $user_name; ?>,
        </h4>
        <p>You've successfully made a <span><?php echo $content['team_name']; ?></span> with the probable winning of <span><?php echo $currency_type.$content['entry_fee']; ?></span>.</span> 
           <span>Entry name- <?php echo $content['team_name']; ?></span> 
           <span>Stake- <?php echo $content['entry_fee'] ?></span> 
            <span>Entry Start Time- <?php echo date('d M Y H:i',strtotime($content['scheduled_date'])); ?> </span>.</p>
        <p>Sit back, relax & watch your players in action!</p>
        <p>Note: You can edit your upcoming entries before their deadline!</p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>my-contests" class="booking-btn">Make more entries </a> <!--  on <span><?php echo $contest_date; ?> (IST) -->
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>
