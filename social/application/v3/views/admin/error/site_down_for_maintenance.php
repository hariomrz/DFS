<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Under Maintenance</title>
        <style type="text/css">
            @import url(http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600,700);
            ::selection{ background-color: #E13300; color: white; }
            ::moz-selection{ background-color: #E13300; color: white; }
            ::webkit-selection{ background-color: #E13300; color: white; }

            body {font-size: 14px;line-height: 1.4;font-family: 'Open Sans', sans-serif;background: #FFF;color: #333; margin: 0px; padding: 0px;}
            a {color: #185C8F;text-decoration: none;outline: none;cursor: pointer;}
            a:hover {color: #144D76;}
            .content-wrapper {width: 100%;max-width: 1200px;margin: 0 auto;padding: 90px 0 58px;}
            .clearfix {height: 0;clear: both;}
            
            .error-region {width:100%; margin:10% auto 0;}
            .error-div{width:100%; height:100%; text-align:center;}
            .error-div .txt{font-size:160px; color:#185C8F; text-shadow:2px 2px 0 rgba(0,0,0,.8)}
            .error-div .img404 {display:inline-block;}
            .error-region .msg {font-size:19px; line-height:26px;}
            .error-region .fsize30 {font-size:30px; line-height:35px; margin:5px 0;}
            .error-region .fsize40 {font-size:40px; line-height:50px; margin:5px 0;}
            .icondiv{text-align: center; margin-bottom: 30px;}
            .btndiv{background-color: #185C8F; color: #fff; padding: 8px 15px; margin: 0 auto; border-radius: 3px; text-transform: uppercase; font-size: 20px; margin-top: 25px; width: 200px;}
        </style>
    </head>
    <body>
        <aside class="content-wrapper"> 
            <div class="clearfix"></div>
            <section class="error-region">
                <aside class="error-div"> 
                    <div class="icondiv"><img src="<?php echo ASSET_BASE_URL; ?>/admin/img/triangle_icon.jpg"/></div>
                    <aside class="msg"> 
                        <div class="fsize30">WEB SITE CURRENTLY</div>
                        <div class="fsize40">UNDER MAINTENANCE</div>
                    </aside>
                    <div class="btndiv">Come Back Soon</div>
                </aside>
            </section>
        </aside>
    </body>
</html>