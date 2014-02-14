<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter CAPTCHA Helper
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/xml_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Create CAPTCHA
 *
 * @access	public
 * @param	array	array of data for the CAPTCHA
 * @param	string	path to create the image in
 * @param	string	URL to the CAPTCHA image folder
 * @param	string	server path to font
 * @return	string
 */
if ( ! function_exists('create_captcha'))
{
	function create_captcha($data = '', $img_path = '', $img_url = '', $font_path = '', $output_file = false, $line_pattern = false)
	{
		$defaults = array('word' => '', 'img_path' => '', 'img_url' => '', 'img_width' => '113', 'img_height' => '34', 'font_path' => '', 'font' => array(), 'expiration' => 7200);

		foreach ($defaults as $key => $val)
		{
			if ( ! is_array($data))
			{
				if ( ! isset($$key) OR $$key == '')
				{
					$$key = $val;
				}
			}
			else
			{
				$$key = ( ! isset($data[$key])) ? $val : $data[$key];
			}
		}

		if($output_file)
		{
			if ($img_path == '' OR $img_url == '')
			{
				return FALSE;
			}

			if ( ! @is_dir($img_path))
			{
				return FALSE;
			}

			if ( ! is_writable($img_path))
			{
				return FALSE;
			}
		}

		if ( ! extension_loaded('gd'))
		{
			return FALSE;
		}

		// -----------------------------------
		// Remove old images
		// -----------------------------------
		
		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);

		if($output_file)
		{
			$current_dir = @opendir($img_path);

			while ($filename = @readdir($current_dir))
			{
				if ($filename != "." and $filename != ".." and $filename != "index.html")
				{
					$name = str_replace(".jpg", "", $filename);

					if (($name + $expiration) < $now)
					{
						@unlink($img_path.$filename);
					}
				}
			}

			@closedir($current_dir);
		}
		// -----------------------------------
		// Do we have a "word" yet?
		// -----------------------------------

	   if ($word == '')
	   {
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			$str = '';
			for ($i = 0; $i < 8; $i++)
			{
				$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
			}

			$word = $str;
	   }

		// -----------------------------------
		// Determine angle and position
		// -----------------------------------

		$length	= strlen($word);
		$angle	= ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
		$x_axis	= rand(6, (360/$length)-16);
		$y_axis = ($angle >= 0 ) ? rand($img_height, $img_width) : rand(6, $img_height);

		// -----------------------------------
		// Create image
		// -----------------------------------

		// PHP.net recommends imagecreatetruecolor(), but it isn't always available
		if (function_exists('imagecreatetruecolor'))
		{
			$im = imagecreatetruecolor($img_width, $img_height);
		}
		else
		{
			$im = imagecreate($img_width, $img_height);
		}

		// -----------------------------------
		//  Assign colors
		// -----------------------------------

		$bg_color		= imagecolorallocate ($im, 255, 255, 255);
		$border_color	= imagecolorallocate ($im, 155, 155, 155);
		$text_color		= imagecolorallocate ($im, 42, 141, 106);
		$grid_color		= imagecolorallocate ($im, 42, 141, 106);
		$shadow_color	= imagecolorallocate ($im, 255, 240, 240);

		// -----------------------------------
		//  Create the rectangle
		// -----------------------------------

		ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

		// -----------------------------------
		//  Create the spiral pattern
		// -----------------------------------
		if($line_pattern)
		{
			$theta		= 1;
			$thetac		= 17;
			$radius		= 50;
			$circles	= 20;
			$points		= 12;

			imagesetthickness($im,3);
			for ($i = 0; $i < ($circles * $points) - 1; $i++)
			{
				$theta = $theta + $thetac;
				$rad = $radius * ($i / $points );
				$x = ($rad * cos($theta)) + $x_axis;
				$y = ($rad * sin($theta)) + $y_axis;
				$theta = $theta + $thetac;
				$rad1 = $radius * (($i + 1) / $points);
				$x1 = ($rad1 * cos($theta)) + $x_axis;
				$y1 = ($rad1 * sin($theta )) + $y_axis;
				imageline($im, $x, $y, $x1, $y1, $grid_color);
				$theta = $theta - $thetac;
			}
			imagesetthickness($im,1);
		}
		// -----------------------------------
		//  Write the text
		// -----------------------------------

		$font_file = $font_path.$font[0];
		$use_font = ($font_file != '' AND file_exists($font_file) AND function_exists('imagettftext')) ? TRUE : FALSE;

		if ($use_font == FALSE)
		{
			$font_size = 10;
			$x = rand(0, $img_width/($length/1));
			$y = 0;
		}
		else
		{
			$font_size	= 17;//字号
			$x = rand(1, $img_width/($length/1.5));//横向坐标
			$y = $font_size+2;
		}

		for ($i = 0; $i < strlen($word); $i++)
		{
			$font_file = $font_path.$font[mt_rand(0,count($font)-1)];
			$use_font = ($font_file != '' AND file_exists($font_file) AND function_exists('imagettftext')) ? TRUE : FALSE;
			if ($use_font == FALSE)
			{
				$y = rand(0 , $img_height/2);
				imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
				$x += ($font_size*2);
			}
			else
			{
				$y = rand($img_height-5, $img_height/2+3);//纵向坐标
				imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_file, substr($word, $i, 1));
				$x += $font_size;
			}
		}

		writeCurve($im, $img_width, $img_height, $font_size, $grid_color);
		//writeNoise($im, $img_width, $img_height, '12312');
		// -----------------------------------
		//  Create the border
		// -----------------------------------

		imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);

		// -----------------------------------
		//  Generate the image
		// -----------------------------------
		if($output_file)
		{
			$img_name = $now.'.jpg';

			ImageJPEG($im, $img_path.$img_name);

			$img = "<img src=\"$img_url$img_name\" width=\"$img_width\" height=\"$img_height\" style=\"border:0;\" alt=\" \" />";

			ImageDestroy($im);

			//output img

			return array('word' => $word, 'time' => $now, 'image' => $img, 'im' => $im);
		}
		else
		{
			return array('word' => $word, 'time' => $now, 'image' => '', 'im' => $im);
		}
		
	}

	function writeCurve($im, $width, $height, $fontsize, $color) {
        $A = mt_rand($height/4, $height/2);                  // 振幅  
        $b = mt_rand(-$height/4, $height/4);   // Y轴方向偏移量  
        $f = mt_rand(-$height/4, $height/4);   // X轴方向偏移量  
        $T = mt_rand($height*1.5, $width*2);  // 周期  
        $w = (2* M_PI)/$T;  
                          
        $px1 = 0;  // 曲线横坐标起始位置  
        $px2 = mt_rand($width/2, $width * 0.667);  // 曲线横坐标结束位置             
        for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {  
            if ($w!=0) {  
                $py = $A * sin($w*$px + $f)+ $b + $height/2;  // y = Asin(ωx+φ) + b  
                $i = (int) (($fontsize - 6)/4);  
                while ($i > 0) {   
                    imagesetpixel($im, $px + $i, $py + $i, $color);  // 这里画像素点比imagettftext和imagestring性能要好很多                    
                    $i--;  
                }  
            }  
        } 
        
        $A = mt_rand($height/4, $height/2);                  // 振幅          
        $f = mt_rand(-$height/4, $height/4);   // X轴方向偏移量  
        $T = mt_rand($height*1.5, $width*2);  // 周期  
        $w = (2* M_PI)/$T;        
        $b = $py - $A * sin($w*$px + $f) - $height/2;  
        $px1 = $px2;  
        $px2 = $width;  
        for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {  
            if ($w!=0) {  
                $py = $A * sin($w*$px + $f)+ $b + $height/2;  // y = Asin(ωx+φ) + b  
                $i = (int) (($fontsize - 8)/4);  
                while ($i > 0) {           
                    imagesetpixel($im, $px + $i, $py + $i, $color);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多      
                    $i--;  
                }  
            }  
        }  
    }

    function writeNoise($im, $width, $height, $text) {  
        for($i = 0; $i < 10; $i++){  
            //杂点颜色  
            $noiseColor = imagecolorallocate($im, mt_rand(150,225), mt_rand(150,225), mt_rand(150,225));  
            for($j = 0; $j < 5; $j++) {  
                // 绘杂点  
                imagestring($im, 3, mt_rand(-10, $width), mt_rand(-10, $height), 
                	$text, // 杂点文本为随机的字母或数字  
                    $noiseColor  
                );  
            }  
        }  
    }
}

// ------------------------------------------------------------------------

/* End of file captcha_helper.php */
/* Location: ./system/heleprs/captcha_helper.php */