<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Twelvedata
{
	protected $end_point = 'https://api.twelvedata.com';
	protected $api_key = '';
	public function __construct() {
	}

    public function query(string $uri, array $parameters) {
		
        $headers = array(
            'Content-Type: application/json',
        );

        $standardParameters = [
            'apikey' => $this->api_key,
        ];

        $finalParameters = array_merge($standardParameters, $parameters);

        $query = http_build_query($finalParameters);
        $url = $this->end_point . $uri . "?" . $query;
		
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        try {
            $response = curl_exec($ch);
            if ($response === FALSE) {
                throw new Exception('Error Twelvedata - ' . curl_error($ch));
            }
            curl_close($ch);
            $result = json_decode($response, true);
            $status_code = isset($result['code']) ? $result['code'] : '';
            if(in_array($status_code, array(400, 401, 403, 404, 414, 429, 500))) {
                $message = isset($result['message']) ? $result['message'] : '';
                $status = isset($result['status']) ? $result['status'] : '';
                throw new Exception('Error Twelvedata - ' . $status.': '.$message);
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception('Error Twelvedata - ' . $e->getMessage());
        }
	}
}