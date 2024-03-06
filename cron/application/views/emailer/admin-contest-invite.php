<!DOCTYPE html>
<html>
<head>
    <title>Reminder Email</title>
    <style>
        *{
            padding: 0;
            margin: 0;
        }
        html,body{
            height: 100%;
            font-family: 'MuliRegular';
        }
        @font-face {
          font-family: 'MuliRegular';
          src:  url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliRegular.eot');
          src:  url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliRegular.eot') format('embedded-opentype'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliRegular.ttf') format('truetype'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliRegular.woff') format('woff'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliRegular.svg#MuliRegular') format('svg');
          font-weight: normal;
          font-style: normal;
        } 
        @font-face {
          font-family: 'MuliBold';
          src:  url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBold.eot');
          src:  url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBold.eot') format('embedded-opentype'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBold.ttf') format('truetype'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBold.woff') format('woff'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBold.svg#MuliBold') format('svg');
          font-weight: normal;
          font-style: normal;
        }     
        @font-face {
          font-family: 'MuliBlack';
          src:  url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBlack.eot');
          src:  url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBlack.eot') format('embedded-opentype'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBlack.ttf') format('truetype'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBlack.woff') format('woff'),
            url('<?php echo BASE_APP_PATH; ?>cron/assets/fonts/MuliBlack.svg#MuliBlack') format('svg');
          font-weight: normal;
          font-style: normal;
        }     
    </style>
