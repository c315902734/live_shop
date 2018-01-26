<?php
namespace common\models;

class ImageProcess{
    public $source;//原图
    public $source_width;//宽
    public $source_height;//高
    public $source_type_id;
    public $orign_name;
    public $orign_dirname;
    //传入图片路径
    public function __construct($source){
        $this->typeList      = array(1=>'gif',2=>'jpg',3=>'png');
        $ginfo               = getimagesize($source);
        $this->source_width  = $ginfo[0];
        $this->source_height = $ginfo[1];
        $this->source_type_id= $ginfo[2];
        $this->orign_url     = $source;
        $this->orign_name    = basename($source);
        $this->orign_dirname = dirname($source);
    }
 
    //判断并处理,返回PHP可识别编码
    public function judgeType($type,$source){
        if($type==1){
            return ImageCreateFromGIF($source);//gif
        }else if($type==2){
            return ImageCreateFromJPEG($source);//jpg
        }else if($type==3){
            return ImageCreateFromPNG($source);//png
        }else{
            return false;
        } 
    }
 
    //生成水印图
    public function watermarkImage($logo){
        $linfo        = getimagesize($logo);
        $logo_width   = $linfo[0];
        $logo_height  = $linfo[1];
        $logo_type_id = $linfo[2];
        $sourceHandle = $this->judgeType($this->source_type_id,$this->orign_url);
        $logoHandle   = $this->judgeType($logo_type_id,$logo);
 
        if( !$sourceHandle || ! $logoHandle ){
            return false;
        }
        $x = $this->source_width - $logo_width;
        $y = $this->source_height- $logo_height;
 
        ImageCopy($sourceHandle,$logoHandle,$x,$y,0,0,$logo_width,$logo_width) or die("fail to combine");
        $newPic = $this->orign_dirname .'\water_'. time().'.'. $this->typeList[$this->source_type_id];
 
        if( $this->saveImage($sourceHandle,$newPic)){
            imagedestroy($sourceHandle);
            imagedestroy($logoHandle);
            return $newPic;
        }
    }
 
    // fix 宽度
    // height = true 固顶高度
    // width  = true 固顶宽度
    public function fixSizeImage($width,$height){
        if( $width > $this->source_width) $this->source_width;
        if( $height > $this->source_height ) $this->source_height;
        if( $width === false){
            $width = floor($this->source_width / ($this->source_height / $height));
        }
        if( $height === false){
            $height = floor($this->source_height / ($this->source_width / $width));
        }
        $this->tinyImage($width,$height);
    }
 
    //比例缩放
    // $scale 缩放比例
    public function scaleImage($scale, $sourePic, $smallFileName){
        $width  = floor($this->source_width * $scale);
        $height = floor($this->source_height * $scale);
        //return $this->tinyImage($width,$height);
        return $this->pngthumb($sourePic, $smallFileName, $width, $height);
    }
 
    //创建略缩图
    private function tinyImage($width,$height){
        $tinyImage = imagecreatetruecolor($width, $height );
        $handle    = $this->judgeType($this->source_type_id,$this->orign_url);
        if(function_exists('imagecopyresampled')){
            imagecopyresampled($tinyImage,$handle,0,0,0,0,$width,$height,$this->source_width,$this->source_height);
            imagesavealpha($tinyImage, true);
            imagepng($tinyImage, './thumb.png');
            return true;
        }else{
            imagecopyresized($tinyImage,$handle,0,0,0,0,$width,$height,$this->source_width,$this->source_height);
        }
 
        $newPic = time().'_'.$width.'_'.$height.'.'. $this->typeList[$this->source_type_id];
        $newPic = $this->orign_dirname .'\thumb_'. $newPic;
        if( $this->saveImage($tinyImage,$newPic)){
            imagedestroy($tinyImage);
            imagedestroy($handle);
            return $newPic;
        }
    }
    
    
    function pngthumb($sourePic,$smallFileName,$width,$heigh){
    	$image=imagecreatefrompng($sourePic);//PNG
    	imagesavealpha($image,true);//这里很重要 意思是不要丢了$sourePic图像的透明色;
    	$BigWidth=imagesx($image);//大图宽度
    	$BigHeigh=imagesy($image);//大图高度
    	$thumb = imagecreatetruecolor($width,$heigh);
    	imagealphablending($thumb,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
    	imagesavealpha($thumb,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
    	if(imagecopyresampled($thumb,$image,0,0,0,0,$width,$heigh,$BigWidth,$BigHeigh)){
    		imagepng($thumb,$smallFileName);}
    		return $smallFileName;//返回小图路径 转载注明 www.chhua.com
    }
    
    
//     function pngthumb($sourePic,$smallFileName,$width,$heigh){
//     	$image=imagecreatefrompng($sourePic);//PNG
//     	imagesavealpha($image,true);//这里很重要 意思是不要丢了$sourePic图像的透明色;
//     	$BigWidth=imagesx($image);//大图宽度
//     	$BigHeigh=imagesy($image);//大图高度
//     	$thumb = imagecreatetruecolor($width,$heigh);
//     	imagealphablending($thumb,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
//     	imagesavealpha($thumb,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
//     	if(imagecopyresampled($thumb,$image,0,0,0,0,$width,$heigh,$BigWidth,$BigHeigh)){
//     		imagepng($thumb,$smallFileName);}
//     		return $smallFileName;//返回小图路径 转载注明 www.chhua.com
//     }
 
    //保存图片
    private function saveImage($image,$url){
        if(imagepng($image,$url)){
            return true;
        }
    }
}
?>