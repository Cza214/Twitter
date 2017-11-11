<?php
/**
 * Created by PhpStorm.
 * User: cezary
 * Date: 01.11.17
 * Time: 14:35
 */

class formatDate
{

    static public function format(string $date){
        $date = new DateTime($date);
        $format = $date->format('g:i A d F Y');
        return $format;
    }
}