<?php
 
/**
 * 图片的大小水印的修改-简而言之就是加工图片
 */
class image_clip{
	/**
	 * 生成指定宽度和高度的图片
	 * @param object image model object
	 * @param string source file directory
	 * @param mixed 临时数据源
	 * @param string 宽度
	 * @param string 高度
	 * @return null 
	 */
    function image_resize(&$imgmdl,$src_file,$target_file,$new_width,$new_height){
        if(isset($src_file)&&is_file($src_file)){
            list($width, $height,$type) = getimagesize($src_file);
            $size = self::get_image_size($new_width,$new_height,$width,$height);
            $new_width = $size[0];
            $new_height = $size[1]; 
            if(ECAE_MODE){
                include_lib('image.php');
                $obj = new ecae_image();
                $obj->set_file($src_file);
                $obj->resize($new_width, $new_height);
                $obj->strip();
                $content = $obj->exec();
                if($content){
                    file_put_contents($target_file, $content);
                    return true;
                }else{
                    return false;
                }                
            }elseif(function_exists('magickresizeimage')){
                $rs = NewMagickWand();
                if(MagickReadImage($rs,$src_file)){
                    MagickResizeImage($rs,$new_width,$new_height,MW_QuadraticFilter,0.3);
                    MagickSetImageFormat($rs,'image/jpeg');
                    MagickWriteImage($rs, $target_file);
                }
                return true;
            }elseif( function_exists('imagecopyresampled')){	
                $quality  = 80;
                $image_p = imagecreatetruecolor($new_width, $new_height);
				if($new_width>$width && $new_height>$height)
				{
					$background_color = imagecolorallocate($image_p, 255, 255, 255);
					imagefilledrectangle ( $image_p, 0, 0, $new_width, $new_height, $background_color );
				}
                imagealphablending($image_p,false);
                switch($type){
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($src_file);
                    $func = 'imagejpeg';
                    break;
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($src_file);
                    $func = 'imagegif';
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($src_file);
                    imagesavealpha($image,true);
                    $func = 'imagepng';
                    $quality  = 8;
                    break;
                }
                imagesavealpha($image_p,true);
				if($new_width>$width && $new_height>$height)
					imagecopyresampled($image_p, $image, ($new_width - $width) /2, ($new_height - $height) /2, 0, 0, $width, $height,$width, $height);
				else
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);	
                if($func) $func($image_p, $target_file, $quality);
                imagedestroy($image_p);
                imagedestroy($image);
            }
        }
    }
    
    /**
     * 得到修改后的图片长度和宽度
     * @param string 图片新的宽度
     * @param string 图片新的高度
     * @param string 图片原来的宽度
     * @param string 图片原来的高度
     * @return array 目前长宽
     */
    function get_image_size($new_width,$new_height,$org_width,$org_height){
        $dest_width = $new_width;
        $dest_height = $new_height;
        if($org_width>$org_height){
            if($org_width>=$new_width){
                $dest_width = $new_width;
                $dest_height = round(($org_height/$org_width)*$new_width);
            }
        }else{
            if($org_height>=$new_height){
                $dest_height = $new_height;
                $dest_width = round(($org_width/$org_height)*$new_height);
            }
        }


        if(defined('WITHOUT_AUTOPADDINGIMAGE')&&WITHOUT_AUTOPADDINGIMAGE){
                
                  if($dest_width>$org_width){
                        $dest_width = $org_width;
                   }
                 
                  if($dest_height>$org_height){
                        $dest_height = $org_height;
                    }

        }       
      


        return array($dest_width,$dest_height);
    }
    
    /**
     * 设置图片水印
     * @param object image 实体对象
     * @param string 文件路径
	 * @param array 设置的集合
	 * @return null
     */
    function image_watermark(&$imgmdl,$file,$set){
        switch($set['wm_type']){
        case 'text':
            $mark_image = $set['wm_text_image'];
            break;
        case 'image':
            $mark_image = $set['wm_image'];
            break;
        default:
            return;
        }
        if($set['wm_text_preview']){
            $mark_image = $set['wm_text_image'];
        }else{
            $mark_image = $imgmdl->fetch($mark_image);
        }
        list($watermark_width,$watermark_height,$type) = getimagesize($mark_image);
        list($src_width,$src_height) = getimagesize($file);
        list($dest_x, $dest_y ) = self::get_watermark_dest($src_width,$src_height,$watermark_width,$watermark_height,$set['wm_loc']);
        
        if(ECAE_MODE){
            include_lib('image.php');
            $obj = new ecae_image();
            $obj->set_file($file);
            $obj->watermark(
                file_get_contents($mark_image), $dest_x, $dest_y, 0, 0, $set['wm_opacity']?$set['wm_opacity']:50
            );
            $content = $obj->exec();
            if($content){
                file_put_contents($file, $content);
            }
        }elseif(function_exists('NewMagickWand')){
            $sourceWand = NewMagickWand();
            $compositeWand = NewMagickWand();
            MagickReadImage($compositeWand, $mark_image);
            MagickReadImage($sourceWand, $file);
            MagickSetImageIndex($compositeWand, 0);
            MagickSetImageType($compositeWand, MW_TrueColorMatteType);
            MagickEvaluateImage($compositeWand, MW_SubtractEvaluateOperator, ($set['wm_opacity']?$set['wm_opacity']:50)/100, MW_OpacityChannel) ;
            MagickCompositeImage($sourceWand, $compositeWand, MW_ScreenCompositeOp, $dest_x, $dest_y);
            MagickWriteImage($sourceWand, $file);
        }elseif(method_exists(image_clip,'imagecreatefrom')){
            $sourceimage = self::imagecreatefrom($file);
            $watermark = self::imagecreatefrom($mark_image);
            imagecolortransparent($watermark, imagecolorat($watermark,0,0));
            imagealphablending($watermark,1);
			$set['wm_opacity'] = intval($set['wm_opacity']);

			imagecopymerge($sourceimage, $watermark, $dest_x, $dest_y, 0,
				0, $watermark_width, $watermark_height, $set['wm_opacity']);				
           
            imagejpeg($sourceimage,$file);
            imagedestroy($sourceimage);
            imagedestroy($watermark);
        }
        @unlink($mark_image);
    }
	
    /**
     * 通过gd库的方法生成image
     * @param string filename
     * @return resource 文件源对象
     */
    static function imagecreatefrom($file){
        list($w,$h,$type) = getimagesize($file);

        switch($type){
        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($file);
        case IMAGETYPE_GIF:
            return imagecreatefromgif($file);
        case IMAGETYPE_PNG:
            return imagecreatefrompng($file);
        }
    }

    /**
     * 得到目标水印的规格（长和宽）
     * @param string 原图的宽度
     * @param string 原图的高度
     * @param string 水印图片的宽度
     * @param string 水印图片的高度
     * @param string 水印图片的位置
     * @return array 目标图片的规格（长和宽）
     */
    static function get_watermark_dest($src_w,$src_h,$wm_w,$wm_h,$loc){
        switch($loc{0}){
        case 't':
            $dest_y = ($src_h - 5 >$wm_h)?5:0;
            break;
        case 'm':
            $dest_y = floor(($src_h - $wm_h)/2);
            break;
        default:
            $dest_y = ($src_h - 5 >$wm_h)?($src_h - $wm_h - 5):0;
        }

        switch($loc{1}){
        case 'l':
            $dest_x = ($src_w - 5 >$wm_w)?5:0;
            break;
        case 'c':
            $dest_x = floor(($src_w - $wm_w)/2);
            break;
        default:
            $dest_x = ($src_w - 5 >$wm_w)?($src_w - $wm_w - 5):0;
        }

        return array($dest_x,$dest_y);
    }
}
