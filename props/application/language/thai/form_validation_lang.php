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

$lang['form_validation_required'] ="ต้องการfield {field}";
$lang['form_validation_isset']        ="field {field} ต้องมีค่า";
$lang['form_validation_valid_email']   ="field {field} ต้องมีที่อยู่อีเมลที่ถูกต้อง";
$lang['form_validation_valid_emails'] ="field {field} ต้องมีที่อยู่อีเมลที่ถูกต้องทั้งหมด";
$lang['form_validation_valid_url'] ="fieldfield} ต้องมี URL ที่ถูกต้อง";
$lang['form_validation_valid_ip'] ="field {field} ต้องมี IP ที่ถูกต้อง";
$lang['form_validation_min_length']   ="field {field} ต้องมีอย่างน้อย {param} อักขระใน";
$lang['form_validation_max_length']   ="field {field} ไม่เกินความยาวอักขระ {param}";
$lang['form_validation_exact_length']   ="field {field} ต้องเป็นอักขระ {param} ที่มีความยาว";
$lang['form_validation_alpha']  ="field {field} อาจมีอักขระตัวอักษรเท่านั้น";
$lang['form_validation_alpha_numeric'] ="field {field} อาจมีอักขระอัลฟ่า-ตัวเลขเท่านั้น";
$lang['form_validation_alpha_numeric_spaces']="field {field} อาจมีอักขระและช่องว่างอัลฟ่าเท่านั้น";
$lang['form_validation_alpha_dash']  ="field {field} อาจมีเฉพาะอักขระอัลฟ่า-ตัวเลข, ขีดเส้นใต้";
$lang['form_validation_numeric']       ="field {field} ต้องมีเพียงตัวเลขเท่านั้น";
$lang['form_validation_is_numeric']   ="field {field} ต้องมีอักขระตัวเลขเท่านั้น";
$lang['form_validation_integer']   ="fieldfield} ต้องมีจำนวนเต็ม";
$lang['form_validation_regex_match']     ="field {field} ไม่อยู่ในรูปแบบที่ถูกต้อง";
$lang['form_validation_matches']  ="field {field} ไม่ตรงกับfield {param}";
$lang['form_validation_differs']   ="field {field} ต้องแตกต่างจากfield {param}";
$lang['form_validation_is_unique']   ="field {field} ต้องมีค่าที่ไม่ซ้ำกัน";
$lang['form_validation_is_natural']   ="fieldfield} ต้องมีตัวเลขเท่านั้น";
$lang['form_validation_is_natural_no_zero']="field {field} จะต้องมีตัวเลขเท่านั้นและต้องมากกว่า";
$lang['form_validation_decimal'] ="field {field} ต้องมีเลขทศนิยม";
$lang['form_validation_less_than'] ="field {field} ต้องมีตัวเลขน้อยกว่า {param}";
$lang['form_validation_less_than_equal_to']="field {field} ต้องมีตัวเลขน้อยกว่าหรือเท่ากับ";
$lang['form_validation_greater_than']      ="field {field} ต้องมีตัวเลขมากกว่า {param}";
$lang['form_validation_greater_than_equal_to']="field {field} ต้องมีตัวเลขที่มากกว่าหรือเท่ากับ";
$lang['form_validation_error_message_not_set']="ไม่สามารถเข้าถึงข้อความแสดงข้อผิดพลาดที่สอดคล้องกับชื่อfieldของคุณ {field}";
$lang['form_validation_in_list'] ="field {field} ต้องเป็นหนึ่งใน: {param}";