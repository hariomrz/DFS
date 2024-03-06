<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">  
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-15">
    <title><?php echo isset($this->app_config['site_title']) ? $this->app_config['site_title']['key_value'] : 'Fantasy Sports'; ?></title>
    
    <style>
      body{font-family:Arial, sans-serif;font-size:14px;background-color: #f2f2f2}
      .main-table{width: 600px;background-color: #ffffff;margin: 0 auto;border-bottom: 5px solid #0CBFEB!important;border: 1px solid #e8e8e8;}

      td{
        border:none;
      }
      tr.logo-tr {height: 44px;}
      tr.logo-tr a{color: #000000;font-size: 13px;  line-height: 12px;text-decoration: none;}
      /*.logo-td{width: 25%;padding: 0 30px;border:1px solid  #0CBFEB !important;border-right:none !important}
      .link-td{width: 75%;padding: 0 30px;text-align: right;border:1px solid  #0CBFEB !important;  border-left:none !important}*/
      .logo-td{width: 25%;padding: 0 30px;border-bottom:1px solid  #e8e8e8 !important;}
      .link-td{width: 75%;padding: 0 30px;text-align: right;border-bottom: 1px solid  #e8e8e8 !important;}
      .info-td{padding: 20px 30px;}
      .info-td h4{color: #212121;font-size: 14px ;margin-bottom: 10px;font-weight: bold; line-height: 14px;}
      .info-td p{color: #555555; font-size: 14px ;margin-top: 0;line-height: 19px;}
      .info-td p img{width: 16px;vertical-align: middle;}
      .info-td p span{font-weight: bold;color: #000;}
      .info-td h5{color: #000000;font-size: 14px;font-weight: bold;line-height: 24px;  margin: 5px;vertical-align: middle;}
      .info-td h5 span{color: #5DBE7D;}
      .middle-table{width: 90%;margin: 0 auto;}
      .middle-table h5{ font-size: 15px;}
      .instruction-td{text-align: center;width: 140px; border-radius: 6px;margin: 0 auto;vertical-align: top;}
      .select-match{width: 140px;margin: 0 auto;}
      .create-team{width: 175px;margin: 0 auto;}
      .svg-icon{background-color: #ffff;width: 65px;height: 42px;margin: 0 auto 15px;}
      .instruction-td h6{color: #212120; font-size: 14px ; font-weight: bold;  line-height: 14px;  margin: 0;text-align: center;}
      .instruction-td p{color: #555555; font-size: 13px;  line-height: 14px;  text-align: center;}
      .booking-btn-wrapper {text-align: center;margin: 20px 0 30px;}
      .booking-btn-wrapper ul{list-style-type: none;padding: 45px 0 35px;margin: 0;}
      .booking-btn-wrapper ul li{display: inline-block; }
      a.booking-btn{padding: 8px 25px;background-color: #0CBFEB;color: #FFFFFF;font-size: 12px;font-weight: bold;line-height: 21px;text-decoration: none;text-transform: uppercase;}
      a.invite-btn{border: 1px solid #0CBFEB;color: #0CBFEB;font-size: 13px;  text-decoration: none;padding: 7px 25px;font-weight: bold;  line-height: 21px;text-transform: uppercase;}
      .text-center{text-align: center;}
      .text-left{text-align: left;}
      .footer-tr{background-color: #F2F2F2;}
      .bootom{padding: 25px 15px;}
      .bootom-nav{padding-left: 0;list-style-type: none;margin: 0;}
      .bootom-nav li{display: inline-block;padding: 8px 1px; color: #555555;}
      .bootom-nav li a{font-size: 12px;line-height: 19px;color: #555555;text-decoration: none;}
      .copyright-p{color: #555555; font-size: 13px;line-height: 19px;}
      .copyright-p span{font-size: 13px;font-weight: 600;}
      .copyright-p img{vertical-align: middle;margin-left: 7px;}
      .invite-p{color: #555555; font-size: 11px;  line-height: 19px;}
      .fixture {text-align: center;}
      .teams-table{margin:  0px auto;}
      .teams-table p{margin: 0;}
      .team-name{vertical-align: bottom;}
      .hyd-logo {height: 35px;width: 35px;margin-left: 15px;}
      .kol-logo {height: 35px;width: 35px;margin-right: 15px;}
      .text1 {color: black;font-weight: bold;font-size: 14px;font-weight: 800;line-height: 45px;text-align: center;}
      .text2 {color: black;font-size: 14px;font-weight: 800;line-height: 45px;text-align: center;}
      .text4 {text-align:center;color: #555555;font-size: 10px;  line-height: 21px;}
      .winning-table{width: 400px; border: 1px solid #EAEAEA;  background-color: #FFFFFF;margin: 20px auto 0;}
      .summary-table{width: 400px; margin: 12px auto;}
      .summary-table td{border-bottom: 1px solid #EAEAEA;}
      .summary-table tr:last-child td{border-bottom: none;}
      .summary-table h5{margin: 0;}
      .final-rank{height: 44px;background-color: rgba(194,227,238,0.4);}
      .result-head{color: #555555;font-size: 13px; font-weight:bold;line-height: 21px;margin: 0; }
      .result-score{color: #555555; font-size: 13px;font-weight: bold; line-height: 21px;margin: 0;}
      .text-right{text-align: right;}
      .padding-5{padding:  5px 10px ;}
      .green-color{color: #5DBE7D;}
      .taxes-para{font-size: 10px;font-style: italic;color: #555555;margin: 0;}
      .info-td_won{ padding: 30px 0 10px; }
      .info-td_won h4{color: #212121;font-size: 14px;line-height: 16px;margin: 0;}
      .info-td_won p{color: #555555; font-size: 11px;  line-height: 19px; margin: 0;
      }
      .winning-font .info-td h4{font-size: 16px;}
      .winning-font .info-td h5{font-size: 16px;}
      .winning-font .info-td p{font-size: 16px;}
      .winning-font .text1 ,.text2{ font-size: 15px;}
      .winning-font .text4{font-size: 12px;}
      .winning-font .result-head{font-size: 12px;color: #555555;font-weight: 400;}
      .winning-font .taxes-para{font-size: 11px;color: #999999;}
      .winning-font .result-score{font-size: 15px;}
      .winning-font .summary-table h5{font-size: 15px;}
        @media (max-width: 767px) {
        .main-table{  width: 100%;}
        .banner{   height: 250px;}
        .banner img{   max-width: 100%;}
        .calender-wapper{  width: 205px;  margin-bottom: 30px;}
        .calender-wapper-td{width:100%;float: left;}
        .booking-btn-wrapper{margin: 25px auto 20px;}
        .booking-btn-wrapper ul li{margin-bottom: 30px;}
        .booking-btn-wrapper ul{padding: 0;}
        .instruction-td{width: 100%;float: left;margin: 0 auto 15px;}
      }
     @media (max-width: 450px) {
        a.booking-btn{padding: 12px 10px;}
     }
     @media (max-width: 320px) {
        a.booking-btn{padding: 12px 5px;}
     }
    </style>
  </head>
  <body  bgcolor="#ECEBEB" style="font-family:Arial, sans-serif">
    <table border="" cellpadding="0" cellspacing="0" class="main-table">
        <!-- Start header section-->
        <tr class="logo-tr">
            <td colspan="2" class="logo-td">
                <a href="<?php echo WEBSITE_URL; ?>"><img src="<?php echo WEBSITE_URL; ?>cron/assets/img/logo.png"></a>
            </td>
            <td colspan="1" class="link-td">
                <a href="<?php echo WEBSITE_URL; ?>"><?php echo WEBSITE_DOMAIN; ?></a>
            </td>
        </tr>