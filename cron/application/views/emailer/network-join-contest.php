<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<?php
$converted_date = get_timezone(strtotime($content['season_scheduled_date']),'d M Y',$this->app_config['timezone']);
$contest_date = $converted_date['date'];
$timezone =  $converted_date['tz'];
?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hey <?php echo $user_name; ?>,
        </h4>
        <?php if($content['entry_fee'] > 0){ ?>
            <p>
                You've successfully joined the contest with an entry fee of <span><?php echo CURRENCY_CODE_HTML.$content['entry_fee']; ?></span> & prize pool of 
                <?php if($content['prize_pool'] > 0){ ?>
                    <span>
                        <?php 
                        if($content['prize_type'] == "0"){
                            echo $content['prize_pool']." Bonus cash";
                        }else if($content['prize_type'] == "1"){
                            echo CURRENCY_CODE_HTML.$content['prize_pool'];
                        }else if($content['prize_type'] == "2"){
                            echo $content['prize_pool']." Coin";
                        }
                        ?>
                    </span>
                <?php }else{ ?>
                    <span>Free</span>
                <?php } ?>
                for the 
                <span><?php echo (!empty($content['collection_name'])) ?  $content['collection_name'] : $content['contest_name']; ?></span> game on <span><?php echo $contest_date.' ('.$timezone.') '; ?></span>
            </p>
        <?php }else{ ?>
            <p>
                You've successfully joined the free contest  with a prize pool of 
                <?php if($content['prize_pool'] > 0){ ?>
                    <span>
                        <?php 
                        if($content['prize_type'] == "0"){
                            echo $content['prize_pool']." Bonus cash";
                        }else if($content['prize_type'] == "1"){
                            echo CURRENCY_CODE_HTML.$content['prize_pool'];
                        }else if($content['prize_type'] == "2"){
                            echo $content['prize_pool']." Coin";
                        }
                        ?>
                    </span>
                <?php }else{ ?>
                    <span>Free</span>
                <?php } ?>
                for the<br> 
                <span><?php echo (!empty($content['collection_name'])) ?  $content['collection_name'] : $content['contest_name']; ?></span> match on <span><?php echo $contest_date.' ('.$timezone.') '; ?></span>
            </p>
        <?php } ?>
        <p>Sit back, relax & watch your All-Star Team in action!</p>
        <div class="booking-btn-wrapper text-left">
            <a href="<?php echo WEBSITE_URL; ?>my-contests-nf" class="booking-btn">Check My Rank</a> 
        </div>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>