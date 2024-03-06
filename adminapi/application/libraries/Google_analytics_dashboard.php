<?php
// Load the Google API PHP Client Library.

require_once APPPATH . '../../vendor/autoload.php'; 

class Google_analytics_dashboard {
  private static $GA_PRIVATE_KEY_LOCATION = '';
  function __construct($data = array())
    {
        if ( ! empty($data))
        {
            self::$GA_PRIVATE_KEY_LOCATION = $data['private_key_location'];
        }
    }

function eventDetails($startDate,$endDate) {

  $analytics = $this->initializeAnalytics();
  $profile = $this->getFirstProfileId($analytics);
  $results  = $this->getResults($analytics, $profile,$startDate,$endDate);
  return $results;
}
function appUsageResults($startDate,$endDate) {

  $analytics = $this->initializeAnalytics();
  $profile = $this->getFirstProfileId($analytics);
  $results  = $this->getAppUsageResults($analytics, $profile,$startDate,$endDate);
  return $results;
}
function browserUsageResults($startDate,$endDate) {

  $analytics = $this->initializeAnalytics();
  $profile = $this->getFirstProfileId($analytics);
  $results  = $this->getBrowserUsageResults($analytics, $profile,$startDate,$endDate);
  return $results;
}
function activeUsers($startDate,$endDate) {

  $analytics = $this->initializeAnalytics();
  $profile = $this->getFirstProfileId($analytics);
  $results  = $this->getActiveUsers($analytics, $profile,$startDate,$endDate);
  return $results;
}


public function initializeAnalytics()
{
  // Creates and returns the Analytics Reporting service object.

  // Use the developers console and download your service account
  // credentials in JSON format. Place them in this directory or
  // change the key file location if necessary.
  

  // Create and configure a new client object.
  $client = new Google_Client();
  $client->setApplicationName("Hello Analytics Reporting");
  $client->setAuthConfig(self::$GA_PRIVATE_KEY_LOCATION);
  $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
  $analytics = new Google_Service_Analytics($client);

  return $analytics;
}

function getFirstProfileId($analytics) {
  // Get the user's first view (profile) ID.

  // Get the list of accounts for the authorized user.
  $accounts = $analytics->management_accounts->listManagementAccounts();

  if (count($accounts->getItems()) > 0) {
    $items = $accounts->getItems();
    
    $firstAccountId = $items[0]->getId();

    // Get the list of properties for the authorized user.
    $properties = $analytics->management_webproperties
        ->listManagementWebproperties($firstAccountId);

    if (count($properties->getItems()) > 0) {
      $items = $properties->getItems();
      $firstPropertyId = $items[0]->getId();

      // Get the list of views (profiles) for the authorized user.
      $profiles = $analytics->management_profiles
          ->listManagementProfiles($firstAccountId, $firstPropertyId);

      if (count($profiles->getItems()) > 0) {
        $items = $profiles->getItems();
        foreach($items as $key => $item){
          $pro_id = $item->getId();
          if($pro_id =='204295469'){ //framework.vinfotech
            $profile_id = $pro_id;
          }
          else{
             $profile_id = $items[0]->getId(); 

          }
          
      }
        // Return the first view (profile) ID.
        return $profile_id; //$items[0]->getId();

      } else {
        throw new Exception('No views (profiles) found for this user.');
      }
    } else {
      throw new Exception('No properties found for this user.');
    }
  } else {
    throw new Exception('No accounts found for this user.');
  }
}

function getResults($analytics, $profileId,$startDate,$endDate) {
    //For Overview report
    $reportmetrics = 'ga:totalEvents,ga:sessions';
   // $startDate='2019-07-10';
    $reportOptParams = array('dimensions' => 'ga:eventCategory,ga:eventAction,ga:eventLabel,ga:userType', 'sort' => 'ga:eventAction');
    $ga_basic_data = $analytics->data_ga->get('ga:' . $profileId, $startDate,$endDate, $reportmetrics, $reportOptParams);
    //print_r( $ga_basic_data); die;
      $returnArr = array(); 
      $returnArr['signup']=0;
      $returnArr['login']=0;
      $returnArr['join-contest']=0;
      $returnArr['Paymentgateway']=0;
      $returnArr['send-download-link']=0;
      $returnArr['apk-download']=0;

      if ($ga_basic_data['totalResults'] > 0) {
      
        //$profileName =  $results['rows']['1'][3]; 
      foreach($ga_basic_data['rows'] as $row ){
        if(isset($returnArr[$row[0]])){
          $returnArr[$row[0]] = ($returnArr[$row[0]] + $row[4]);
        } else{
          $returnArr[$row[0]] =$row[4];  
        }
        
      }
      $returnArr['Visitors']=(int)$ga_basic_data['totalsForAllResults']['ga:sessions']; 
      //into percent
      /* $returnArrUp = array();
      $returnArrUp['Signup']=$returnArr['Signup'];
      $returnArrUp['Visitors']=$returnArr['Visitors'];
      $returnArrUp['Login']=(int)(($returnArr['Login']/$returnArr['Visitors'])*100);
      $returnArrUp['Fixture']=(int)(($returnArr['Fixture']/$returnArr['Visitors'])*100);
      $returnArrUp['Contestlist']=(int)(($returnArr['Contestlist']/$returnArr['Visitors'])*100);
      $returnArrUp['Contestjoin']=(int)(($returnArr['Contestjoin']/$returnArr['Visitors'])*100);
      $returnArrUp['Createteam']=(int)(($returnArr['Createteam']/$returnArr['Visitors'])*100);

      $returnArrUp['Selectcaptain']=(int)(($returnArr['Selectcaptain']/$returnArr['Visitors'])*100);
      $returnArrUp['Confirmteam']=(int)(($returnArr['Confirmteam']/$returnArr['Visitors'])*100);
      $returnArrUp['Confirmationpopup']=(int)(($returnArr['Confirmationpopup']/$returnArr['Visitors'])*100);
      $returnArrUp['Paymentgateway']=(int)(($returnArr['Paymentgateway']/$returnArr['Visitors'])*100);
      $returnArrUp['Joingame']=(int)(($returnArr['Joingame']/$returnArr['Visitors'])*100); */

      $returnArr['join_contest'] = $returnArr['join-contest'];
      $returnArr['send_download_link'] = $returnArr['send-download-link'];
      $returnArr['apk_download'] = $returnArr['apk-download'];
      unset($returnArr['apk-download']);
      unset($returnArr['send-download-link']);
      unset($returnArr['join-contest']);
      unset($returnArr['Login']);
      unset($returnArr['Nocampaign']);
      unset($returnArr['Visitors']);
      unset($returnArr['join contest']);


     // print_r( $returnArrUp); die;
      return $returnArr;
    } 
    else{
      return $returnArr;
    }
}
function getActiveUsers($analytics, $profileId,$startDate,$endDate) {
  //For Overview report
  $reportmetrics = 'ga:totalEvents,ga:sessions';
 // $startDate='2019-07-10';
  $reportOptParams = array('dimensions' => 'ga:eventCategory,ga:eventAction,ga:eventLabel,ga:userType', 'sort' => 'ga:eventAction');
  $ga_basic_data = $analytics->data_ga->get('ga:' . $profileId, $startDate,$endDate, $reportmetrics, $reportOptParams);
  //print_r( $ga_basic_data); die;
    $returnArr = array();
    
    $returnArr['Visitors']=0;
    $returnArr['loggedInusers']=0;//registered users which already loggedin
    
    if ($ga_basic_data['totalResults'] > 0) {
    //$profileName =  $results['rows']['1'][3]; 
    foreach($ga_basic_data['rows'] as $row ){
      if(isset($returnArr[$row[1]])){
        $returnArr[$row[1]] = ($returnArr[$row[1]] + $row[4]);
      } else{
       // $returnArr[$row[1]] =$row[3];  
      }
      
    }
    $returnArr['Visitors']=(int)$ga_basic_data['totalsForAllResults']['ga:sessions']; 
   
    return $returnArr;
  } 
  else{
    return $returnArr;
  }
}
function getAppUsageResults($analytics, $profileId,$startDate,$endDate) {
  //For Overview report
  $reportmetrics = 'ga:sessions';
 // $startDate='2019-07-10'; ga:mobileDeviceBranding,ga:deviceCategory
  $reportOptParams = array('dimensions' => 'ga:deviceCategory', 'sort' => 'ga:deviceCategory');
  $ga_basic_data = $analytics->data_ga->get('ga:' . $profileId, $startDate, $endDate, $reportmetrics, $reportOptParams);
  
  if ($ga_basic_data['totalResults'] > 0) {
    //$profileName =  $results['rows']['1'][3]; 
    $returnArr = array();
    /* $returnArr['desktop']=0;
    $returnArr['tablet']=0;
    $returnArr['mobile']=0; */
    foreach($ga_basic_data['rows'] as $row ){
        if($row[0]=='desktop') $row[0]='Desktop';
        if($row[0]=='mobile') $row[0]='Mobile';
        if($row[0]=='tablet') $row[0]='Tablet';
        $returnArr[] = array('y'=>(int)$row[1],'name'=>$row[0],'color'=> '#F77084');
      }
    
    return $returnArr;
  } 
  else{
    return array();
  }
}
function getBrowserUsageResults($analytics, $profileId,$startDate,$endDate) {
  
  $reportmetrics = 'ga:sessions';
  $reportOptParams = array('dimensions' => 'ga:browser', 'sort' => 'ga:browser');
  $ga_basic_data = $analytics->data_ga->get('ga:' . $profileId, $startDate, $endDate, $reportmetrics, $reportOptParams);
  if ($ga_basic_data['totalResults'] > 0) {
    $returnArr = array();
    foreach($ga_basic_data['rows'] as $row ){
        $returnArr[] = array('y'=>(int)$row[1],'name'=>$row[0],'color'=> '#F77084');
    }
    
    return $returnArr;
  } 
  else{
    return array();
  }
}

}




