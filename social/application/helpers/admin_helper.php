<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Function for convert php date_format into Jquery/js date_format
 * Matches each symbol of PHP date format standard with jQuery equivalent codeword
 * Parameters : php Date Format
 * Return : Jquery/js Date Format
 */
function dateformat_php_to_jqueryui($php_format)
{
    $SYMBOLS_MATCHING = array(
        // Day
        'd' => 'dd',
        'D' => 'D',
        'j' => 'd',
        'l' => 'DD',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => 'o',
        // Week
        'W' => '',
        // Month
        'F' => 'MM',
        'm' => 'mm',
        'M' => 'M',
        'n' => 'm',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => 'yy',
        'y' => 'y',
        // Time
        'a' => '',
        'A' => '',
        'B' => '',
        'g' => '',
        'G' => '',
        'h' => '',
        'H' => '',
        'i' => '',
        's' => '',
        'u' => ''
    );
    $jqueryui_format = "";
    $escaping = false;
    for ($i = 0; $i < strlen($php_format); $i++)
    {
        $char = $php_format[$i];
        if ($char === '\\')
        { // PHP date format escaping character
            $i++;
            if ($escaping)
                $jqueryui_format .= $php_format[$i];
            else
                $jqueryui_format .= '\'' . $php_format[$i];
            $escaping = true;
        }
        else
        {
            if ($escaping)
            {
                $jqueryui_format .= "'";
                $escaping = false;
            }
            if (isset($SYMBOLS_MATCHING[$char]))
                $jqueryui_format .= $SYMBOLS_MATCHING[$char];
            else
                $jqueryui_format .= $char;
        }
    }
    return $jqueryui_format;
}

/**
 * Function for convert php date_format into mysql date_format
 * Matches each symbol of PHP date format standard with mysql equivalent codeword
 * Parameters : php Date Format
 * Return : Mysql Date Format
 */
function dateformat_php_to_mysql($php_format)
{
    $SYMBOLS_MATCHING = array(
        // Day
        'd' => '%d',
        'D' => '%b',
        'j' => '%e',
        'l' => '%W',
        'N' => '%w',
        'S' => '%D',
        'w' => '%c',
        'z' => '%j',
        // Week
        'W' => '%u',
        // Month
        'F' => '%M',
        'm' => '%m',
        'M' => '%b',
        'n' => '%m',
        't' => '',
        // Year
        'L' => '',
        'o' => '',
        'Y' => '%Y',
        'y' => '%y',
        // Time
        'a' => '%p',
        'A' => '%p',
        'B' => '',
        'g' => '%l',
        'G' => '%k',
        'h' => '%h',
        'H' => '%H',
        'i' => '%i',
        's' => '%S',
        'u' => ''
    );
    $mysql_format = "";
    $escaping = false;
    for ($i = 0; $i < strlen($php_format); $i++)
    {
        $char = $php_format[$i];
        if ($char === '\\')
        { // PHP date format escaping character
            $i++;
            if ($escaping)
                $mysql_format .= $php_format[$i];
            else
                $mysql_format .= '\'' . $php_format[$i];
            $escaping = true;
        }
        else
        {
            if ($escaping)
            {
                $mysql_format .= "'";
                $escaping = false;
            }
            if (isset($SYMBOLS_MATCHING[$char]))
                $mysql_format .= $SYMBOLS_MATCHING[$char];
            else
                $mysql_format .= $char;
        }
    }
    return $mysql_format;
}

/**
 * Default function for intilize CkEditor on any input Field
 * Parameters : $path : ckfinder Path for upload any file
 * Return : Editor Appereance on given field
 * author :   Ashwin Soni
 * created date : 28-10-2014
 */
function editor($path)
{
    //Get CI instance
    $CI = & get_instance();

    //Loading Library For Ckeditor
    $CI->load->library('ckeditor');
    $CI->load->library('ckFinder');

    //Configure base path of ckeditor folder and JS files
    $CI->ckeditor->basePath = base_url('assets/admin/js') . '/ckeditor/';

    //Configure toolbar of CKeditor
    $CI->ckeditor->config['toolbar'] = array(
        array('Bold', 'Italic', 'Underline', 'NumberedList', 'BulletedList'),
        array('Source'),
        array('Link', 'Unlink', 'Anchor')
    ); //'Full';
    //Configure language of CKeditor
    $CI->ckeditor->config['language'] = 'en';

    //Configure width of CKeditor
    $CI->ckeditor->config['width'] = '485';
    $CI->ckeditor->config['enterMode'] = 'CKEDITOR.ENTER_BR';

    //configure ckfinder with ckeditor config
    $CI->ckfinder->SetupCKEditor($CI->ckeditor, $path);
}

/**
 * Function for get full path of image if file name given
 * @param : 
 *  $type = folder_name From which folder you want to get image
 *  $file_name='Filename/Imagename'
 *  $width= Image width for get image from that folder
 *  $height= Image width for get image from that folder
 * @return : Full image path
 */
