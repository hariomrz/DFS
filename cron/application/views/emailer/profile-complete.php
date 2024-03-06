

<!DOCTYPE html>
<html>
<head>
    <title>Emailer</title>
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
    </style>
</head>
<body>

    <table style="width: 100%; height: 100%;">
        <tbody>
            <tr>
                <td style="background: url('<?php echo BASE_APP_PATH; ?>cron/assets/img/step-7-img.png');background-repeat: no-repeat;background-size: cover;">
                   <table style="width: 580px;margin: 0 auto;">
                       <tbody>
                           <tr>
                                <td style="height: 110px;">
                                    <table style="width: 100%;height: 100%;">
                                        <tbody>
                                            <tr style="height: 40px;"></tr>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo BASE_APP_PATH; ?>"><img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/fslogo.png"></a>
                                                </td>
                                                <!-- <td style="text-align: right;">
                                                    <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/fb-ic.svg">
                                                    <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/tw-ic.svg">
                                                </td> -->
                                                <td style="width: 85px;">
                                                    <table style="height: 35px;width: 100%;">
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <a href="#" style="text-align: center;width: 30px;display: inline-block;">
                                                                        <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/fbwhite.png">  
                                                                    </a>
                                                                </td>
                                                                <td style="width: 14px;"></td>
                                                                <td>
                                                                    <a href="#" style="text-align: center;width: 30px;display: inline-block;">
                                                                        <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/twwhite2.png">
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr style="height: 25px;"></tr>
                                        </tbody>
                                    </table>
                                </td>
                           </tr>
                           <tr>
                               <td style="background:#ffffff url('<?php echo BASE_APP_PATH; ?>cron/assets/img/43870-O3VFUAimg.png');background-repeat: no-repeat;background-size: cover;">
                                   <table style="width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 100px;"></td>
                                            <td>
                                                <table style="width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="height: 200px;text-align: center;vertical-align: bottom;">
                                                                <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/congratulation.png">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="text-align: center;height: 150px;">
                                                                <p style="font-size: 40px;font-family: MuliRegular;color: #1CD9ED;">
                                                                   <?php echo $content["user_name"];?>,
                                                                </p>
                                                                <p style="font-size: 24px;font-family: MuliRegular;color: #39393A;">
                                                                    Your friend <?php echo $content["friend_name"];?> has verified his <?php echo $content["profile_type"];?>.
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td  style="height: 50px;vertical-align: middle;" align="center">
                                                                <img src="<?php echo BASE_APP_PATH; ?>cron/assets/img/Separater.png">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="height: 60px;">
                                                                <p style="color: #39393A; font-family: MuliRegular; font-size: 24px; font-weight: 300; line-height: 30px; text-align: center;">You get</p>
                                                                <p style="font-size: 40px;font-family: MuliRegular;color: #1CD9ED;text-align: center;">
                                                                   <?php echo $content["amount"];?> bonus cash
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="height: 70px;vertical-align: middle;" align="center">
                                                                <a href="<?php echo $content['link'];?>" style="font-size: 16px; font-weight: bold;  line-height: 38px;  text-align: center;width: 320px;height: 38px;background-color: #1CD9ED;color: #fff;border-radius: 3px;border:0;display: inline-block;text-decoration: none;box-shadow: 0 2px 4px 0 rgba(0,0,0,0.2);">View Referrals & Status</a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="height: 170px;"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td style="width: 100px;"></td>
                                        </tr>
                                    </tbody>
                                       <!--  -->
                                   </table>
                               </td>
                           </tr>
                           <tr>
                               <td style="height: 60px;">
                                   <table style="width: 100%;">
                                       <tbody>
                                           <tr>
                                               <td style="height: 20px;text-align: center; color: #FFFFFF; font-family: MuliRegular;font-size: 12px;line-height: 15px;">
                                                   © 2017 Fantasy Sports. All rights reserved
                                               </td>
                                           </tr>
                                           <tr>
                                               <td style="height: 40px;"></td>
                                           </tr>
                                       </tbody>
                                   </table>
                               </td>
                           </tr>
                       </tbody>
                   </table>
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>
