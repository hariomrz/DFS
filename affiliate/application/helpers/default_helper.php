<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed');}

/**
 * Get formated  date string.
 * @param string $date
 * @param string $format
 * @return string
 */
if (!function_exists('format_date')) {

    function format_date($date = 'today', $format = DATE_FORMAT) {
        if ($date == "today") {
            if (IS_LOCAL_TIME === TRUE) {
                $back_time = strtotime(BACK_YEAR);
                $dt = date($format, $back_time);
            } else {
                $dt = date($format);
            }
        } else {
            if (is_numeric($date)) {
                $dt = date($format, $date);
            } else {
                if ($date != null) {
                    $dt = date($format, strtotime($date));
                } else {
                    $dt = "--";
                }
            }
        }

        $path = APPPATH . '../../date_time.php';

        if (file_exists($path)) {
            include($path);
        }

        if (isset($date_time) && $date_time && (ENVIRONMENT !== 'production' )) {
            $dt = date($format, strtotime($date_time));
        }
        return $dt;
    }

}

/**
 * Get pagination offset.
 * @param int $page_no
 * @param int $lmiit
 * @return int
 */
if (!function_exists('get_pagination_offset')) {

    function get_pagination_offset($page_no, $lmiit) {
        if (empty($page_no)) {
            $page_no = 1;
        }
        return ($page_no - 1) * $lmiit;
    }

}

/**
 * replace quotes by "".
 * @param string $string
 * @return string
 */
if (!function_exists('replace_quotes')) {

    function replace_quotes($string) {
        return preg_replace(array("/`/", "/'/", "/&acute;/"), "", $string);
    }

}

/**
 * truncate number
 * @param int $number
 * @param int $decimals
 * @return string
 */
if (!function_exists('truncate_number')) {

    function truncate_number($number = 0, $decimals = 2) {
        $point_index = strrpos($number, '.');
        if ($point_index === FALSE)
            return $number;
        return substr($number, 0, $point_index + $decimals + 1);
    }

}

/**
 * truncate number only
 * @param int $number
 * @param int $precision
 * @return int
 */
if (!function_exists('truncate_number_only')) {

    function truncate_number_only($number, $precision = 2) {
        // Zero causes issues, and no need to truncate
        if (0 == (int) $number) {
            return $number;
        }
        // Are we negative?
        $negative = $number / abs($number);
        // Cast the number to a positive to solve rounding
        $number = abs($number);
        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);
        // Run the math, re-applying the negative value to ensure returns correctly negative / positive
        return floor($number * $precision) / $precision * $negative;
    }

}

/**
 * generate random string based on given length.
 * @param int $length
 * @return string
 */
if (!function_exists('generateRandomString')) {

    function generateRandomString($length = 8) {
        return substr(md5(mt_rand() . uniqid()), 0, $length);
    }

}

//helper function for converting field name to camel case in case of report export
function camelCaseString($key){
    $key = ucfirst(implode('', array_map('ucfirst', explode('_', $key))));
    return $key;
}

function generate_data_hash($string, $action = 'e' ) {
    //Please update hash keys
    $secret_key = 'Bhf45Sdds3HB45';
    $secret_iv = 'Hfdd456Htr6G456d';
    $output = "";
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }

    return $output;
}