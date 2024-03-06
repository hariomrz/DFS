<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
* Example
* This Class used for REST API
* (All THE API CAN BE USED THROUGH POST METHODS)
* @package      CodeIgniter
* @subpackage   Rest Server
* @category Controller
* @author       Phil Sturgeon
* @link     http://philsturgeon.co.uk/code/
*/
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require_once APPPATH.'/libraries/REST_Controller.php';
class Twitter extends REST_Controller
{      
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('users/login_model'));
        $this->load->library(array('twitteroauth'));
        parse_str($_SERVER['QUERY_STRING'],$_REQUEST);
        $this->post_data = $this->post();
        if(!session_id())
        {
          session_start();
        }
    }

    public function twitter_signin()
    {
        $connection = new Twitteroauth(TWITTERAPIKEY,TWITTERAPISECRET);
        $request_token = $connection->getRequestToken(site_url().'api/twitter/twitter_user_info');
        $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        switch ($connection->http_code) 
        {
            case 200:
            /* Build authorize URL and redirect user to Twitter. */
            $url = $connection->getAuthorizeURL($token);
            header('Location: ' . $url);
            break;
            default:
            /* Show notification if something went wrong. */
            echo 'Could not connect to Twitter. Refresh the page or try again later.';
        }
    }

    function twittersignup_get()
    {
        
        $_SESSION['action']=$this->uri->segment(3);
        //error_reporting(E_ALL ^ (E_NOTICE | E_WARNING ));
        // $this->load->library('Twitteroauth');
        /* Build Twitteroauth object with client credentials. */
        
        $connection = new Twitteroauth(TWITTERAPIKEY,TWITTERAPISECRET);
        /* Get temporary credentials. */
        $request_token = $connection->getRequestToken(base_url().'api/twitter/twitter_user_info');
        /* Save temporary credentials to session. */
        $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        /* If last connection failed don't display authorization link. */

        switch ($connection->http_code) 
        {
            case 200:
            /* Build authorize URL and redirect user to Twitter. */
            $url = $connection->getAuthorizeURL($token);
            header('Location: ' . $url);
            break;
            default:
            /* Show notification if something went wrong. */
            echo 'Could not connect to Twitter. Refresh the page or try again later.';
        }
    }

    public function twitter_user_info_get() {
        error_reporting(E_ALL ^ E_NOTICE);
        $connection = new Twitteroauth(TWITTERAPIKEY,TWITTERAPISECRET,$_SESSION['oauth_token'],$_SESSION['oauth_token_secret']);
        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $_SESSION['access_token'] = $access_token;
        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $connection->http_code) {
            /* If method is set change API call made. Test is called by default. */
            $content = $connection->get('account/verify_credentials');

            /* Some example calls */
            $users = $connection->get('users/show', array('screen_name' => $content->screen_name));
            if(isset($users->id) && $users->id!='')
            {
                $exp=explode(" ",$users->name);
                $profile_image = str_replace("_normal","", $content->profile_image_url);
                $data['user']=array('taction'=>$_SESSION['action'],'id'=>$users->id,'firstname'=>$exp[0],'lastname'=>$exp[1],'picture'=>$profile_image,'screen_name'=>$users->screen_name);
                echo '<script type="text/javascript">
                window.opener.receiveDataFromPopup('.json_encode($data).');
                window.close();
                </script>';
            }
        } else {
            echo "<h1> Twitter login failed </h1>" ;
        }
        die;
   }

    public function twitter_friend_list_post_method($friend_list,$task_type='0',$user_data='') {
        if($task_type =='0') {
            return $friend_list->ids;
        } else {

            $details = json_encode($friend_list);
            echo '<script type="text/javascript">
                        window.opener.Network_twitter.prototype.response_twitter_friend('.$details.','.json_encode($user_data).');
                        window.close();
              </script>';
        }
    }

    public function getUserData_get() 
    {
        error_reporting(E_ALL ^ E_NOTICE);
        $connection = new Twitteroauth(TWITTERAPIKEY,TWITTERAPISECRET,$_SESSION['userDataKey'],$_SESSION['userDataSecret']);
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
        $_SESSION['access_token'] = $access_token;
        if (200 == $connection->http_code) 
        {
            $content = $connection->get('account/verify_credentials');
            $users=$connection->get('users/show', array('screen_name' => $content->screen_name));
            $user_data                      =array();
            $user_data['twitter_id']        =$users->id;
            $user_data['name']              =$users->name;
            $user_data['screen_name']       =$users->screen_name;
            $user_data['profile_image_url'] =$users->profile_image_url;
            
            $friends_id = $connection->get('followers/ids', array('screen_name' => $content->screen_name));
            $friends = $this->twitter_friend_list_post_method($friends_id);
            $friends = array_chunk($friends, 99);
            if($friends)
            {
              foreach ($friends as $key=>$val) {
                $friends_data[$key] = $connection->get('users/lookup', array('user_id' => implode(",",$val)));
              }
              $friends_data = array_flatten($friends_data);
            }
            $this->twitter_friend_list_post_method($friends_data,1,$user_data);
        }
    }
    
    public function get_twitter_friends_get() 
    {
        // error_reporting(E_ALL ^ E_NOTICE);
        $connection = new Twitteroauth(TWITTERAPIKEY,TWITTERAPISECRET);
        $request_token = $connection->getRequestToken(base_url().'api/twitter/getUserData');
        $_SESSION['userDataKey']=$request_token['oauth_token'];
        $_SESSION['userDataSecret']=$request_token['oauth_token_secret'];
        if($connection->http_code==200) 
        {
            $url = $connection->getAuthorizeURL($_SESSION['userDataKey']);
            header('Location:'.$url);
        }
    }

    /**
     * get_twitter_user_data()
     * Connect to Twitter and collect user information.
     * @access public
     * @param
     * @return
     */

    public function get_twitter_user_data() {

        $twitter =json_decode($_SESSION['twitter']);

        $user_data = array();
        $user_data['twitter_id']        = $twitter->id;
        $user_data['name']              = $twitter->name;
        $user_data['screen_name']       = $twitter->screen_name;
        $user_data['profile_image_url'] = $twitter->profile_image_url;

        echo json_encode($user_data);

    }

    public function check_user_exists_post(){
      $Return['ResponseCode'] = 200;
      $Return['Data'] = array();
      $Return['ServiceName'] = 'api/twitter/check_user_exists';
      $Return['Message'] = lang('success');

      $Data = $this->post_data;
      $user_id = $this->UserID;
      $Return['Data'] = $Data;
      $SocialID = '';
      if(isset($Data['SocialID'])){
        $SocialID = $Data['SocialID'];
      }
      $SocialTypeID = '';
      if(isset($Data['SocialTypeID'])){
        $SocialTypeID = $Data['SocialTypeID'];
      }
      $checkData = $this->login_model->check_social_user_exists($SocialID,$SocialTypeID,$user_id);
      if($checkData){
        if($checkData=='1'){
          $Return['Data']['Status'] = '1';
        } else {
          $Return['Data']['Status'] = '3';
        }
      } else {
        $Return['Data']['Status'] = '2';
      }
      $this->response($Return);
    }

    /**
     * do_login_twt()
     * Connect to Twitter and collect user information.
     * This method will call only in child window (iframe) and return user's all data
     * ONLY USE FOR SIGN IN PROCESS.
     * @access public
     * @param
     * @return
     */

    public function do_login_twt() {

        try {
             $_SESSION['calling_method'] = '/twitter/do_login_twt';
            if(isset($_SESSION['twitter'])){

                $twitter = json_decode($_SESSION['twitter']);

                $user_data                      = array();
                $user_data['twitter_id']        = $twitter->id;
                $user_data['name']              = $twitter->name;
                $user_data['screen_name']       = $twitter->screen_name;
                $user_data['profile_image_url'] = $twitter->profile_image_url;

                echo '<script type="text/javascript">
                                window.opener.twitter_signin.prototype.response_user_data('.json_encode($user_data).');
                                window.close();
                      </script>';
            } else {
                $this->twitter_signin();
            }
            } catch(Exception $e) {
            print_r($e);
        }
    }


    /**
     * twt_login_build_network()
     * Build network from Twitter connection.
     * @access public
     * @param
     * @return
     */

    public function twt_login_build_network() {
        $_SESSION['calling_method'] = '/twitter/twt_login_build_network';

        if(isset($_SESSION['twitter'])) {

            $twitter = json_decode($_SESSION['twitter']);

            $user_data                      = array();
            $user_data['twitter_id']        = $twitter->id;
            $user_data['name']              = $twitter->name;
            $user_data['screen_name']       = $twitter->screen_name;
            $user_data['profile_image_url'] = $twitter->profile_image_url;

            echo '<script type="text/javascript">
                   window.opener.Network_twitter.prototype.response_user_data('.json_encode($user_data).');
                   window.close();
                  </script>';
        } else {
            $this->twitter_signin();
        }
    }

    /**
     * twt_login_build_network()
     * Build network from Twitter connection.
     * @access public
     * @param
     * @return
     */

    public function twt_login_account_setting(){
        $_SESSION['calling_method'] = '/twitter/twt_login_account_setting';

            $this->twitter_signin();


    }

    public function post_direct_message_get() 
    {
        
        $connection    = new Twitteroauth(TWITTERAPIKEY,TWITTERAPISECRET);
        $request_token = $connection->getRequestToken(site_url().'api/twitter/post_direct_message_callback');

        $_SESSION['twt_oauth_token']        = $token = $request_token['oauth_token'];
        $_SESSION['twt_oauth_token_secret'] = $request_token['oauth_token_secret'];
        
        $Data = $this->get();
        $_SESSION['friend_list']     = $Data['list'] ;
        $_SESSION['LoginSessionKey'] = $Data[AUTH_KEY] ;

         /* If last connection failed don't display authorization link. */
        switch ($connection->http_code) {
            case 200:
                /* Build authorize URL and redirect user to Twitter. */
                $url = $connection->getAuthorizeURL($token);
                header('Location: ' . $url);
                break;
            default:
                /* Show notification if something went wrong. */
                echo 'Could not connect to Twitter. Refresh the page or try again later.';
        }

        
    }

    public function post_direct_message_callback_get(){
        $connection = new Twitteroauth(TWITTERAPIKEY, TWITTERAPISECRET, $_SESSION['twt_oauth_token'], $_SESSION['twt_oauth_token_secret']);

        error_reporting(1);
        /* Request access tokens from twitter */

        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        /* Save the access tokens. Normally these would be saved in a database for future use. */
        // $_SESSION['access_token'] = $access_token;
        if (200 == $connection->http_code) {
            /* If method is set change API call made. Test is called by default. */
            $content         = $connection->get('account/verify_credentials');
            $friend_list     = $_SESSION['friend_list'];
            $friend_list     = explode('-', $friend_list);
            $LoginSessionKey = $this->session->userdata('LoginSessionKey');

            if(!empty($friend_list)){

                foreach($friend_list as $id){
                    
                    $message = $this->save_invitation($id,$LoginSessionKey) ;

                    //$msg = site_url()."signup?Token=".$message;
                    
                    $text = "Hi, Your friend on Twitter ".$_SESSION['access_token']['screen_name'].' invited you to join '.SITE_NAME.' '.$message;

                    $options = array("user_id"=>trim($id), "text"=>$text ) ;                   

                   $result =  $connection->post('direct_messages/new', $options);

                   echo "
                   <script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
                   <script>
                   $(document).ready(function(){
                    $('#wrap_user_".$id."',opener.document).after('Already Invited');
                    $('#wrap_user_".$id."',opener.document).remove();
                   });
                   </script>";
                }
            }
            echo " <h3> Your invitation has been sent successfully. </h3> ";
        }
        else {
            echo "<h3> Some thing went wrong </h3> ";
        }

        echo "<p> This window will close automatically within 5 sec.</p>";
        echo '<script type="text/javascript"> opener.update_invite_user("'.$friend_list[0].'"); setTimeout(function(){ window.close();  }, 5000);    </script>';
        
    }

    /**
     * save_invitation()
     * Save invitation for Twitter user.
     * @access public
     * @param $social_id Twitter social identifier
     * @return link to invitation
     */

    public function save_invitation($social_id,$LoginSessionKey) 
    {
        $this->GetUserIDByLoginSessionKey($LoginSessionKey);
        $user_data = array();
        $userid    = $this->UserID;        
        $token     = uniqid();

        $insert_data = array(array( 
                                'user_id'       => $userid, 
                                'invite_type'   => 3, 
                                'user_social_id' => $social_id, 
                                'token'        => $token,  
                                'created_date'  => get_current_date('%Y-%m-%d %H:%i:%s'), 
                                'is_registered' => 0, 
                                'EntityType'   => 0,
                                'EntityID'  => 0 
                            ));
        $this->load->model('build_network_model');
        $token_ar = $this->build_network_model->save_native_invitation($insert_data);
        $token = $token_ar[0];
        
        $link = site_url().'signup?Token='.( $token );
        //$return=$this->google_url_shortner($link);
        return $link;
        //return " join ".SITE_NAME." ".  $return ;
    }

    /**
     * twitter_lookup()
     * Look user up on Twitter.
     * @access public
     * @param
     * @return
     */

    public function twitter_lookup() {
        $ids = $this->input->post('twitter_id');
        $user_detail = 'https://api.twitter.com/1.1/users/lookup.json?user_id='.$ids.'&include_entities=true';
        $details = curl($user_detail,'GET');
        echo $details;
    }

    /**
     *  google_url_shortner()
     * Look user up on Twitter.
     * @access public
     * @param $longUrl url to be shortened
     * @return link to shorter url
     */

public function google_url_shortner($longUrl) {

      //$longUrl = 'https://www.packtpub.com/php-jquery-cookbook-to-create-interactive-web-applications/book';
      $apiKey = CLIENT_ID;
      //Get API key from : http://code.google.com/apis/console/

      $postData = array('longUrl' => $longUrl, 'key' => $apiKey);
      $jsonData = json_encode($postData);

      $curlObj = curl_init();

      curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url');
      curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curlObj, CURLOPT_HEADER, 0);
      curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
      curl_setopt($curlObj, CURLOPT_POST, 1);
      curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

      $response = curl_exec($curlObj);

      //change the response json string to object
      $json = json_decode($response);

      curl_close($curlObj);

      return $json->id;
    }
    
    function GetUserIDByLoginSessionKey($LoginSessionKey,$servicename='')
    {
        $Inputs=array();
        $Inputs['LoginSessionKey']=$LoginSessionKey;
        $AuthData=$this->login_model->active_login_auth($Inputs);

        if(!isset($AuthData['Data']['UserID'])) 
        {
            $Outputs[$servicename]=$AuthData;
            $this->response($Outputs);
        }
        else
        {
            $this->UserID=$AuthData['Data']['UserID'];
        }
    }
}