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

$lang['form_validation_required']               = 'Field {field} harus diisi.';
$lang['form_validation_isset']                  = 'Field {field} harus memiliki nilai.';
$lang['form_validation_valid_email']            = 'Field {field} harus berisi alamat email yang valid.';
$lang['form_validation_valid_emails']           = 'Field {field} harus berisi semua alamat email yang valid.';
$lang['form_validation_valid_url']              = 'Field {field} harus berisi URL yang valid.';
$lang['form_validation_valid_ip']               = 'Field {field} harus berisi IP yang valid.';
$lang['form_validation_min_length']             = 'Field {field} setidaknya harus terdiri dari {param} karakter.';
$lang['form_validation_max_length']             = 'Panjang isian {field} tidak boleh melebihi {param} karakter.';
$lang['form_validation_exact_length']           = 'Field {field} harus sama persis dengan {param} karakter.';
$lang['form_validation_alpha']                  = 'Field {field} hanya boleh berisi karakter alfabet.';
$lang['form_validation_alpha_numeric']          = 'Field {field} hanya boleh berisi karakter alpha-numeric.';
$lang['form_validation_alpha_numeric_spaces']   = 'Field {field} hanya boleh berisi karakter alfanumerik dan spasi.';
$lang['form_validation_alpha_dash']             = 'Field {field} hanya boleh berisi karakter alfanumerik, garis bawah, dan tanda hubung.';
$lang['form_validation_numeric']                = 'Field {field} harus berisi angka saja.';
$lang['form_validation_is_numeric']             = 'Field {field} hanya boleh berisi karakter numerik.';
$lang['form_validation_integer']                = 'Field {field} harus mengandung integer.';
$lang['form_validation_regex_match']            = 'Field {field} tidak dalam format yang benar.';
$lang['form_validation_matches']                = 'Field {field} tidak cocok dengan field {param}.';
$lang['form_validation_differs']                = 'Field {field} harus berbeda dari field {param}.';
$lang['form_validation_is_unique']              = 'Field {field} harus mengandung nilai yang unik.';
$lang['form_validation_is_natural']             = 'Field {field} hanya boleh berisi angka.';
$lang['form_validation_is_natural_no_zero']     = 'Field {field} hanya boleh berisi angka dan harus lebih besar dari nol.';
$lang['form_validation_decimal']                = 'Field {field} harus berisi angka desimal.';
$lang['form_validation_less_than']              = 'Field {field} harus berisi angka kurang dari {param}.';
$lang['form_validation_less_than_equal_to']     = 'Field {field} harus berisi angka kurang dari atau sama dengan {param}.';
$lang['form_validation_greater_than']           = 'Field {field} harus berisi angka yang lebih besar dari {param}.';
$lang['form_validation_greater_than_equal_to']  = 'Field {field} harus berisi angka yang lebih besar dari atau sama dengan {param}.';
$lang['form_validation_error_message_not_set']  = 'Tidak dapat mengakses pesan kesalahan yang berhubungan dengan nama field Anda {field}.';
$lang['form_validation_in_list']                = 'Field {field} harus salah satu dari: {param}.';