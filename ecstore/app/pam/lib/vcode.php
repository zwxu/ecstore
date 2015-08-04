<?php

/**
*这是一个生成验证码图片的类，已经移到base下面了
*/
class pam_vcode{
	
	/**
	* 图片的生成格式。png或者其他
	* @var bool
	*/
    var $use_gd = false;
    /**
	*初始化生成图片
	*@param int $m 生成图片的长度。默认为8，相当于8个数字
	@return string 生成验证码的数字串
	*/
    function init($m = 8){
        if(false && function_exists('imagecreatefrompng')){
            $this->use_gd = true;
            $codeDir= DATA_DIR.'/vcode';

            if ($handle = opendir($codeDir)) {
                    while (false !== ($file = readdir($handle))) {
                            if (substr($file,-4)=='.png') {
                                $lib[] = substr($file,0,-4);
                            }
                    }
                    closedir($handle);
            }
            $n = count($lib)-1;
            $str = '';
            for($i=0;$i<$m;$i++){
                $str.=$c = $lib[rand(0,$n)];
                $ret[] = $codeDir.'/'.$c.'.png';
            }
            $this->ret = &$ret;
        }else{
            $this->softGif = new pam_softvcode;
            $str = $this->softGif->init();
        }

        return $str;
    }
	
	/**
	*输出显示验证码
	*/
    function output(){
        if($this->use_gd){
             $this->gd_merge();
        }else{
            $this->softGif->output();
        }
    }
	/**
	*生成验证码图片
	*/
    function gd_merge(){
        $arr = $this->ret;
        $bg = DATA_DIR.'/vcodebg.png';
        $image = imagecreatefrompng($bg); 
        list($w, $baseH) = getimagesize($bg);

        header('Content-type: image/png');
        $x = 1;

        foreach($arr as $i=>$filename){
            list($w, $h) = getimagesize($filename);
            $source = imagecreatefrompng($filename);
            $t_id = imagecolortransparent($source);
            $rotate = imagerotate($source, rand(-20,20),$t_id);
            $w2 = $w*$baseH/$h;
            imagecopyresized($image, $rotate, $x, 0, 0, 0, $w2, $baseH, $w, $h);
            imagedestroy($source);
            imagedestroy($rotate);
            $x+=$w2;
        }
        $x+=1;

        $dst = imagecreatetruecolor($x, $baseH);
        imagecopyresampled($dst, $image, 0, 0, 0, 0, $x, $baseH, $x, $baseH);
        imagepng($dst);
        imagedestroy($image);
        imagedestroy($dst);
        exit();
    }

}
?>
