
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Fantasy Sports</title>
    <style type="text/css">
        html,body{
            margin: 0;
            padding: 0;
            border: 0;
            outline: none;
        }
        iframe{
            overflow: hidden;
            border: 0;
            outline: none;
        }
        .banner-container-sm{
            width: 300px;
            height: 300px;
            background: url('<?php echo BASE_APP_PATH; ?>user/assets/img/bw-background-sm.png');
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 4px;
        }
        .text-wrapper{
            padding: 10px;
        }
        .yellow-text{
            color: #FFE600;
            font-size: 60px; 
            font-weight: bold;  
            line-height: 72px;
        }
        .white-text{
            color: #FFFFFF;
            font-size: 30px; 
            line-height: 37px;
        }
        button{
            background-color: #FFE600;
            color: #000;
            font-weight: bold;
            height: 50px;
            width: 110px;
            font-size: 14px;
            line-height: 24px;
            border: none;
            border-radius: 4px;
        }
        .small-div{
            width: 120px;
            display: inline-block;
            vertical-align: middle;
        }
        .small-div img{
            width: 150px;
            height: 80px;
        }
        .banner-container-md{
            width: 370px;
            height: 240px;
            background: url('<?php echo BASE_APP_PATH; ?>user/assets/img/bw-background-md.png');
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 4px;
        }
        .banner-container-md .yellow-text{
            line-height: 60px;
        }
        .banner-container-md .white-text{
            line-height: 30px;
        }
        .banner-container-lg{
            width: 728px;
            height: 90px;
            background: url('<?php echo BASE_APP_PATH; ?>user/assets/img/bw-background-lg.png');
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 4px;
        }
        span{
            vertical-align: middle;
        }
        .banner-container-lg .yellow-text{
            font-size: 48px;
        }
        .banner-container-lg .white-text{
            font-size: 26px;
            line-height: 30px;
        }
        .banner-container-lg .text-wrapper{
            padding: 0;
        }
    </style>

</head>
<body>
<?php if($this->data["banner_size"]=="300x300") {?>
    <div class="banner-container-sm">
        <div class="text-wrapper">
            <div class="white-text">Refer friends and get </div>
            <div class="yellow-text">$10</div>
            <div class="white-text">as bonus cash</div>
            <div class="small-div">
                <button>REFER NOW</button>
            </div>
            <div class="small-div">
                <img src="<?php echo BASE_APP_PATH; ?>user/assets/img/cartoon.png">
            </div>
        </div>
    </div>
<?php }elseif($this->data["banner_size"]=="370x240") {?>
    <div class="banner-container-md">
        <div class="text-wrapper">
            <div class="white-text">Refer friends and get </div>
            <div class="yellow-text">$10</div>
            <div class="white-text">as bonus cash</div>
            <div class="small-div">
                <button>REFER NOW</button>
            </div>
            <div class="small-div">
                <img src="<?php echo BASE_APP_PATH; ?>user/assets/img/cartoon.png">
            </div>
        </div>
    </div>
<?php }elseif($this->data["banner_size"]=="728x90") {?>
    <div class="banner-container-lg">
        <div class="text-wrapper">
            <div>
                <span class="white-text">Refer friends and get </span>
                <span class="yellow-text">$10</span>
                <span class="white-text">as bonus cash</span>
                <span class="small-div">
                    <button>REFER NOW</button>
                </span>
                <span class="small-div">
                    <img src="<?php echo BASE_APP_PATH; ?>user/assets/img/cartoon.png">
                </span>
            </div>
        </div>
    </div>
 <?php }?>                   
</body>
</html>