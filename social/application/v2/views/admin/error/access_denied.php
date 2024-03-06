<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Access Denied</title>
        <style type="text/css">
            @import url(http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600,700);
            ::selection{ background-color: #E13300; color: white; }
            ::moz-selection{ background-color: #E13300; color: white; }
            ::webkit-selection{ background-color: #E13300; color: white; }

            body {font-size: 14px;line-height: 1.4;font-family: 'Open Sans', sans-serif;background: #FFF;color: #333; margin: 0px; padding: 0px;}
            .content-wrapper {width: 100%;max-width: 1200px;margin: 0 auto;padding: 90px 0 58px;}
            .clearfix {height: 0;clear: both;}
            
            .error-region {width:100%; margin:10% auto 0;}
            .error-region .msg {font-size:19px; line-height:26px;}
            .error-div{width:100%; height:100%; text-align:center; color: #ff0000;}
            .error-region .fsize20 {font-size:20px; line-height:35px; margin:5px 0;}
            .error-region .fsize40 {font-size:40px; line-height:50px; margin:5px 0;}
            .icondiv{text-align: center; margin-bottom: 10px;}
        </style>
    </head>
    <body>
        <aside class="content-wrapper"> 
            <?php echo accessDeniedHtml(); ?>
        </aside>
    </body>
</html>