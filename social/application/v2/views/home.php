<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<title>Vcommonsocial</title>
<link href="<?php echo base_url(); ?>assets/css/animate.min.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lander/style.css">
<!--[if lt IE 9]><script src="js/html5.js"></script><![endif]-->

<style>
[class^="icon-"], [class*="icon-"] { background: url({{AssetBaseUrl}}img/home/home-sprite.png) no-repeat; }
</style>
</head>
<body class="no-js">
<section class="main">
  <header>
    <div class="wrap">
      <div class="logo" > <a href="/" class="wow fadeInDown"> <img src="{{AssetBaseUrl}}img/logo.png" width="180" height="36"  /></a> </div>
      <!-- logo --> 
      <!--<div class="social">
        <ul class="clearfix wow fadeInDown">
          <li class="wow fadeIn"><a class="social-facebook" href="#" title="facebook">facebook</a></li>
          <li class="wow fadeIn"><a class="social-twitter" href="#" title="twitter">twitter</a></li>
          <li class="wow fadeIn"><a class="social-googleplus" href="#" title="google plus">google plus</a></li>
        </ul>
      </div>--> 
      <!--social --> 
    </div>
    <!-- wrap --> 
  </header>
  <section class="promo">
    <div class="wrap relative">
      <div class="promo-text wow fadeInLeft">
        <div class="promo-title">Your Supportive Community for <span class="color-orange">Mindfulness</span> Practice</div>
        <p>The first community and social network that helps you train mindfulness online. Train mindfulness and transform daily stressful challenges into wisdom.</p>
        <p><a class="promo-button" href="#subscribeWrap">SUBSCRIBE NOW</a> &nbsp; <a class="promo-link" href="#promo-link-wrap">Learn more</a></p>
      </div>
      <!-- promo-text --> 
      
      <img class="wow fadeInRight" src="<?php echo base_url(); ?>assets/lander/upload/promo.png"  > </div>
    <!-- wrap --> 
  </section>
  <!-- promo -->
  <section class="simple">
    <div class="wrap">
      <h1 id="promo-link-wrap" class="wow fadeInDown">Vcommonsocial is for you. Connect with your well-being.</h1>
      <p class="wow fadeInRight" data-wow-delay="0.6s">Vcommonsocial is mainly for people interested in overcoming stress and feel better. It is also directed at researchers, health and medicine professionals interested in using mindfulness with their patients. It is equally interesting to organizations that wish to reduce the stress levels of their employees.</p>
    </div>
    <!-- wrap --> 
  </section>
  <!-- simple -->
  <section class="features">
    <div class="wrap">
      <div class="features-columns clearfix">
        <div class="feature wow fadeInLeft"> <i class="icon-course"></i>
          <h4  class="wow fadeIn">Course</h4>
          <p>We provide tools for self-evaluation, to get to know your state and monitor your progress. Get to know your level of stress, attention, mindfulness and self-control.</p>
        </div>
        <!-- feature -->
        <div class="feature wow  fadeInUp"> <i class="icon-exercises"></i>
          <h4 class="wow fadeIn">Exercises</h4>
          <p>Train a healthy mind with MindFocus Web developed by our team of experts in mindfulness. Train your attention, awareness and self-regulation of thoughts and emotions.</p>
        </div>
        <!-- feature -->
        <div class="feature wow fadeInRight"> <i class="icon-polls"></i>
          <h4  class="wow fadeIn">Polls</h4>
          <p>By participating you can contribute to scientific knowledge about the benefits of mindfulness on stress reduction and improvement of health and wellbeing.</p>
        </div>
        <!-- feature --> 
      </div>
      <!-- features-columns --> 
    </div>
    <!-- wrap --> 
  </section>
  <!-- features -->
  <section class="tabsblock">
    <div class="wrap">
      <div class="tab">
        <ul class="tabs clearfix">
          <li class="active"></li>
        </ul>
        <!-- tabs -->
        <div class="box visible">
          <div class="box-text">
            <h3 class="wow fadeInRight">Connect here and now with thousands of people who are training mindulness online.</h3>
            <p class="wow fadeInLeft">In Vcommonsocial you can find information on mindfulness and its benefits. As a registered user you can get to know your stress level, train mindfulness, share your experience with your friends and ask experts that can help you with your practice.</p>
          </div>
          <img src="<?php echo base_url(); ?>assets/lander/upload/tabs.png" width="437" height="459"  class="wow bounceIn"> </div>
        <!-- box -->
        <div class="box">
          <div class="box-text">
            <h3 class="wow fadeInRight">Connect here and now with thousands of people who are training mindulness online.</h3>
            <p class="wow fadeInLeft">In Vcommonsocial you can find information on mindfulness and its benefits. As a registered user you can get to know your stress level, train mindfulness, share your experience with your friends and ask experts that can help you with your practice.</p>
          </div>
          <img src="<?php echo base_url(); ?>assets/lander/upload/tabs.png" width="437" height="459"  class="wow fadeInUp"> </div>
        <!-- box -->
        <div class="box">
          <div class="box-text">
            <h3 class="wow fadeInRight">Connect here and now with thousands of people who are training mindulness online.</h3>
            <p class="wow fadeInLeft">In Vcommonsocial you can find information on mindfulness and its benefits. As a registered user you can get to know your stress level, train mindfulness, share your experience with your friends and ask experts that can help you with your practice.</p>
          </div>
          <img src="<?php echo base_url(); ?>assets/lander/upload/tabs.png" width="437" height="459"  class="wow fadeInRight"> </div>
        <!-- box --> 
      </div>
      <!-- tab --> 
    </div>
    <!-- wrap --> 
  </section>
  <!--  -->
  <section class="simple">
    <div class="wrap">
      <h2 class="wow bounceIn">Transform stress into well-being.</h2>
      <p class="wow fadeIn">Science, medicine and the experience of thousands of people support the benefits of practicing mindfulness. If you are interested in taking care of yourself, learning, and sharing with your friends what you learn from practicing mindfulness, this is your place and moment.</p>
    </div>
    <!-- wrap --> 
  </section>
  <!-- simple -->
  <section class="subscribe" id="subscribeWrap">
    <div class="wrap"> 
      <!--          <div class="subscribe-title wow fadeInUp">Subscribe to our newsletter</div>-->
      <div class="subscribe-form clearfix wow fadeInDown">
        <form>
          <div class="pull-left">
            <div id="successmessage" style="text-align:center; color:#009900; font-weight:bold;"></div>
            <uix-input id="emailid" type="text" name="useremail" class="subscribe-email" placeholder="Your email adress" value="Your email adress" data-controltype="email"  data-mandatory="true" data-msglocation="erroremail" data-requiredmessage="Please enter a Your Email." data-ng-model="useremail"></uix-input>
            <div id="erroremail" class="error-holder"></div>
          </div>
          <input type="button" value="Submit" class="subscribe-button" id="sendmailto">
        </form>
      </div>
      <!-- subscribe-form -->
      <div class="subscribe-note wow fadeIn">Write your email, we will contact you as soon as we launch.<br>
        We promise not to share your e-mail.</div>
    </div>
    <!-- wrap --> 
  </section>
  <!-- subscribe -->
  <footer>
    <div class="wrap">
      <div class="logo wow fadeInLeft"> <a href="#"><i class="icon-logo"></i></a> </div>
      <!-- logo -->
      <div class="copy wow fadeInRight">
        <p>Copyright &copy; 2014 <a href="#">Vcommonsocial</a></p>
      </div>
      <!-- copy --> 
      <!--<div class="social">
        <ul class="clearfix">
          <li class="wow fadeInUp"><a class="social-facebook" href="#" title="facebook">facebook</a></li>
          <li class="wow fadeInUp"><a class="social-twitter" href="#" title="twitter">twitter</a></li>
          <li class="wow fadeInUp"><a class="social-googleplus" href="#" title="google plus">google plus</a></li>
        </ul>
      </div>--> 
      <!--social --> 
    </div>
    <!-- wrap --> 
  </footer>
