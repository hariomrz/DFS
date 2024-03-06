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

$lang['form_validation_required'] ="Le field {field} est requis.";
$lang['form_validation_isset']        ="Le field {field} doit avoir une valeur.";
$lang['form_validation_valid_email']   ="Le field {field} doit contenir une adresse e-mail valide.";
$lang['form_validation_valid_emails'] ="Le field {field} doit contenir toutes les adresses e-mail valides.";
$lang['form_validation_valid_url'] ="Le field {field} doit contenir une URL valide.";
$lang['form_validation_valid_ip'] ="Le field {field} doit contenir une IP valide.";
$lang['form_validation_min_length']   ="Le field {field} doit être au moins {param} des caractères dans";
$lang['form_validation_max_length']   ="Le field {field} ne peut pas dépasser les caractères {param} en longueur.";
$lang['form_validation_exact_length']   ="Le field {field} doit être exactement des caractères {param} en longueur";
$lang['form_validation_alpha']  ="Le field {Field} ne peut contenir que des caractères alphabétiques.";
$lang['form_validation_alpha_numeric'] ="Le field {Field} ne peut contenir que des caractères alpha-nuques.";
$lang['form_validation_alpha_numeric_spaces']="Le field {Field} peut ne contenir que des caractères et des espaces alpha-numeriques";
$lang['form_validation_alpha_dash']  ="Le field {Field} ne peut contenir que des caractères alpha-numes, soulignent";
$lang['form_validation_numeric']       ="Le field {field} ne doit contenir que des nombres.";
$lang['form_validation_is_numeric']   ="Le field {field} ne doit contenir que des caractères numériques.";
$lang['form_validation_integer']   ="Le field {field} doit contenir un entier.";
$lang['form_validation_regex_match']     ="Le field {field} n'est pas dans le format correct.";
$lang['form_validation_matches']  ="Le field {field} ne correspond pas au field {param}.";
$lang['form_validation_differs']   ="Le field {field} doit différer du field {param}.";
$lang['form_validation_is_unique']   ="Le field {field} doit contenir une valeur unique.";
$lang['form_validation_is_natural']   ="Le field {field} ne doit contenir que des chiffres.";
$lang['form_validation_is_natural_no_zero']="Le field {field} ne doit contenir que des chiffres et doit être supérieur à";
$lang['form_validation_decimal'] ="Le field {field} doit contenir un numéro décimal.";
$lang['form_validation_less_than'] ="Le field {field} doit contenir un nombre inférieur à {param}.";
$lang['form_validation_less_than_equal_to']="Le field {field} doit contenir un nombre inférieur ou égal à";
$lang['form_validation_greater_than']      ="Le field {field} doit contenir un nombre supérieur à {param}.";
$lang['form_validation_greater_than_equal_to']="Le field {field} doit contenir un nombre supérieur ou égal à";
$lang['form_validation_error_message_not_set']="Impossible d'accéder à un message d'erreur correspondant au nom de votre field {field}.";
$lang['form_validation_in_list'] ="Le field {field} doit être l'un des: {param}.";