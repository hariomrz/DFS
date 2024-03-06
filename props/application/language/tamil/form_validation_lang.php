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

$lang['form_validation_required'] ="{field} field தேவை.";
$lang['form_validation_isset']        ="{field} புலத்திற்கு ஒரு மதிப்பு இருக்க வேண்டும்.";
$lang['form_validation_valid_email']   ="{field} புலத்தில் சரியான மின்னஞ்சல் முகவரியைக் கொண்டிருக்க வேண்டும்.";
$lang['form_validation_valid_emails'] ="{field} புலத்தில் அனைத்து செல்லுபடியாகும் மின்னஞ்சல் முகவரிகளும் இருக்க வேண்டும்.";
$lang['form_validation_valid_url'] ="{field} புலத்தில் செல்லுபடியாகும் URL இருக்க வேண்டும்.";
$lang['form_validation_valid_ip'] ="{field} புலத்தில் செல்லுபடியாகும் ஐபி இருக்க வேண்டும்.";
$lang['form_validation_min_length']   ="{field} field குறைந்தபட்சம் {பரம்} எழுத்துக்களாக இருக்க வேண்டும்";
$lang['form_validation_max_length']   ="{field} field {param} எழுத்துக்களை நீளத்திற்கு மிகாமல் இருக்க வேண்டும்.";
$lang['form_validation_exact_length']   ="{field} field சரியாக {பரம்} எழுத்துக்கள் நீளமாக இருக்க வேண்டும்";
$lang['form_validation_alpha']  ="{field} புலத்தில் அகரவரிசை எழுத்துக்கள் மட்டுமே இருக்கலாம்.";
$lang['form_validation_alpha_numeric'] ="{field} புலத்தில் ஆல்பா-எண் எழுத்துக்கள் மட்டுமே இருக்கலாம்.";
$lang['form_validation_alpha_numeric_spaces']="{field} புலத்தில் ஆல்பா-எண் எழுத்துக்கள் மற்றும் இடைவெளிகள் மட்டுமே இருக்கலாம்";
$lang['form_validation_alpha_dash']  ="{field} புலத்தில் ஆல்பா-எண் எழுத்துக்கள் மட்டுமே இருக்கலாம், அடிக்கோடிட்டுக் காட்டுகின்றன";
$lang['form_validation_numeric']       ="{field} புலத்தில் எண்கள் மட்டுமே இருக்க வேண்டும்.";
$lang['form_validation_is_numeric']   ="{field} புலத்தில் எண் எழுத்துக்கள் மட்டுமே இருக்க வேண்டும்.";
$lang['form_validation_integer']   ="{field} புலத்தில் ஒரு முழு எண் இருக்க வேண்டும்.";
$lang['form_validation_regex_match']     ="{field} field சரியான வடிவத்தில் இல்லை.";
$lang['form_validation_matches']  ="{field} field {பரம்} புலத்துடன் பொருந்தவில்லை.";
$lang['form_validation_differs']   ="{field} field {பரம்} புலத்திலிருந்து வேறுபட வேண்டும்.";
$lang['form_validation_is_unique']   ="{field} field ஒரு தனித்துவமான மதிப்பைக் கொண்டிருக்க வேண்டும்.";
$lang['form_validation_is_natural']   ="{field} புலத்தில் இலக்கங்கள் மட்டுமே இருக்க வேண்டும்.";
$lang['form_validation_is_natural_no_zero']="{field} field இலக்கங்களை மட்டுமே கொண்டிருக்க வேண்டும் மற்றும் விட அதிகமாக இருக்க வேண்டும்";
$lang['form_validation_decimal'] ="{field} புலத்தில் ஒரு தசம எண்ணும் இருக்க வேண்டும்.";
$lang['form_validation_less_than'] ="{field} புலத்தில் {பரம்} ஐ விட குறைவான எண்ணைக் கொண்டிருக்க வேண்டும்.";
$lang['form_validation_less_than_equal_to']="{field} புலத்தில் குறைவான அல்லது சமமான எண்ணை கொண்டிருக்க வேண்டும்";
$lang['form_validation_greater_than']      ="{field} புலத்தில் {பரம்} ஐ விட அதிகமான எண் இருக்க வேண்டும்.";
$lang['form_validation_greater_than_equal_to']="{field} புலத்தில் ஒரு எண்ணை விட அல்லது சமமான எண்ணிக்கை இருக்க வேண்டும்";
$lang['form_validation_error_message_not_set']="உங்கள் புலப் பெயருடன் தொடர்புடைய பிழை செய்தியை அணுக முடியவில்லை {field}.";
$lang['form_validation_in_list'] ="{field} field ஒன்றாக இருக்க வேண்டும்: {param}.";