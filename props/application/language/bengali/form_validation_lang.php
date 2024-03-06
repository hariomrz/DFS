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
$lang['form_validation_required'] ='{field} fieldের প্রয়োজন। ';
$lang['form_validation_isset']        ='{field} fieldের অবশ্যই একটি মান থাকতে হবে। ';
$lang['form_validation_valid_email']   ='{field} fieldটিতে অবশ্যই একটি বৈধ ইমেল ঠিকানা থাকতে হবে ';
$lang['form_validation_valid_emails'] ='{field} fieldটিতে অবশ্যই সমস্ত বৈধ ইমেল ঠিকানা থাকতে হবে';
$lang['form_validation_valid_url'] ='{field} fieldটিতে অবশ্যই একটি বৈধ url থাকতে হবে';
$lang['form_validation_valid_ip'] ='{field} fieldটিতে অবশ্যই একটি বৈধ আইপি থাকতে হবে ';
$lang['form_validation_min_length']   ='{field} fieldটি অবশ্যই কমপক্ষে {param} অক্ষর হতে হবে';
$lang['form_validation_max_length']   ='{field} fieldটি দৈর্ঘ্যের {param} অক্ষর অতিক্রম করতে পারে না ';
$lang['form_validation_exact_length']   ='{field} fieldটি অবশ্যই দৈর্ঘ্যের {param} অক্ষর হতে হবে';
$lang['form_validation_alpha']  ='{field} fieldটিতে কেবল বর্ণানুক্রমিক অক্ষর থাকতে পারে ';
$lang['form_validation_alpha_numeric'] ='{field} fieldটিতে কেবল আলফা-সংখ্যার অক্ষর থাকতে পারে ';
$lang['form_validation_alpha_numeric_spaces']='{field} fieldটিতে কেবল আলফা-সংখ্যার অক্ষর এবং স্পেস থাকতে পারে';
$lang['form_validation_alpha_dash']  ='{field} fieldটিতে কেবল আলফা-সংখ্যার অক্ষর, আন্ডারস্কোর থাকতে পারে';
$lang['form_validation_numeric']       ='{field} fieldটিতে অবশ্যই কেবল সংখ্যা থাকতে হবে ';
$lang['form_validation_is_numeric']   ='{field} fieldটিতে অবশ্যই কেবলমাত্র সংখ্যার অক্ষর থাকতে হবে ';
$lang['form_validation_integer']   ='{field} fieldটিতে অবশ্যই একটি পূর্ণসংখ্যা থাকতে হবে ';
$lang['form_validation_regex_match']     ='{field} fieldটি সঠিক ফর্ম্যাটে নেই ';
$lang['form_validation_matches']  ='{field} fieldটি {param} fieldের সাথে মেলে না';
$lang['form_validation_differs']   ='{field} fieldটি অবশ্যই {param} field থেকে পৃথক হতে হবে ';
$lang['form_validation_is_unique']   ='{field} fieldটিতে অবশ্যই একটি অনন্য মান থাকতে হবে ';
$lang['form_validation_is_natural']   ='{field} fieldটিতে কেবল অঙ্ক থাকতে হবে ';
$lang['form_validation_is_natural_no_zero']='{field} fieldটিতে অবশ্যই অঙ্কগুলি থাকতে হবে এবং এর চেয়ে বড় হতে হবে';
$lang['form_validation_decimal'] ='{field} fieldটিতে অবশ্যই একটি দশমিক সংখ্যা থাকতে হবে ';
$lang['form_validation_less_than'] ='{field} fieldটিতে অবশ্যই {param} এর চেয়ে কম সংখ্যা থাকতে হবে';
$lang['form_validation_less_than_equal_to']='{field} fieldটিতে অবশ্যই একটি সংখ্যা কম বা সমান হতে হবে';
$lang['form_validation_greater_than']      ='{field} fieldটিতে অবশ্যই {param} এর চেয়ে বড় একটি সংখ্যা থাকতে হবে';
$lang['form_validation_greater_than_equal_to']='{field} fieldটিতে অবশ্যই একটি সংখ্যা বা তার চেয়ে বড় বা সমান থাকতে হবে';
$lang['form_validation_error_message_not_set']='আপনার fieldের নাম {field} এর সাথে সম্পর্কিত কোনও ত্রুটি বার্তা অ্যাক্সেস করতে অক্ষম;';
$lang['form_validation_in_list'] ='{field} fieldটি অবশ্যই একটি হতে হবে: {param}।';