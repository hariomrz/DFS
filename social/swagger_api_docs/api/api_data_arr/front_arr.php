<?php 

$swggeger_api_path = "/var/www/html/framework/social/swagger_api_docs/";
$swggeger_run_path = "/var/www/html/framework/social/vendor/bin/swagger";

$whitelist = array(
    '127.0.0.1',
    '::1',
    'localhost'
);

$base_path = '';
if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
    $base_path = '/social';
    $swggeger_api_path = "/var//www//html//framework//social//swagger_api_docs//";
    $swggeger_run_path = "/var//www//html//framework//social//vendor//bin//swagger";
    
} 



return  array(
    
   /* array(
        'api_name' => 'Activity_helper', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=activity_helper&from=front',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Activity_helper.php  --output  {$swggeger_api_path}json/front/activity_helper.json"
    ),    
     */
  
    array(
        'api_name' => 'Tags ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=tag&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Tag.php  --output  {$swggeger_api_path}json/front/tag.json"
    ), 
    
    array(
        'api_name' => 'Activity', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=activity&from=front',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Activity.php  --output  {$swggeger_api_path}json/front/activity.json"
    ),    
    array(
        'api_name' => 'Activity_helper', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=activity_helper&from=front',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Activity_helper.php  --output  {$swggeger_api_path}json/front/activity_helper.json"
    ),
    array(
        'api_name' => 'Activity_hide', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=activity_hide&from=front',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Activity_hide.php  --output  {$swggeger_api_path}json/front/activity_hide.json"
    ),
    array(
        'api_name' => 'Album', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=album&from=front',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Album.php  --output  {$swggeger_api_path}json/front/album.json"
    ),
    array(
        'api_name' => 'Announcement', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=announcement&from=front',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Announcement.php  --output  {$swggeger_api_path}json/front/announcement.json"
    ),   
    array(
        'api_name' => 'Comment ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=comment&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Comment.php  --output  {$swggeger_api_path}json/front/comment.json"
    ), 
    array(
        'api_name' => 'Contact ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=contact&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Contact.php  --output  {$swggeger_api_path}json/front/contact.json"
    ),                
    array(
        'api_name' => 'Favourite ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=favourite&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Favourite.php  --output  {$swggeger_api_path}json/front/favourite.json"
    ),
    array(
        'api_name' => 'Follow ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=follow&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Follow.php  --output  {$swggeger_api_path}json/front/follow.json"
    ),
    array(
        'api_name' => 'Interest ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=interest&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Interest.php  --output  {$swggeger_api_path}json/front/interest.json"
    ), 
    array(
        'api_name' => 'Locality ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=locality&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Locality.php  --output  {$swggeger_api_path}json/front/locality.json"
    ),    
    array(
        'api_name' => 'Login ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=login&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Login.php  --output  {$swggeger_api_path}json/front/login.json"
    ),               
    array(
        'api_name' => 'Media ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=media&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Media.php  --output  {$swggeger_api_path}json/front/media.json"
    ), 
    array(
        'api_name' => 'Message ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=Message&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Message.php  --output  {$swggeger_api_path}json/front/message.json"
    ),    
    array(
        'api_name' => 'Polls ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=polls&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Poll.php  --output  {$swggeger_api_path}json/front/polls.json"
    ),
    array(
        'api_name' => 'Profile ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=profile&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Profile.php  --output  {$swggeger_api_path}json/front/profile.json"
    ), 
    array(
        'api_name' => 'Search ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=search&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Search.php  --output  {$swggeger_api_path}json/front/search.json"
    ),        
    array(
        'api_name' => 'Signup ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=signup&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Signup.php  --output  {$swggeger_api_path}json/front/signup.json"
    ),
    array(
        'api_name' => 'Skill ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=skill&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Skill.php  --output  {$swggeger_api_path}json/front/skill.json"
    ),
    array(
        'api_name' => 'Upload ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=upload&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Upload.php  --output  {$swggeger_api_path}json/front/upload.json"
    ),
    array(
        'api_name' => 'Update Profile ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=update_profile&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Update_profile.php  --output  {$swggeger_api_path}json/front/update_profile.json"
    ),
    array(
        'api_name' => 'User Directory ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=directory&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Directory.php  --output  {$swggeger_api_path}json/front/directory.json"
    ),    
    array(
        'api_name' => 'Users ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=users&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Users.php  --output  {$swggeger_api_path}json/front/users.json"
    ),
    array(
        'api_name' => 'Ward ', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=ward&from=front', 
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Ward.php  --output  {$swggeger_api_path}json/front/ward.json"
    ),   
    array(
        'api_name' => 'Quiz', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=quiz&from=front',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}front_api/Quiz.php  --output  {$swggeger_api_path}json/front/quiz.json"
    ), 
);
