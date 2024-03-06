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
$lang['form_validation_valid_email'] = 'ฟิลด์ {field} ต้องมีที่อยู่อีเมลที่ถูกต้อง';
$lang['form_validation_valid_emails'] = 'ฟิลด์ {field} ต้องมีที่อยู่อีเมลที่ถูกต้องทั้งหมด';
$lang['form_validation_valid_url'] = 'ฟิลด์ {field} ต้องมี URL ที่ถูกต้อง';
$lang['form_validation_valid_ip'] = 'ฟิลด์ {field} ต้องมี IP ที่ถูกต้อง';
$lang['form_validation_min_length'] = 'ฟิลด์ {field} ต้องมีความยาวอย่างน้อย {param} อักขระ';
$lang['form_validation_max_length'] = 'ฟิลด์ {field} ต้องมีความยาวไม่เกิน {param} อักขระ';
$lang['form_validation_exact_length'] = 'ฟิลด์ {field} ต้องมีความยาวเท่ากับ {param} อักขระ';
$lang['form_validation_alpha'] = 'ฟิลด์ {field} ต้องมีเฉพาะตัวอักษรเท่านั้น';
$lang['form_validation_alpha_numeric'] = 'ฟิลด์ {field} ต้องมีเฉพาะอักขระที่เป็นตัวอักษรและตัวเลขเท่านั้น';
$lang['form_validation_alpha_numeric_spaces'] = 'ฟิลด์ {field} ต้องมีเฉพาะตัวอักษรและช่องว่างเท่านั้น';
$lang['form_validation_alpha_dash'] = 'ฟิลด์ {field} ต้องมีเฉพาะตัวอักษรและตัวเลขขีดล่างและขีดกลาง';
$lang['form_validation_numeric'] = 'ฟิลด์ {field} ต้องมีตัวเลขเท่านั้น';
$lang['form_validation_is_numeric'] = 'ฟิลด์ {field} ต้องมีอักขระตัวเลขเท่านั้น';
$lang['form_validation_integer'] = 'ฟิลด์ {field} ต้องมีจำนวนเต็ม';
$lang['form_validation_regex_match'] = 'ฟิลด์ {field} ไม่อยู่ในรูปแบบที่ถูกต้อง';
$lang['form_validation_matches'] = 'ฟิลด์ {field} ไม่ตรงกับฟิลด์ {param}';
$lang['form_validation_differs'] = 'ฟิลด์ {field} ต้องแตกต่างจากฟิลด์ {param}';
$lang['form_validation_is_unique'] = 'ฟิลด์ {field} ต้องมีค่าที่ไม่ซ้ำกัน';
$lang['form_validation_is_natural'] = 'ฟิลด์ {field} ต้องมีตัวเลขเท่านั้น';
$lang['form_validation_is_natural_no_zero'] = 'ฟิลด์ {field} ต้องมีเฉพาะตัวเลขและต้องมากกว่าศูนย์';
$lang['form_validation_decimal'] = 'ฟิลด์ {field} ต้องมีเลขฐานสิบ';
$lang['form_validation_less_than'] = 'ฟิลด์ {field} ต้องมีตัวเลขน้อยกว่า {param}';
$lang['form_validation_less_than_equal_to'] = 'ฟิลด์ {field} ต้องมีตัวเลขน้อยกว่าหรือเท่ากับ {param}';
$lang['form_validation_greater_than'] = 'ฟิลด์ {field} ต้องมีตัวเลขที่มากกว่า {param}';
$lang['form_validation_greater_than_equal_to'] = 'ฟิลด์ {field} ต้องมีตัวเลขที่มากกว่าหรือเท่ากับ {param}';
$lang['form_validation_error_message_not_set'] = 'ไม่สามารถเข้าถึงข้อความแสดงข้อผิดพลาดที่เกี่ยวข้องกับชื่อฟิลด์ของคุณ {field}';
$lang['form_validation_in_list'] = 'ฟิลด์ {field} ต้องเป็นหนึ่งใน: {param}';