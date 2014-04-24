<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

function smarty_function_my_time($params)
{
    $params['value']	=	round($params['value']);
    return gmstrftime('%H:%M:%S',$params['value']);
     
}

?>