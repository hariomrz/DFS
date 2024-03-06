<?php

require_once APPPATH . '../../vendor/autoload.php'; 

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;

class Ga4_library{

    private static $GOOGLE_APPLICATION_CREDENTIALS = '';
    private static $GOOGLE_CLOUD_PROJECT = '';
    function __construct($data = array())
    {
        if ( ! empty($data))
        {
            self::$GOOGLE_APPLICATION_CREDENTIALS = $data['app_credentials'];
            self::$GOOGLE_CLOUD_PROJECT = $data['cloud_project_id'];
        }
    }

    /**
     * counting event based total users in given date.
     * @param startDate
     * @param endDate
     */

    public function eventDetails($startDate,$endDate){
        /**
     * TODO(developer): Replace this variable with your Google Analytics 4
     *   property ID before running the sample.
     */
    $property_id = self::$GOOGLE_CLOUD_PROJECT;
    // Using a default constructor instructs the client to use the credentials
    // specified in self::$GOOGLE_APPLICATION_CREDENTIALS environment variable.
    $credentials_json_path = self::$GOOGLE_APPLICATION_CREDENTIALS;

    // Explicitly use service account credentials by specifying
    // the private key file.
    $client = new BetaAnalyticsDataClient(['credentials' =>
        $credentials_json_path]);

    $response = $client->runReport([
        'property' => 'properties/' . $property_id,
        'dateRanges' => [new DateRange(['start_date' => $startDate,'end_date' => $endDate,]),],
        'dimensions' => [new Dimension(['name' => 'eventName',]),],
        'metrics' => [new Metric(['name' => 'eventCount',])]
        ]);
       $events = array();
        foreach ($response->getRows() as $row) {
            // print_r($row->getMetricValues()[0]->getValue());exit;
            $events[$row->getDimensionValues()[0]->getValue()]= $row->getMetricValues()[0]->getValue();
        }
        return $events;
    }

    /**
     * active users acc to google : The number of distinct users who visited your site or app.
     * I am taking country wise unique users in given date and sum up them as active users
     * @param startDate
     * @param endDate
     */
    public function activeUsers($startDate,$endDate){
        $property_id = self::$GOOGLE_CLOUD_PROJECT;
    $credentials_json_path = self::$GOOGLE_APPLICATION_CREDENTIALS;
    $client = new BetaAnalyticsDataClient(['credentials' => $credentials_json_path]);
    $response = $client->runReport([
        'property' => 'properties/' . $property_id,
        'dateRanges' => [new DateRange(['start_date' => $startDate,'end_date' => $endDate,]),],
        'dimensions' => [new Dimension(['name' => 'country',]),],
        'metrics' => [new Metric(['name' => 'activeUsers',])]
        ]);
       $totalActiveUsers = 0;
        foreach ($response->getRows() as $row) {
            $totalActiveUsers +=$row->getMetricValues()[0]->getValue();
        }
        return $totalActiveUsers;
    }


}

?>