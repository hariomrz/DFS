<style>
  p {margin: 0}
  @media only screen and (max-width:767px) {
    table[class="main-wrapper"] {width: 94%!important}
    td[class="content-padding"] {padding: 20px!important}
    td[class="small"] {width: 30%!important; display: block!important; padding: 10px!important}
    td[class="mob-content"] {display: block!important; width: 100%!important; padding: 0 0 10px 10px !important}
    td[class="mob-padding"] {padding: 10px!important}
    a {outline: none}
    img {border: 0;outline: none}
  }
  </style>

<tr>
    <td colspan="2" class="content-padding" style="padding:40px 40px 20px; border-bottom:1px solid #E5E5E5;background:#FFFFFF;">
        <p style="font-family: 'Arial', sans-serif; font-size:16px; color:#444; font-weight:600; margin-bottom:20px;">
            Hey 

            <?php
            if (!empty($data['user']['FirstName']) && !empty($data['user']['LastName'])) {
                echo $data['user']['FirstName'] . ' ' . $data['user']['LastName'];
            } else {
                echo 'there';
            }
            ?>,
        </p>
        <p style="font-family: 'Arial', sans-serif; font-size:14px; color:#666; font-weight:400; margin-bottom:20px;">
            We aggregated some articles JUST FOR YOU, that we know you’l love!
        </p>        

        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #E5E5E5; border-radius:4px;">
            <tbody>
                <tr>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-bottom:1px solid #E5E5E5;">
                            <tbody><tr>
                                    <td style="padding:10px 20px; width:100%;" class="mob-padding">
                                        <p style="font-size:16px; color:#444; font-weight:600; display:block; margin:0; line-height:24px;">Check them out!</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>


                        <?php $this->load->view('emailer/n_feed_list') ?>



                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody><tr>
                                    <td style="width:100%; font-size:14px; color:#35217A; font-weight:600; text-align:center; margin:0; padding:10px 0;">
                                        <a style="color:#00529F;cursor:pointer; display:block" href="<?php echo site_url() ?>">Read More</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>




        <?php $this->load->view('emailer/notification_settings') ?>



    </td>
</tr>