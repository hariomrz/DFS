<?php

defined('BASEPATH') OR exit('No direct script access allowed');
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
include APPPATH . 'third_party/yahoo/OAuth/OAuth.php';
include APPPATH . 'third_party/yahoo/Yahoo/YahooOAuthApplication.class.php';

//require_once APPPATH . '/libraries/REST_Controller.php';

class Yahoo extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model(array('users/login_model'));
        $this->post_data = $this->post();
        $this->app = new YahooOAuthApplication(YAHOO_CONSUMER_KEY, YAHOO_CONSUMER_SECRET, YAHOO_APP_ID);
    }

    public function yahoo_signin_get()
    {
        if (array_key_exists("in_popup", $_GET))
        {

            $url = $_GET['in_popup'] . '?oauth_verifier=' . $_GET['oauth_verifier'];
            header('Location:' . $url);
        }
        else
        {
            $callback_params = array('in_popup' => base_url() . 'api/yahoo/getUserData');
            $callback = sprintf("%s://%s%s?%s", (@$_SERVER["HTTPS"] == 'on') ? 'https' : 'http', @$_SERVER["HTTP_HOST"], @$_SERVER["REQUEST_URI"], http_build_query($callback_params));
            $request_token = $this->app->getRequestToken($callback);

            $this->oauth_set_cookie('yos-social-rt', $request_token, $request_token->expires_in);

            $auth_url = $this->app->getAuthorizationUrl($request_token);
            redirect($auth_url);
        }
    }

    function getUserData_get()
    {
        if (array_key_exists("oauth_verifier", $_GET))
        {

            $request_token = $this->oauth_get_cookie('yos-social-rt');
            $this->app->token = $this->app->getAccessToken($request_token, $_GET['oauth_verifier']);
            $this->app->token->expires = 'foobar';
            $this->oauth_set_cookie('yos-social-at', $this->app->token, $this->app->token->expires_in);
            //  close_popup();
            $token = $this->oauth_get_cookie('yos-social-at');
            $user_data=array();
            if ($token)
            {
                $Connections = $this->app->getContacts($token->yahoo_guid, 0, 100);
                if ($Connections)
                {
                    
                        foreach($Connections->contact as $item){
                            $user_data[]=array('id'=>$item->fields[1]->value,'name'=>$item->fields[0]->value->givenName,'email'=>$item->fields[1]->value);
                    }
                }
                 echo '<script type="text/javascript">
                                window.opener.network_signin.prototype.response_user_data('.json_encode($user_data).');
                                window.close();
                      </script>';
            }
        }
    }

    function oauth_get_cookie($name)
    {
        return unserialize(base64_decode(@$_COOKIE[$name]));
    }

    function oauth_set_cookie($name, $data, $expires = 3600)
    {
        setcookie($name, base64_encode(serialize($data)), time() + $expires);
    }

    function oauth_unset_cookie($name)
    {
        setcookie($name, '', time() - 600);
    }

    function close_popup()
    {
        ?>
        <script type="text/javascript">
            window.close();
        </script>
        <?php

    }

}
