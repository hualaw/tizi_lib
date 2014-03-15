<?php

require_once("auth_digest.php");

// --------------------------------------------------------------------------------
// class Qiniu_ImageView

class Qiniu_ImageView {
	public $Mode;
    public $Width;
    public $Height;
    public $Quality;
    public $Format;

    public function MakeRequest($url)
    {
    	$ops = array($this->Mode);

    	if (!empty($this->Width)) {
    		$ops[] = 'w/' . $this->Width;
    	}
    	if (!empty($this->Height)) {
    		$ops[] = 'h/' . $this->Height;
    	}
    	if (!empty($this->Quality)) {
    		$ops[] = 'q/' . $this->Quality;
    	}
    	if (!empty($this->Format)) {
    		$ops[] = 'format/' . $this->Format;
    	}

    	return $url . "?imageView/" . implode('/', $ops);
    }
}

// --------------------------------------------------------------------------------
// class Qiniu_Exif

class Qiniu_Exif {

	public function MakeRequest($url)
	{
		return $url . "?exif";
	}

}

// --------------------------------------------------------------------------------
// class Qiniu_ImageInfo

class Qiniu_ImageInfo {

	public function MakeRequest($url)
	{
		return $url . "?imageInfo";
	}

}

//图片缩放->裁切(->缩放)
class Qiniu_ImageThumbCrop{
    //缩放宽度百分比（1-1000）
    public $width_percent;
    
    //裁切参数控制
    public $crop = false;
    //以下参数$crop = true起效
    //裁切宽（像素）
    public $crop_width;
    //裁切高（像素）
    public $crop_height;
    //裁切左偏移（像素）
    public $crop_left;
    //裁切右偏移（像素）
    public $crop_top;
    
    //裁切后缩放参数控制
    public $crop_thumb = false;
    //缩放宽度百分比（1-1000）
    public $crop_thumb_wp;

    public function MakeRequest($url){
        if(!$this->width_percent){
            return false;
        }
        $ops = 'imageMogr2/thumbnail/';
        
        if($this->width_percent){
            $ops .= "!{$this->width_percent}px";
        }
        
        $cops = '';
        if($this->crop){
            if(!$this->crop_width || !$this->crop_height || !$this->crop_left || !$this->crop_top){
                
            }
            $cops = '|imageMogr2/crop/!';
            $cops .= max(array(1, intval($this->crop_width))) . 'x' . max(array(1, intval($this->crop_height)));
            $cops .= 'a' . max(array(0, intval($this->crop_left))) . 'a' . max(array(0, intval($this->crop_top)));
        }
        
        $tops = '';
        if($this->crop_thumb){
            if(!$this->crop_thumb_wp){
                return false;
            }
            $tops = '|imageMogr2/thumbnail/';
            $tops .= "!{$this->crop_thumb_wp}px";
        }
        
        return $url . "?" . $ops . $cops . $tops;
    }
}


//图片缩放忽略原宽高比例
class Qiniu_ImageThumb{
    public $width;
    public $height;
    
    public function MakeRequest($url){
        if(!$this->width || !$this->height){
            return false;
        }
        
        $ops = "imageMogr2/thumbnail/{$this->width}x{$this->height}!";
        
        return $url . '?' . $ops;
    }
}