function get_image_path($type = '', $file_name = '', $width = ADMIN_THUMB_WIDTH, $height = ADMIN_THUMB_HEIGHT, $MediaTypeId = '')
{

    if ($file_name != '')
    {
        switch ($type)
        {
            case $type:
                if ($MediaTypeId == VIDEO_MEDIA_TYPE_ID)
                {
                    if (strtolower(IMAGE_SERVER) == 'remote')
                    {                        
                        if ($file_name != "")
                        {

                            if ($width != '' && $height != '')
                            {
                                $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $width . 'x' . $height . '/' . $file_name;
                            }
                            else
                            {
                                $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $file_name;
                            }
                        }
                        else
                        {
                            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'video_thumb.jpg';
                        }
                        unset($s3);
                    }
                    else if (file_exists(DOC_PATH . SUBDIR . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $file_name))
                    {
                        if ($width != '' && $height != '' && file_exists(DOC_PATH . SUBDIR . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $width . 'x' . $height . '/' . $file_name))
                        {
                            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $width . 'x' . $height . '/' . $file_name;
                        }
                        else
                        {
                            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $file_name;
                        }
                    }
                    else
                    {
                        $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'video_thumb.jpg';
                    }
                }
                else if ($MediaTypeId == YOUTUBE_MEDIA_TYPE_ID)
                {
                    $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'youtube_thumb.jpg';
                }
                else
                {
                    if (strtolower(IMAGE_SERVER) == 'remote')
                    {                        
                        if ($file_name != "")
                        {

                            if ($width != '' && $height != '')
                            {
                                $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $width . 'x' . $height . '/' . $file_name;
                            }
                            else
                            {
                                $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $file_name;
                            }
                        }
                        else
                        {
                            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'blank-profile.jpg';
                        }
                        unset($s3);
                    }
                    else if (file_exists(DOC_PATH . SUBDIR . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $file_name))
                    {
                        if ($width != '' && $height != '' && file_exists(DOC_PATH . SUBDIR . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $width . 'x' . $height . '/' . $file_name))
                        {
                            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $width . 'x' . $height . '/' . $file_name;
                        }
                        else
                        {
                            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $type . '/' . $file_name;
                        }
                    }
                    else
                    {
                        $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'blank-profile.jpg';
                    }
                }
                break;

            default:
                if ($MediaTypeId == VIDEO_MEDIA_TYPE_ID)
                {
                    $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'video_thumb.jpg';
                }
                else if ($MediaTypeId == YOUTUBE_MEDIA_TYPE_ID)
                {
                    $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'youtube_thumb.jpg';
                }
                else
                {
                    $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . $file_name;
                }
        }
    }
    else
    {
        if ($MediaTypeId == VIDEO_MEDIA_TYPE_ID)
        {
            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'video_thumb.jpg';
        }
        else if ($MediaTypeId == YOUTUBE_MEDIA_TYPE_ID)
        {
            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'youtube_thumb.jpg';
        }
        else
        {
            $full_path = IMAGE_SERVER_PATH . PATH_IMG_UPLOAD_FOLDER . 'blank-profile.jpg';
        }
    }
    return $full_path;
}

if (!function_exists('formatSizeUnits'))
{

    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = NumberFormat($bytes / 1073741824) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = NumberFormat($bytes / 1048576) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = NumberFormat($bytes / 1024) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = NumberFormat($bytes) . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = NumberFormat($bytes) . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

}

if (!function_exists('NumberFormat'))
{

    function NumberFormat($number)
    {
        $CI = & get_instance();
        /* Load Global settings */
        $global_settings = $CI->config->item("global_settings");
        if (isset($global_settings['decimal_places']))
            $decimal_places = $global_settings['decimal_places'];
        else
            $decimal_places = 2;

        $newnumber = number_format($number, $decimal_places);
        return $newnumber;
    }

}

if (!function_exists('dateDiff'))
{

    function dateDiff($start, $end)
    {
        $start_ts = strtotime($start);
        $end_ts = strtotime($end);
        $diff = $end_ts - $start_ts;
        return round($diff / 86400) + 1;
    }

}

if (!function_exists('getOffset'))
{

    function getOffset($PageNo, $Limit)
    {
        if (empty($PageNo))
        {
            $PageNo = 1;
        }
        $offset = ($PageNo - 1) * $Limit;
        return $offset;
    }

}

if (!function_exists('get_data'))
{

    function get_data($field, $table, $where, $row = '0', $orderBy = '')
    {

        $CI = & get_instance();
        $CI->db->select($field);
        $CI->db->from($table);
        if ($where)
            $CI->db->where($where);
        if ($orderBy)
            $CI->db->order_by($orderBy);

        if ($row == 1)
        {
            $CI->db->limit(1);
        }

        $sql = $CI->db->get();

        if ($sql->num_rows())
        {
            $result = $sql->result();
            if ($row == 1)
            {
                return $result[0];
            }
            else
                return $result;
        }
        else
        {
            return false;
        }
    }

}

if (!function_exists('dynamic_values'))
{

    function dynamic_values($array, $field, $minimum, $maximum)
    {
        if (!empty($array))
        {
            $cnt = array_column($array, $field);

            $cnt_min = min($cnt);
            $cnt_max = max($cnt);

            foreach ($array as &$item)
            {
                if($cnt_max == $cnt_min)
                {
                    $item['height'] = $minimum;
                }
                else
                {
                    $item['height'] = $minimum + floor(($item[$field] - $cnt_min) * ($maximum - $minimum) / ($cnt_max - $cnt_min));
                }
            }
        }

        return $array;
    }

}

/**
 *
 * @param   DateFormat
 * @return  Current UTC Date       
 */
if (!function_exists('getCurrentDate')) {

    function getCurrentDate($dateFormat, $timediff = 0) {
        $CI = & get_instance();
        $CI->load->helper('date');
        $now = now();
        if ($timediff) {
            $now = $now - (24 * 60 * 60 * $timediff);
        }
        return mdate($dateFormat, $now);
    }

}
