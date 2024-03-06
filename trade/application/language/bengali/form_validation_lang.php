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

$lang['form_validation_required'] = '{field} ক্ষেত্রটি প্রয়োজন।';
$lang['form_validation_isset'] = '{field} ক্ষেত্রের একটি মান থাকতে হবে।';
$lang['form_validation_valid_email'] = '{field} ফিল্ডে অবশ্যই একটি বৈধ ইমেল ঠিকানা থাকতে হবে।';
$lang['form_validation_valid_emails'] = '{field} ফিল্ডে অবশ্যই সব বৈধ ইমেল ঠিকানা থাকতে হবে।';
$lang['form_validation_valid_url'] = '{field} ফিল্ডে অবশ্যই একটি বৈধ URL থাকতে হবে।';
$lang['form_validation_valid_ip'] = '{field} ফিল্ডে অবশ্যই একটি বৈধ আইপি থাকতে হবে।';
$lang['form_validation_min_length'] = '{field} ফিল্ডের দৈর্ঘ্য কমপক্ষে {param} অক্ষর হতে হবে।';
$lang['form_validation_max_length'] = '{field} ফিল্ডের দৈর্ঘ্য {param} অক্ষরের বেশি হতে পারে না।';
$lang['form_validation_exact_length'] = '{field} ফিল্ডটি অবশ্যই দৈর্ঘ্যে ঠিক {param} অক্ষর হতে হবে।';
$lang['form_validation_alpha'] = '{field} ফিল্ডে শুধুমাত্র বর্ণানুক্রমিক অক্ষর থাকতে পারে।';
$lang['form_validation_alpha_numeric'] = '{field} ফিল্ডে শুধুমাত্র আলফা-সংখ্যাসূচক অক্ষর থাকতে পারে।';
$lang['form_validation_alpha_numeric_spaces'] = '{field} ফিল্ডে শুধুমাত্র আলফা-সংখ্যাসূচক অক্ষর এবং স্পেস থাকতে পারে।';
$lang['form_validation_alpha_dash'] = '{field} ফিল্ডে শুধুমাত্র আলফা-সংখ্যাসূচক অক্ষর, আন্ডারস্কোর এবং ড্যাশ থাকতে পারে।';
$lang['form_validation_numeric'] = '{field} ফিল্ডে শুধুমাত্র সংখ্যা থাকতে হবে।';
$lang['form_validation_is_numeric'] = '{field} ফিল্ডে শুধুমাত্র সংখ্যাসূচক অক্ষর থাকতে হবে।';
$lang['form_validation_integer'] = '{field} ফিল্ডে অবশ্যই একটি পূর্ণসংখ্যা থাকতে হবে।';
$lang['form_validation_regex_match'] = '{field} ক্ষেত্রটি সঠিক বিন্যাসে নেই।';
$lang['form_validation_matches'] = '{field} ক্ষেত্রটি {param} ক্ষেত্রের সাথে মেলে না।';
$lang['form_validation_differs'] = '{field} ফিল্ডটি অবশ্যই {param} ফিল্ড থেকে আলাদা হতে হবে।';
$lang['form_validation_is_unique'] = '{field} ক্ষেত্রে একটি অনন্য মান থাকতে হবে।';
$lang['form_validation_is_natural'] = '{field} ফিল্ডে শুধুমাত্র সংখ্যা থাকতে হবে।';
$lang['form_validation_is_natural_no_zero'] = '{field} ফিল্ডে শুধুমাত্র সংখ্যা থাকতে হবে এবং শূন্যের চেয়ে বড় হতে হবে।';
$lang['form_validation_decimal'] = '{field} ফিল্ডে অবশ্যই একটি দশমিক সংখ্যা থাকতে হবে।';
$lang['form_validation_less_than'] = '{field} ফিল্ডে অবশ্যই {param} এর থেকে কম একটি সংখ্যা থাকতে হবে।';
$lang['form_validation_less_than_equal_to'] = '{field} ফিল্ডে অবশ্যই {param} এর থেকে কম বা সমান একটি সংখ্যা থাকতে হবে।';
$lang['form_validation_greater_than'] = '{field} ফিল্ডে অবশ্যই {param} এর থেকে বড় একটি সংখ্যা থাকতে হবে।';
$lang['form_validation_greater_than_equal_to'] = '{field} ফিল্ডে অবশ্যই {param} এর থেকে বড় বা সমান একটি সংখ্যা থাকতে হবে।';
$lang['form_validation_error_message_not_set'] = 'আপনার ক্ষেত্রের নামের সাথে সম্পর্কিত একটি ত্রুটি বার্তা অ্যাক্সেস করতে অক্ষম।';
$lang['form_validation_in_list'] = '{field} ক্ষেত্রটি অবশ্যই একটি হতে হবে: {param}';