<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['form_validation_required'] = '{फ़ील्ड} फ़ील्ड आवश्यक है।';
$lang['form_validation_isset'] = '{फ़ील्ड} फ़ील्ड का एक मान होना चाहिए।';
$lang['form_validation_valid_email'] = '{फ़ील्ड} फ़ील्ड में एक वैध ईमेल पता होना चाहिए।';
$lang['form_validation_valid_emails'] = '{फ़ील्ड} फ़ील्ड में सभी मान्य ईमेल पते होने चाहिए।';
$lang['form_validation_valid_url'] = '{फ़ील्ड} फ़ील्ड में एक वैध यूआरएल होना चाहिए।';
$lang['form_validation_valid_ip'] = '{फ़ील्ड} फ़ील्ड में एक वैध आईपी होना चाहिए।';
$lang['form_validation_min_length'] = '{फ़ील्ड} फ़ील्ड की लंबाई कम से कम {param} वर्ण होनी चाहिए।';
$lang['form_validation_max_length'] = '{फ़ील्ड} फ़ील्ड की लंबाई {param} वर्णों से अधिक नहीं हो सकती।';
$lang['form_validation_exact_length'] = '{फ़ील्ड} फ़ील्ड की लंबाई बिल्कुल {param} वर्ण होनी चाहिए।';
$lang['form_validation_alpha'] = '{फ़ील्ड} फ़ील्ड में केवल वर्णमाला वर्ण हो सकते हैं।';
$lang['form_validation_alpha_numeric'] = '{फ़ील्ड} फ़ील्ड में केवल अल्फ़ा-न्यूमेरिक वर्ण हो सकते हैं।';
$lang['form_validation_alpha_numeric_spaces'] = '{फ़ील्ड} फ़ील्ड में केवल अल्फ़ा-न्यूमेरिक वर्ण और रिक्त स्थान हो सकते हैं।';
$lang['form_validation_alpha_dash'] = '{फ़ील्ड} फ़ील्ड में केवल अल्फ़ा-न्यूमेरिक वर्ण, अंडरस्कोर और डैश हो सकते हैं।';
$lang['form_validation_numeric'] = '{फ़ील्ड} फ़ील्ड में केवल संख्याएँ होनी चाहिए।';
$lang['form_validation_is_numeric'] = '{फ़ील्ड} फ़ील्ड में केवल संख्यात्मक वर्ण होने चाहिए।';
$lang['form_validation_integer'] = '{फ़ील्ड} फ़ील्ड में एक पूर्णांक होना चाहिए।';
$lang['form_validation_regex_match'] = '{फ़ील्ड} फ़ील्ड सही प्रारूप में नहीं है।';
$lang['form_validation_matches'] = '{फ़ील्ड} फ़ील्ड {param} फ़ील्ड से मेल नहीं खाती।';
$lang['form_validation_differs'] = '{फ़ील्ड} फ़ील्ड {param} फ़ील्ड से भिन्न होनी चाहिए।';
$lang['form_validation_is_unique'] = '{फ़ील्ड} फ़ील्ड में एक अद्वितीय मान होना चाहिए।';
$lang['form_validation_is_प्राकृतिक'] = '{फ़ील्ड} फ़ील्ड में केवल अंक होने चाहिए।';
$lang['form_validation_is_प्राकृतिक_no_zero'] = '{फ़ील्ड} फ़ील्ड में केवल अंक होने चाहिए और शून्य से अधिक होना चाहिए।';
$lang['form_validation_decimal'] = '{फ़ील्ड} फ़ील्ड में एक दशमलव संख्या होनी चाहिए।';
$lang['form_validation_less_than'] = '{फ़ील्ड} फ़ील्ड में {param} से कम संख्या होनी चाहिए।';
$lang['form_validation_less_than_equal_to'] = '{फ़ील्ड} फ़ील्ड में {param} से कम या उसके बराबर संख्या होनी चाहिए।';
$lang['form_validation_greater_than'] = '{फ़ील्ड} फ़ील्ड में {param} से बड़ी संख्या होनी चाहिए।';
$lang['form_validation_greater_than_equal_to'] = '{फ़ील्ड} फ़ील्ड में {param} से अधिक या उसके बराबर संख्या होनी चाहिए।';
$lang['form_validation_error_message_not_set'] = 'आपके फ़ील्ड नाम {फ़ील्ड} से संबंधित त्रुटि संदेश तक पहुंचने में असमर्थ।';
$lang['form_validation_in_list'] = '{फ़ील्ड} फ़ील्ड इनमें से एक होनी चाहिए: {param}.';