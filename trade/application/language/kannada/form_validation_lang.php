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

$lang['form_validation_required'] = '{field} ಕ್ಷೇತ್ರ ಅಗತ್ಯವಿದೆ.';
$lang['form_validation_isset'] = '{field} ಕ್ಷೇತ್ರವು ಮೌಲ್ಯವನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_valid_email'] = '{field} ಕ್ಷೇತ್ರವು ಮಾನ್ಯವಾದ ಇಮೇಲ್ ವಿಳಾಸವನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_valid_emails'] = '{field} ಕ್ಷೇತ್ರವು ಎಲ್ಲಾ ಮಾನ್ಯ ಇಮೇಲ್ ವಿಳಾಸಗಳನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_valid_url'] = '{field} ಕ್ಷೇತ್ರವು ಮಾನ್ಯವಾದ URL ಅನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_valid_ip'] = '{field} ಕ್ಷೇತ್ರವು ಮಾನ್ಯವಾದ IP ಅನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_min_length'] = '{field} ಕ್ಷೇತ್ರವು ಕನಿಷ್ಟ {param} ಅಕ್ಷರಗಳ ಉದ್ದವನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_max_length'] = '{field} ಕ್ಷೇತ್ರವು {param} ಅಕ್ಷರಗಳ ಉದ್ದವನ್ನು ಮೀರುವಂತಿಲ್ಲ.';
$lang['form_validation_exact_length'] = '{field} ಕ್ಷೇತ್ರವು ನಿಖರವಾಗಿ {param} ಅಕ್ಷರಗಳ ಉದ್ದವನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_alpha'] = '{field} ಕ್ಷೇತ್ರವು ವರ್ಣಮಾಲೆಯ ಅಕ್ಷರಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬಹುದು.';
$lang['form_validation_alpha_numeric'] = '{field} ಕ್ಷೇತ್ರವು ಆಲ್ಫಾ-ಸಂಖ್ಯೆಯ ಅಕ್ಷರಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬಹುದು.';
$lang['form_validation_alpha_numeric_spaces'] = '{field} ಕ್ಷೇತ್ರವು ಕೇವಲ ಆಲ್ಫಾ-ಸಂಖ್ಯೆಯ ಅಕ್ಷರಗಳು ಮತ್ತು ಸ್ಥಳಗಳನ್ನು ಹೊಂದಿರಬಹುದು.';
$lang['form_validation_alpha_dash'] = '{field} ಕ್ಷೇತ್ರವು ಆಲ್ಫಾ-ಸಂಖ್ಯೆಯ ಅಕ್ಷರಗಳು, ಅಂಡರ್‌ಸ್ಕೋರ್‌ಗಳು ಮತ್ತು ಡ್ಯಾಶ್‌ಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬಹುದು.';
$lang['form_validation_numeric'] = '{field} ಕ್ಷೇತ್ರವು ಸಂಖ್ಯೆಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_is_numeric'] = '{field} ಕ್ಷೇತ್ರವು ಸಂಖ್ಯಾ ಅಕ್ಷರಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_integer'] = '{field} ಕ್ಷೇತ್ರವು ಪೂರ್ಣಾಂಕವನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_regex_match'] = '{field} ಕ್ಷೇತ್ರವು ಸರಿಯಾದ ಸ್ವರೂಪದಲ್ಲಿಲ್ಲ.';
$lang['form_validation_matches'] = '{field} ಕ್ಷೇತ್ರವು {param} ಕ್ಷೇತ್ರಕ್ಕೆ ಹೊಂದಿಕೆಯಾಗುವುದಿಲ್ಲ.';
$lang['form_validation_differs'] = '{field} ಕ್ಷೇತ್ರವು {param} ಕ್ಷೇತ್ರದಿಂದ ಭಿನ್ನವಾಗಿರಬೇಕು.';
$lang['form_validation_is_unique'] = '{field} ಕ್ಷೇತ್ರವು ವಿಶಿಷ್ಟ ಮೌಲ್ಯವನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_is_natural'] = '{field} ಕ್ಷೇತ್ರವು ಅಂಕೆಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_is_natural_no_zero'] = '{field} ಕ್ಷೇತ್ರವು ಅಂಕೆಗಳನ್ನು ಮಾತ್ರ ಹೊಂದಿರಬೇಕು ಮತ್ತು ಶೂನ್ಯಕ್ಕಿಂತ ಹೆಚ್ಚಾಗಿರಬೇಕು.';
$lang['form_validation_decimal'] = '{field} ಕ್ಷೇತ್ರವು ದಶಮಾಂಶ ಸಂಖ್ಯೆಯನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_less_than'] = '{field} ಕ್ಷೇತ್ರವು {param} ಗಿಂತ ಕಡಿಮೆ ಸಂಖ್ಯೆಯನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_less_than_equal_to'] = '{field} ಕ್ಷೇತ್ರವು {param} ಗಿಂತ ಕಡಿಮೆ ಅಥವಾ ಸಮಾನವಾದ ಸಂಖ್ಯೆಯನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_greater_than'] = '{field} ಕ್ಷೇತ್ರವು {param} ಗಿಂತ ಹೆಚ್ಚಿನ ಸಂಖ್ಯೆಯನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_greater_than_equal_to'] = '{field} ಕ್ಷೇತ್ರವು {param} ಗಿಂತ ಹೆಚ್ಚಿನ ಅಥವಾ ಸಮಾನವಾದ ಸಂಖ್ಯೆಯನ್ನು ಹೊಂದಿರಬೇಕು.';
$lang['form_validation_error_message_not_set'] = 'ನಿಮ್ಮ ಕ್ಷೇತ್ರದ ಹೆಸರು {field} ಗೆ ಸಂಬಂಧಿಸಿದ ದೋಷ ಸಂದೇಶವನ್ನು ಪ್ರವೇಶಿಸಲು ಸಾಧ್ಯವಿಲ್ಲ.';
$lang['form_validation_in_list'] = '{field} ಕ್ಷೇತ್ರವು ಇವುಗಳಲ್ಲಿ ಒಂದಾಗಿರಬೇಕು: {param}.';