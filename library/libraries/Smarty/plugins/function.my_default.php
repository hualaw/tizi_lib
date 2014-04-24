<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

function smarty_function_my_default($params)
{
    return $params['value']==''?$params['default']:$params['value'];
}

?>