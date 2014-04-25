<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

function smarty_function_my_percent($params)
{
	if($params['denominator']<=0)
	{
		return 0;
	}
	else
	{
		return round($params['numerator']/$params['denominator']*10000)/100;
	}
		
}

?>