</head>
<body>

    <table style="width: 100%; height: 100%;">
        <tbody>
            <tr>
                <td style="background-color: #e4e5e7;">
                   <table style="width: 580px;margin: 0 auto;">
                       <tbody>
                            <tr>
                                <td style="height: 46px;"></td>
                            </tr>
                            <tr>
                                <td style="background: #ffffff url('<?php echo BASE_APP_PATH; ?>cron/assets/img/collection-invite-bg.png');background-repeat: no-repeat;background-size: 100%;height: 885px;width: 100%;">
                                    <table style="width: 100%;">
                                        <tbody>
                                            <tr>
                                                <td style="padding-top: 40px;padding-bottom: 20px;">
                                                    <table style="width: 100%;">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding-left: 40px;">
                                                                    <a href="<?php echo BASE_APP_PATH; ?>"><img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/fslogo.png" border="0"></a>
                                                                </td> 
                                                                <td style="width: 85px;padding-right: 40px;">
                                                                    <table style="height: 35px;width: 100%;">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <a target="_blank" href="<?php echo FB_LINK;?>" style="text-align: center;width: 30px;display: inline-block;">
                                                                                        <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/fbwhite.png"  border="0">
                                                                                    </a>
                                                                                </td>
                                                                                <td style="width: 14px;"></td>
                                                                                <td>
                                                                                    <a target="_blank" href="<?php echo TWITTER_LINK?>" style="text-align: center;width: 30px;display: inline-block;">
                                                                                        <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/twwhite2.png" border="0">
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;">
                                                   
                                                    <p>
                                                        <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/win.png">
                                                    </p>
                                                    <p style="color: #1CDB91;font-family: MuliBlack; font-size: 60px;line-height: 69px;">
                                                    <?php echo CURRENCY_CODE_HTML.$content['prize_pool'];?>
                                                    </p>
                                                    
                                                    <p style="margin-top: 10px;">
                                                        <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/Prize.png">
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height: 20px;"></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table style="width: 100%;">
                                                        <tbody>
                                                            <tr>
                                                                <td style="width: 165px;"></td>
                                                                <td style="width: 250px;height: 250px;background:url('<?php echo BASE_APP_PATH; ?>cron/assets/img/Oval shape.png');background-repeat: no-repeat;background-size: 100%;text-align: center;vertical-align: middle;">

                                                                     <!-- For League Abbr -->    
                                                                    <?php if(!empty($content['league_abbr'])) { ?>
                                                                    <p style="color: #898989;font-family: MuliRegular;font-size: 12px;font-weight: bold;line-height: 15px;border-radius: 9px;background-color: #F0F0F0;width: 40px;margin: 0 auto;"><?php echo $content['league_abbr'];?></p>
                                                                    <?php } ?>

                                                                    <!-- For Contest Name -->
                                                                    <?php if(!empty($content['contest_name'])) { ?>
                                                                    <p style="color: #262626; font-family: MuliRegular;font-size: 12px;font-weight: bold;  line-height: 15px;margin-top: 8px;"><?php echo $content['contest_name'];?></p>
                                                                    <?php } ?>

                                                                    <!-- For First Match's Home/Away Team -->
                                                                    <?php if(!empty($content['home']) && !empty($content['away'])) { ?>
                                                                    <p style="color: #39393A;font-family: MuliBlack;font-size: 24px;line-height: 28px;margin-top: 8px;text-transform: uppercase;"><?php echo $content['home']; ?></p>
                                                                    <p>
                                                                        <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/vs.png">
                                                                    </p>
                                                                    <p style="color: #39393A;font-family: MuliBlack;font-size: 24px;line-height: 24px;text-transform: uppercase;"><?php echo $content['away']; ?></p>
                                                                    <?php } ?>
                                                                    <!-- For More Matches Info -->
                                                                    <?php if(!empty($content['season_game_count']) && $content['season_game_count'] > 1){ ?>
                                                                    <p style="color: #898989;font-family: MuliRegular;font-size: 10px;font-style: italic; line-height: 13px;margin-top: 2px;">+<?php echo ($content['season_game_count'])-1; ?> more matches</p>
                                                                    <?php } ?>
                                                                    <!-- For Season Scheduled Date -->
                                                                    <?php if(!empty($content['season_scheduled_date'])) { ?>
                                                                    <p style="color: #262626; font-family: MuliRegular;font-size: 12px;font-weight: bold;  line-height: 15px;margin-top: 13px;"><?php echo $content['season_scheduled_date'];?></p>
                                                                     <?php } ?>
                                                                </td>
                                                                <td style="width: 165px;"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 0 83px 0 48px;">
                                                    <p style="font-size: 28px;line-height: 42px;color: #898989;margin-bottom: 18px;">Hi <?php 
                                                         $new_user_name = explode('@', $email);
                                                         $new_user_name = $new_user_name[0];
                                                         echo $new_user_name;?>,
                                                    </p>
                                                    <p style="margin-bottom: 24px;font-size: 28px;line-height: 42px;color: #898989;">Your friend has invited you to play on <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php echo SITE_TITLE;?></sapn></font>. Join the contest by clicking on the link <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php echo $content['link']?></sapn></font> or else use the League Code <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php echo $content['league_code']?></sapn></font> to join!</p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="padding: 0 83px 0 48px;">
                                                    <p style="margin-bottom: 24px;font-size:28px;line-height: 42px;color: #898989;">Here are further details of the Contest! 
                                                    </p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="padding: 0 83px 0 48px;">
                                                    <p style="margin-bottom: 24px;font-size:28px;line-height: 42px;color: #898989;">League Name : <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php echo $content['contest_name'];?></font></sapn><br>
                                                        <?php if($content['prize_pool'] > 0){?>

                                                        <font color="#898989">Prize Pool : </font>
                                                        <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php 
                                                        if(($content['prize_type'] == 0 || $content['prize_type'] == 1))
                                                            {echo CURRENCY_CODE_HTML.$content['prize_pool'];}
                                                        if($content['prize_type'] == 2)
                                                            {echo $content['prize_pool']." coins";}
                                                        ?></font></sapn><br>
                                                        <?php }?>
                                                        Size : <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php echo $content['size'];?></font></sapn><br>
                                                        <?php /* ?>
                                                        <font color="#898989">Prize Pool: </font>
                                                        <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php 
                                                        if($content['prize_type'] == 0 || $content['prize_type'] == 1)
                                                            {echo CURRENCY_CODE_HTML.$content['prize_pool'];}
                                                        if($content['prize_type'] == 2)
                                                            {echo $content['prize_pool'];}

                                                        ?></font></sapn><br>
                                                        <?php */?>
                                                        <font color="#898989">Entry Fee : </font>
                                                        <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php 
                                                        if(($content['prize_type'] == 0 || $content['prize_type'] == 1) && $content['entry_fee']> 0)
                                                            {echo CURRENCY_CODE_HTML.$content['entry_fee'];}
                                                        else if(($content['prize_type'] == 0 || $content['prize_type'] == 1) && $content['entry_fee'] == 0)
                                                        {
                                                            echo "FREE";
                                                        }
                                                        if($content['prize_type'] == 2)
                                                            {echo $content['entry_fee']." coins";}
                                                        ?></font></sapn><br>
                                                       <!--  <font color="#898989">Prize Type: </font>
                                                        <sapn style="color: #00DFF1;"><font color="#00DFF1"><?php 
                                                        if ($content['prize_type'] == 0)
                                                            {echo "Bonus Cash";}
                                                        if($content['prize_type'] == 1)
                                                            {echo "Real Cash";}
                                                        if($content['prize_type'] == 2)
                                                            {echo "Coins";}
                                                        ?></font></sapn><br> -->

                                                    </p>
                                                </td>
                                            </tr>
                                    <!--         <tr>
                                                <td style="padding: 0 83px 0 48px;">
                                                    <p style="margin-bottom: 24px;font-size:28px;line-height: 42px;color: #898989;">Fantasy Sports team wishes you luck.
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;">
                                                    <a href="<?php echo $content['link'];?>" style="font-size: 16px; font-weight: bold;  line-height: 38px;  text-align: center;width: 480px;height: 38px;background-color: #00DFF1;color: #fff;border-radius: 3px;border:0;display: inline-block;text-decoration: none;box-shadow: 0 2px 4px 0 rgba(0,0,0,0.2);font-family: MuliRegular;">
                                                        PLAY NOW
                                                    </a>
                                                </td>
                                            </tr> -->
                                            <tr>
                                                <td style="padding: 0 83px 0 48px;">
                                                    <p style="margin-bottom: 24px;font-size: 24px;line-height: 30px;color: #898989;">Note : P.S. League codes are specific to cricket and football. Please ensure you have the right sport selected before entering the code.</p>
                                                </td>
                                            </tr>
                                            <tr>
                                            <td style="padding: 0 83px 0 48px;font-size: 24px;line-height: 30px;color: #898989;">
                                                Cheers,<br>
                                                <?php echo SITE_TITLE; ?> Team
                                            </td>
                                        </tr>
                                            <tr>
                                                <td style="padding: 0 83px 0 48px;">
                                            </tr>
                                            <tr>
                                                <td style="padding-bottom: 28px;padding-top: 50px;text-align: center;color: #898989;font-family: MuliRegular;font-size: 12px;line-height: 15px;">
                                                    &copy; <?php echo date("Y"); ?> Fantasy Sports. All rights reserved
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="height: 46px;"></td>
                            </tr>
                       </tbody>
                   </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>