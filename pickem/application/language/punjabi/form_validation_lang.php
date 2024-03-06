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

$lang['form_validation_required'] ='{field} ਖੇਤਰ ਲੋੜੀਂਦਾ ਹੈ. ';
$lang['form_validation_isset']        ='{field} ਖੇਤ ਵਿੱਚ ਇੱਕ ਮੁੱਲ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_valid_email']   ='{field} ਖੇਤਰ ਵਿੱਚ ਇੱਕ ਵੈਧ ਈਮੇਲ ਪਤਾ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_valid_emails'] ='{field} is ਖੇਤਰ ਵਿੱਚ ਸਾਰੇ ਯੋਗ ਈਮੇਲ ਪਤੇ ਹੋਣੇ ਚਾਹੀਦੇ ਹਨ. ';
$lang['form_validation_valid_url'] ='{field} ਖੇਤਰ ਵਿੱਚ ਇੱਕ ਵੈਧ URL ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_valid_ip'] ='{field} ਖੇਤਰ ਵਿੱਚ ਇੱਕ ਵੈਧ IP ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_min_length']   ='{field} ਖੇਤਰ ਘੱਟੋ ਘੱਟ {param param} ਅੱਖਰ ਹੋਣੇ ਚਾਹੀਦੇ ਹਨ'.
$lang['form_validation_max_length']   ='{field} ਖੇਤਰ ਲੰਬਾਈ ਵਿੱਚ {param ਮਨ ਅੱਖਰਾਂ ਤੋਂ ਵੱਧ ਨਹੀਂ ਹੋ ਸਕਦਾ. ';
$lang['form_validation_exact_length']   ='{field} ਖੇਤਰ ਲੰਬਾਈ ਦੇ ਬਿਲਕੁਲ {param} ਅੱਖਰ ਹੋਣੇ ਚਾਹੀਦੇ ਹਨ ';
$lang['form_validation_alpha']  ='Emation field} ਖੇਤਰ ਵਿੱਚ ਵਰਣਮਾਲਾ ਅੱਖਰਾਂ ਵਿੱਚ ਹੋ ਸਕਦਾ ਹੈ. ';
$lang['form_validation_alpha_numeric'] ='Freate field} ਖੇਤਰ ਵਿੱਚ ਅਲਫ਼ਾ-ਸੰਖਿਆਤਮਿਕ ਅੱਖਰ ਹੋ ਸਕਦੇ ਹਨ. ';
$lang['form_validation_alpha_numeric_spaces']=' {field} ਖੇਤਰ ਵਿੱਚ ਅਲਫ਼ਾ-ਸੰਖਿਆਤਮਕ ਅੱਖਰ ਅਤੇ ਖਾਲੀ ਥਾਂਵਾਂ ਹੋ ਸਕਦੀਆਂ ਹਨ ';
$lang['form_validation_alpha_dash']  ='{field} ਖੇਤਰ ਵਿੱਚ ਸਿਰਫ ਅਲਫ਼ਾ-ਸੰਖਿਆਤਮਕ ਅੱਖਰ, ਅੰਡਰਸਕੋਰਸ ਹੋ ਸਕਦੇ ਹਨ';
$lang['form_validation_numeric']       ='{field} ਖੇਤਰ ਵਿੱਚ ਸਿਰਫ ਸਿਰਫ ਨੰਬਰ ਹੋਣੇ ਚਾਹੀਦੇ ਹਨ. ';
$lang['form_validation_is_numeric']   ='Freate field} ਖੇਤਰ ਵਿੱਚ ਸਿਰਫ ਸੰਖਿਆਤਮਕ ਅੱਖਰ ਹੋਣੇ ਚਾਹੀਦੇ ਹਨ. ';
$lang['form_validation_integer']   =' {field} ਖੇਤਰ ਵਿੱਚ ਪੂਰਨ ਅੰਕ ਰੱਖਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_regex_match']     ='{field} is ਖੇਤਰ ਸਹੀ ਫਾਰਮੈਟ ਵਿੱਚ ਨਹੀਂ ਹੈ. ';
$lang['form_validation_matches']  ='{field} ਖੇਤਰ {param} ਖੇਤਰ ਨਾਲ ਮੇਲ ਨਹੀਂ ਖਾਂਦਾ. ';
$lang['form_validation_differs']   ='{field} ਖੇਤਰ ਨੂੰ {param} ਖੇਤਰ ਤੋਂ ਵੱਖਰਾ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_is_unique']   ='{field} ਖੇਤਰ ਵਿੱਚ ਇੱਕ ਵਿਲੱਖਣ ਮੁੱਲ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_is_natural']   ='{field} ਖੇਤਰ ਵਿੱਚ ਸਿਰਫ ਅੰਕ ਹੀ ਸ਼ਾਮਲ ਹੋਣੇ ਚਾਹੀਦੇ ਹਨ. ';
$lang['form_validation_is_natural_no_zero']='{field} ਖੇਤਰ ਵਿੱਚ ਸਿਰਫ ਅੰਕ ਹੋ ਜਾਣੇ ਚਾਹੀਦੇ ਹਨ ਅਤੇ ਇਸ ਤੋਂ ਵੱਧ ਹੋਣੇ ਚਾਹੀਦੇ ਹਨ'.
$lang['form_validation_decimal'] ='Ematlat ਖੇਤਰ ਵਿੱਚ ਇੱਕ ਦਸ਼ਮਲਵ ਨੰਬਰ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_less_than'] ='{field} ਖੇਤਰ ਵਿੱਚ {paramਵਾਂ ਦੀ ਵੱਧ ਗਿਣਤੀ ਘੱਟ ਹੋਣੀ ਚਾਹੀਦੀ ਹੈ. ';
$lang['form_validation_less_than_equal_to']='{field} ਖੇਤਰ ਵਿੱਚ ਇੱਕ ਨੰਬਰ ਘੱਟ ਜਾਂ ਇਸਦੇ ਬਰਾਬਰ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ ';
$lang['form_validation_greater_than']      = 'field ਖੇਤਰ ਵਿੱਚ {param} ਨਾਲੋਂ ਇੱਕ ਨੰਬਰ ਵੱਡਾ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';
$lang['form_validation_greater_than_equal_to']='{field} ਖੇਤਰ ਵਿੱਚ ਇੱਕ ਨੰਬਰ ਜਾਂ ਇਸਦੇ ਬਰਾਬਰ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ ';
$lang['form_validation_error_message_not_set']='ਤੁਹਾਡੇ ਖੇਤਰ ਦੇ ਨਾਮ {field {field {field ਵਿੱਚ ਇੱਕ ਗਲਤੀ ਸੁਨੇਹਾ ਪ੍ਰਾਪਤ ਕਰਨ ਵਿੱਚ ਅਸਮਰੱਥ. ';
$lang['form_validation_in_list'] = '{field} ਖੇਤ: {param ਲਾਰਮ ਵਿੱਚੋਂ ਹੋਣਾ ਚਾਹੀਦਾ ਹੈ. ';