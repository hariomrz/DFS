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

$lang['form_validation_required'] = '{field} 欄位為必填項。';
$lang['form_validation_isset'] = '{field} 欄位必須有一個值。';
$lang['form_validation_valid_email'] = '{field} 欄位必須包含有效的電子郵件地址。';
$lang['form_validation_valid_emails'] = '{field} 欄位必須包含所有有效的電子郵件地址。';
$lang['form_validation_valid_url'] = '{field} 欄位必須包含有效的 URL。';
$lang['form_validation_valid_ip'] = '{field} 欄位必須包含有效的 IP。';
$lang['form_validation_min_length'] = '{field} 欄位的長度必須至少為 {param} 個字元。';
$lang['form_validation_max_length'] = '{field} 欄位的長度不能超過 {param} 個字元。';
$lang['form_validation_exact_length'] = '{field} 欄位的長度必須剛好為 {param} 個字元。';
$lang['form_validation_alpha'] = '{field} 欄位只能包含字母字元。';
$lang['form_validation_alpha_numeric'] = '{field} 欄位只能包含字母數字字元。';
$lang['form_validation_alpha_numeric_spaces'] = '{field} 欄位只能包含字母數字字元和空格。';
$lang['form_validation_alpha_dash'] = '{field} 欄位只能包含字母數字字元、底線和破折號。';
$lang['form_validation_numeric'] = '{field} 欄位只能包含數字。';
$lang['form_validation_is_numeric'] = '{field} 欄位只能包含數字字元。';
$lang['form_validation_integer'] = '{field} 欄位必須包含一個整數。';
$lang['form_validation_regex_match'] = '{field} 欄位的格式不正確。';
$lang['form_validation_matches'] = '{field} 欄位與 {param} 欄位不符。';
$lang['form_validation_differs'] = '{field} 欄位必須與 {param} 欄位不同。';
$lang['form_validation_is_unique'] = '{field} 欄位必須包含唯一值。';
$lang['form_validation_is_natural'] = '{field} 欄位只能包含數字。';
$lang['form_validation_is_natural_no_zero'] = '{field} 欄位只能包含數字且必須大於零。';
$lang['form_validation_decimal'] = '{field} 欄位必須包含十進位數。';
$lang['form_validation_less_than'] = '{field} 欄位必須包含小於 {param} 的數字。';
$lang['form_validation_less_than_equal_to'] = '{field} 欄位必須包含小於或等於 {param} 的數字。';
$lang['form_validation_greater_than'] = '{field} 欄位必須包含大於 {param} 的數字。';
$lang['form_validation_greater_than_equal_to'] = '{field} 欄位必須包含大於或等於 {param} 的數字。';
$lang['form_validation_error_message_not_set'] = '無法存取與您的欄位名稱 {field} 對應的錯誤訊息。';
$lang['form_validation_in_list'] = '{field} 欄位必須是以下之一：{param}。';