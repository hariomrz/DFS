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

$lang['form_validation_required']              = '{field} துறையில் தேவைப்படுகிறது.';
$lang['form_validation_isset']                 = '{field} துறையில் ஒரு மதிப்பு வேண்டும்.';
$lang['form_validation_valid_email']           = '{field} துறையில் ஒரு செல்லுபடியாகும் மின்னஞ்சல் முகவரி வேண்டும்.';
$lang['form_validation_valid_emails']          = '{field} துறையில் அனைத்து சரியான மின்னஞ்சல் முகவரிகள் கொண்டிருக்க வேண்டும்.';
$lang['form_validation_valid_url']             = '{field} புலத்தில் செல்லுபடியாகும் URL இருக்க வேண்டும்.';
$lang['form_validation_valid_ip']              = '{field} துறையில் சரியான IP கொண்டிருக்க வேண்டும்.';
$lang['form_validation_min_length']            = '{field} துறையில் நீளம் குறைந்தது {param} எழுத்துகள் இருக்க வேண்டும்.';
$lang['form_validation_max_length']            = '{field} துறையில் நீளம் {param} எழுத்துக்களுக்கு மேல் இருக்கக்கூடாது.';
$lang['form_validation_exact_length']          = '{field} துறையில் நீளம் சரியாக {param} எழுத்துகள் இருக்க வேண்டும்.';
$lang['form_validation_alpha']                 = '{field} துறையில் மட்டும் அகரவரிசை எழுத்துகள் கொண்டிருக்கலாம்.';
$lang['form_validation_alpha_numeric']         = '{field} துறையில் அகரவரிசை எழுத்துக்குறிகள்.';
$lang['form_validation_alpha_numeric_spaces']  = '{field} துறையில் மட்டும் ஆல்பா எண் எழுத்துகள் மற்றும் இடைவெளிகள் கொண்டிருக்கலாம்.';
$lang['form_validation_alpha_dash']            = '{field} துறையில் மட்டும் ஆல்பா எண் எழுத்துகள், அடிக்கோடு, கோடுகளும் கொண்டிருக்கலாம்.';
$lang['form_validation_numeric']               = '{field} துறையில் எண்கள் மட்டுமே இருக்க வேண்டும்.';
$lang['form_validation_is_numeric']            = '{field} துறையில் எண் எழுத்துகள் இருக்க வேண்டும்.';
$lang['form_validation_integer']               = '{field} துறையில் ஒரு முழு கொண்டிருக்க வேண்டும்.';
$lang['form_validation_regex_match']           = '{field} துறையில் சரியான வடிவத்தில் இல்லை';
$lang['form_validation_matches']               = '{field} துறையில் {param} புலம் பொருந்தவில்லை.';
$lang['form_validation_differs']               = '{field} துறையில் {param} துறையில் இருந்து வேறுபட்டே ஆகவேண்டும்.';
$lang['form_validation_is_unique']             = '{field} துறையில் ஒரு தனிப்பட்ட மதிப்பு இருக்க வேண்டும்.';
$lang['form_validation_is_natural']            = '{field} புலத்தில் இலக்கங்கள் மட்டுமே இருக்க வேண்டும்.';
$lang['form_validation_is_natural_no_zero']    = '{field} புலத்தில் இலக்கங்கள் மட்டுமே இருக்க வேண்டும் மற்றும் பூஜ்ஜியத்தை விட அதிகமாக இருக்க வேண்டும்.';
$lang['form_validation_decimal']               = '{field} துறையில் ஒரு தசம எண்ணை உள்ளடக்கியதாக இருக்க வேண்டும்.';
$lang['form_validation_less_than']             = '{field} துறையில் குறைவாக ஒரு எண்ணை உள்ளடக்கியதாக இருக்க வேண்டும் {param}.';
$lang['form_validation_less_than_equal_to']    = '{field} துறையில் குறைவாக ஒரு எண்ணை உள்ளடக்கியதாக அல்லது {param} க்கு சமமானதாக இருக்கும்.';
$lang['form_validation_greater_than']          = '{field} துறையில் விட அதிகமான எண்ணாக {param} கொண்டிருக்க வேண்டும்.';
$lang['form_validation_greater_than_equal_to'] = '{field} துறையில் விட அதிகமான எண்ணாக கொண்டிருக்கும் அல்லது {param} க்கு சமமானதாக இருக்கும்.';
$lang['form_validation_error_message_not_set'] = 'உங்கள் துறையில் பெயரை {field} தொடர்புடைய ஒரு பிழை செய்தி அணுக முடியவில்லை.';
$lang['form_validation_in_list']               = '{param}: {field} துறையில் ஒன்றாக இருக்க வேண்டும்.';