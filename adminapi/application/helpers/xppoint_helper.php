<?php

function get_master_levels()
{
    $level_numbers = range(1,100);
    $final_arr = array();
    foreach($level_numbers as $level)
    {
        $final_arr[$level] = "Level ".$level;
    }
    return $final_arr;
}