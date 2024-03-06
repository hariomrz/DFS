<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 *     Copyright (C) 2012  Dan Murfitt
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Extended Output Class
 *
 * Adds additional functionality to the Output library.
 *
 * @package		CodeIgniter
 * @subpackage          Libraries
 * @category            Output
 * @author		Dan Murfitt
 * @link		http://twitter.com/danmurf
 * @license             http://opensource.org/licenses/gpl-license.php GNU Public License (GPLv3)
 */
class MY_Output extends CI_Output {
    
    public function __construct() {
        parent::__construct();     
    }
    
    
    /**
     * Clears the cache for the specified path
     * @param string $uri The URI path
     * @return boolean TRUE if successful, FALSE if not
     */
    public function clear_path_cache($uri, &$CFG, &$URI)
    {
	 $path = $CFG->config['cache_path'];
        
        $cache_path = ($path == '') ? APPPATH.'cache/' : $path;
        
        if (empty($uri)) {
                $uri = $URI->uri_string();

                if (($cache_query_string = $CFG->config['cache_query_string']) && ! empty($_SERVER['QUERY_STRING']))
                {
                        if (is_array($cache_query_string))
                        {
                                $uri .= '?'.http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
                        }
                        else
                        {
                                $uri .= '?'.$_SERVER['QUERY_STRING'];
                        }
                }
        }
                
        
        $cache_path .= md5($CFG->config['base_url'].$CFG->config['index_page'].ltrim($uri, '/'));
        //return;
        log_message("error", $uri); 
	//$cache_path .= md5($uri); 
        @unlink($cache_path); 
        log_message("error", $cache_path); 
        return;
    }
    
    public function _display_cache(&$CFG, &$URI) {
       $c = isset($_COOKIE['dische'])?$_COOKIE['dische']:0;
       
        if ($c == 0) { 
            //$this->clear_path_cache('', $CFG, $URI);            
           return FALSE;            
        }       
        /* Call the parent function */
            return parent::_display_cache($CFG,$URI);
        // die;
    }
}

/* End of file MY_Output.php */
/* Location: ./application/core/MY_Output.php */