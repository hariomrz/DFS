<?php

/** application/libraries/MY_Form_validation * */
/* * *This file is required to run form validation callbacks** */
//-----Added by Ankit patidar <ankit.patidar@vinfotech.com>-------/
class MY_Form_validation extends CI_Form_validation {

    public $CI;

    function is_url($uri) {
        if (preg_match('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/i', $uri)) {
            return $uri;
        } else {
            $this->set_message('is_url', 'Invalid match link');
            return false;
        }
    }

}