</section>
<!-- main --> 
<script src="<?php echo base_url(); ?>assets/lander/js/jquery.js"></script> 
<script src="<?php echo base_url(); ?>assets/lander/js/library.js"></script> 
<script src="<?php echo base_url(); ?>assets/lander/js/script.js"></script> 
<script src="<?php echo base_url(); ?>assets/lander/js/retina.js"></script> 
<script src="<?php echo base_url(); ?>assets/js/wow.min.js"></script> 

<!-- Form Validation Files --> 
<script src="<?php echo base_url(); ?>assets/js/vendor/BaseControl.js"></script> 

<!-- Angular Files --> 
<script src="<?php echo base_url(); ?>assets/js/vendor/angular.min.js"></script> 
 
<script src="<?php echo base_url(); ?>assets/js/app/services.js"></script> 
<script data-require="angular-ui-bootstrap@0.3.0" data-semver="0.3.0" src="<?php echo base_url(); ?>assets/js/vendor/ui-bootstrap-tpls-0.3.0.min.js"></script> 
<script src="<?php echo base_url(); ?>assets/js/vendor/chosen.js"></script> 
<script>
$(document).ready(function(e) {
    	$(".promo-button").on("click" ,function(e){
			  e.preventDefault();
				$("body, html").animate({ 
					scrollTop: $( $(this).attr('href') ).offset().top 
				}, 600);
			});
			
			$(".promo-link").on("click" ,function(e){
			  e.preventDefault();
				$("body, html").animate({ 
					scrollTop: $( $(this).attr('href') ).offset().top 
				}, 600);
			});
	
		$(document).on("click","#sendmailto",function(){
			emailid=$("#emailid").val();
			if(emailid=='')
			{
				$("#erroremail").text("Please insert valid e-mail.");
			}
			else
			{
				$.ajax({
					type: "POST", 
					url: "<?php echo base_url(); ?>home/sendpromoemail", 
					data: 'emailid='+emailid, 
					success: function(data) {
						$("#erroremail").text("Email sent successfully.");
					}
				});
			}
		})
	
	});
	var wow = new WOW(
		  {
			boxClass:     'wow',      // animated element css class (default is wow)
			animateClass: 'animated', // animation css class (default is animated)
			offset:       0,          // distance to the element when triggering the animation (default is 0)
			mobile:       false        // trigger animations on mobile devices (true is default)
		  }
	);
	wow.init();
</script>
</body>
</html>