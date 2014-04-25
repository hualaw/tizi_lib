<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

function smarty_function_my_json($params)
{
    //print_r($data);
    //exit();
    return json_encode($params['data']);
}

?>