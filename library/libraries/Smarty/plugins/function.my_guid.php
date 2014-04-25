<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */


function smarty_function_my_guid()
{
    //$charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $charid = md5(uniqid(mt_rand(), true));
    $hyphen = chr(45);// "-"
    //$uuid = chr(123)// "{"
    $uuid = substr($charid, 0, 8).$hyphen
    .substr($charid, 8, 4).$hyphen
    .substr($charid,12, 4).$hyphen
    .substr($charid,16, 4).$hyphen
    .substr($charid,20,12);
    //.chr(125);// "}"
    //echo $charid;
    return $uuid;
}


?>