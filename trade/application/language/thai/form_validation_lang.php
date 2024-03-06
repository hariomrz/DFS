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

$lang['form_validation_required'] = 'ต้องระบุฟิลด์ {field}';
$lang['form_validation_isset'] = 'ฟิลด์ {field} ต้องมีค่า';
$lang['form_validation_valid_email'] = 'ช่อง {field} ต้องมีที่อยู่อีเมลที่ถูกต้อง';
$lang['form_validation_valid_emails'] = 'ช่อง {field} ต้องมีที่อยู่อีเมลที่ถูกต้องทั้งหมด';
$lang['form_validation_valid_url'] = 'ช่อง {field} ต้องมี URL ที่ถูกต้อง';
$lang['form_validation_valid_ip'] = 'ช่อง {field} ต้องมี IP ที่ถูกต้อง';
$lang['form_validation_min_length'] = 'ฟิลด์ {field} ต้องมีความยาวอย่างน้อย {param} ตัวอักษร';
$lang['form_validation_max_length'] = 'ฟิลด์ {field} ต้องมีความยาวไม่เกิน {param} ตัวอักษร';
$lang['form_validation_exact_length'] = 'ฟิลด์ {field} จะต้องมีความยาว {param} ตัวอักษรพอดี';
$lang['form_validation_alpha'] = 'ฟิลด์ {field} อาจมีเฉพาะตัวอักษรเท่านั้น';
$lang['form_validation_alpha_numeric'] = 'ช่อง {field} ต้องมีเฉพาะตัวอักษรและตัวเลขเท่านั้น';
$lang['form_validation_alpha_numeric_spaces'] = 'ช่อง {field} ใช้ได้เฉพาะตัวอักษรและตัวเลขและการเว้นวรรคเท่านั้น';
$lang['form_validation_alpha_dash'] = 'ช่อง {field} ใช้ได้เฉพาะตัวอักษรและตัวเลข ขีดล่าง และขีดกลางเท่านั้น';
$lang['form_validation_numeric'] = 'ช่อง {field} ต้องมีเฉพาะตัวเลขเท่านั้น';
$lang['form_validation_is_numeric'] = 'ช่อง {field} ต้องมีอักขระที่เป็นตัวเลขเท่านั้น';
$lang['form_validation_integer'] = 'ช่อง {field} จะต้องมีจำนวนเต็ม';
$lang['form_validation_regex_match'] = 'ฟิลด์ {field} ไม่ได้อยู่ในรูปแบบที่ถูกต้อง';
$lang['form_validation_matches'] = 'ฟิลด์ {field} ไม่ตรงกับฟิลด์ {param}';
$lang['form_validation_differs'] = 'ฟิลด์ {field} ต้องแตกต่างจากฟิลด์ {param}';
$lang['form_validation_is_unique'] = 'ช่อง {field} ต้องมีค่าไม่ซ้ำกัน';
$lang['form_validation_is_natural'] = 'ช่อง {field} ต้องมีเฉพาะตัวเลขเท่านั้น';
$lang['form_validation_is_natural_no_zero'] = 'ช่อง {field} ต้องมีเฉพาะตัวเลขและต้องมากกว่าศูนย์';
$lang['form_validation_decimal'] = 'ช่อง {field} ต้องมีเลขทศนิยม';
$lang['form_validation_less_than'] = 'ช่อง {field} ต้องมีตัวเลขน้อยกว่า {param}';
$lang['form_validation_less_than_equal_to'] = 'ช่อง {field} ต้องมีตัวเลขน้อยกว่าหรือเท่ากับ {param}';
$lang['form_validation_greater_than'] = 'ช่อง {field} ต้องมีตัวเลขมากกว่า {param}';
$lang['form_validation_greater_than_equal_to'] = 'ช่อง {field} ต้องมีตัวเลขมากกว่าหรือเท่ากับ {param}';
$lang['form_validation_error_message_not_set'] = 'ไม่สามารถเข้าถึงข้อความแสดงข้อผิดพลาดที่ตรงกับชื่อฟิลด์ของคุณ {field}';
$lang['form_validation_in_list'] = 'ช่อง {field} จะต้องเป็นหนึ่งใน: {param}.';