<?php 
//echo print_r($activity);die;
?>
<div ng-controller="WallPostCtrl as WallPost">
    <ul>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db','Social Description 1','V Social 1','http://commonsocial-live.s3-us-west-2.amazonaws.com/upload/profilebanner/1200x300/f8f8d1341452502362.jpg');" >Facebook 1</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db2','Social Description 2','V Social 2','http://www.planwallpaper.com/static/images/4-Nature-Wallpapers-2014-1_ukaavUI.jpg');" >Facebook 2</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db3','Social Description 3','V Social 3','http://www.desktopwallpaper.cn/pic2/dtymjzofhpfwtptwimut.jpg');" >Facebook 3</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db4','Social Description 4','V Social 4','http://commonsocial-live.s3-us-west-2.amazonaws.com/upload/profilebanner/1200x300/f8f8d1341452502362.jpg');" >Facebook 4</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db5','Social Description 5','V Social 5','https://encrypted-tbn3.gstatic.com/images?q=tbn:ANd9GcQjpKt9Dcfg4uvt-fniZsclEtUU4CfrAU2INEtx3qyjspltM4g4');" >Facebook 5</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db6','Social Description 5','V Social 6','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTp1-NNfLpNtsFQY25z7A4u9pp2D_vJHTNU70RDupydx4i7BrMKYw');" >Facebook 6</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db7','Social Description 7','V Social 7','https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcTA8HjDkOQmVSKfoFbcOPNTmXS9KP2ZPHhFXZBckX-9CnVdcQQC');" >Facebook 7</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db8','Social Description 8','V Social 8','https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcQ8nicKXy-6pXSyi-KHDH1V63Em_O9Tq7vEXzCvUymhgO_B4LGo');" >Facebook 8</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db9','Social Description 9','V Social 9','https://encrypted-tbn3.gstatic.com/images?q=tbn:ANd9GcQldP7Ck_QwXVwzP1eBajV9C65etSl6sQPBx8RZezZ2UzRLIhrw');" >Facebook 9</span></li>
        <li><span ng-click="FacebookShare('<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db10','Social Description 10','V Social 10','https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcQjAfusbb4u2J6-Ehb3zv5OVhbfd7VRDzCYTqpR1KQyLF80Uol7');" >Facebook 10</span></li>
        <li><span ng-click="twitterShare('Social Description 1','<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db' );" >Twitter 1</span></li>
        <li><span ng-click="twitterShare('Social Description 2','<?php echo site_url() ?>rohit/activity/16de922b-af47-93dc-1a46-3f2dff6b92db2');" >Twitter 2</span></li>
       
    </ul>
    
</div>
<script type="text/javascript">
    window.fbAsyncInit = function () {
        FB.init({
            appId:FacebookAppId,
            xfbml: true,
            version: 'v2.5'
        });
    };
        (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
    </script>


