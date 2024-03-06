<!doctype html>
<html>
    <head>
        <!-- <meta charset="utf-8"> -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo SITE_NAME; ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css?family=Roboto:400,500,700');
            p {margin: 0}
            img {border:0; outline:none;}
            a {outline: none; cursor:pointer; text-decoration:none; }  
            @media only screen and (max-width:767px) {
                table[class="main-wrapper"] {width: 94%!important}
                td[class="mob-gutter"] {padding-left: 10px!important; padding-right: 10px!important;}
                td[class="mob-fluid"] {display:block !important; width:100% !important; padding-left:0px!important; padding-right:0px!important;}
                img[class="email-img"] {height:auto !important; max-width:610px !important; width: 100% !important;}      
                td[class="content-padding"] {padding:20px !important;}
                td[class="small"] {width:30% !important; display:block !important; padding:10px !important;}
                img[class="get-startedbtn"]{width:100% !important;}
                td[class="mob-content"] {display:block !important; width:100% !important; padding:0 0 10px 10px !important;}
                td[class="mob-padding"] {padding:10px !important;}
                td[class="add-frnds"] {padding:10px 0 !important;}
                td[class="mob-paddinglr"] {padding:0 10px !important;}
              }
        </style>
    </head>
    <body style="background:#EEEEF0; font-family: 'Roboto', sans-serif; margin:0; padding:0;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:0; padding:0 0 20px 0; font-size:16px; color:#444; font-family: 'Roboto', sans-serif; font-weight: normal;font-style: normal; background:#EEEEF0;">
            <tbody>
                <!--Header-->
                <?php $this->load->view("emailer/header"); ?>
                <!-- //Header -->

                <!--Content-->
                <?php
                if (isset($content_view)) {
                    $this->load->view("$content_view");
                }
                ?>
                <!--//Content-->

                <!--Footer-->
                <?php $this->load->view("emailer/footer"); ?>
                <!-- //Footer -->
            </tbody>
        </table>
    </body>
</html>
