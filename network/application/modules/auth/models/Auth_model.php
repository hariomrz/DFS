<?php 
class Auth_model extends MY_Model 
{

    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * for generate user unique key
     * @return string
     */
    public function _generate_key() 
    {
        $this->load->helper('security');
        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 10);
        }

        // Already in the DB? Fail. Try again
        while (self::_key_exists($new_key));

        return $new_key;
    }

    
}