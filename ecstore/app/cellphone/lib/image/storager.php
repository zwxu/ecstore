<?php

class cellphone_image_storager extends base_storager{
	
    static function modifier($image_id,$size=''){
        if(isset($image_id{31}) && !isset($image_id{32})){
            return '%IMG_'.$image_id.'_S_'.strtolower($size).'_IMG%';
        }else{
            return $image_id;
        }
    }
    static function image_path($image_id,$size=''){
        $tmp = self::modifier($image_id,strtolower($size));
        return self::image_storage($tmp);
    }
    static function image_storage($content){
        $blocks = preg_split('/%IMG_([0-9a-f]{32})_S_([a-z0-9\:]*)_IMG%/'
                    ,$content,-1,PREG_SPLIT_DELIM_CAPTURE);

        $c = count($blocks);

        $imglib = array();
        $img = array();

        for($i=0;$i<$c;$i++){
            switch($i%3){
                case 1:
                   $image_id = $blocks[$i];
                   $img[$image_id][$i] = array($blocks[$i+1]);
                   $blocks[$i] = &$img[$image_id][$i][0];
                    break;
               case 2:
                   $img[$image_id][$i-1][1] = $blocks[$i];
                   $blocks[$i] = '';
                   break;
            }
        }

        if(defined('HOST_MIRRORS')){
            $host_mirrors = preg_split('/[,\s]+/',constant('HOST_MIRRORS'));
            if(is_array($host_mirrors) && isset($host_mirrors[0])){
                $host_mirrors_count = count($host_mirrors)-1;
            }
            $url = $host_mirrors[rand(0,$host_mirrors_count)].'/'.$url;
        }

        if($img){
            $db = &kernel::database();
            foreach($db->select($s='select image_id,url,s_url,m_url,l_url,cs_url,cl_url,last_modified,width,height from sdb_image_image where image_id in(\''.
                    implode("','",array_keys($img)).'\')') as $r){
                $imglib[$r['image_id']] = $r;
            }

            foreach($img as $image_id => $sizes){
                foreach($sizes as $i=>$item){
                    switch(strtolower($item[0])){
                        case 's':
                            $url = $imglib[$image_id]['s_url']?$imglib[$image_id]['s_url']:$imglib[$image_id]['url'];
                            break;
                        case 'm':
                            $url = $imglib[$image_id]['m_url']?$imglib[$image_id]['m_url']:$imglib[$image_id]['url'];
                            break;
                        case 'l':
                            $url = $imglib[$image_id]['l_url']?$imglib[$image_id]['l_url']:$imglib[$image_id]['url'];
                            break;
                        case 'cs':
                            $url = $imglib[$image_id]['cs_url']?$imglib[$image_id]['cs_url']:'';
                            break;
                        case 'cl':
                            $url = $imglib[$image_id]['cl_url']?$imglib[$image_id]['cl_url']:'';
                            break;
                        default:
                            $url = $imglib[$image_id]['url'];
                            break;
                    }
                    if($url&&!strpos($url,'://')){
                        if(is_array($host_mirrors)){
                            $url = $host_mirrors[rand(0,$host_mirrors_count)].'/'.$url;
                        }else{
                        	$image_url = defined('IMG_URL') ? IMG_URL : kernel::base_url(1);
                            $url = $image_url.'/'.$url;
                        }
                    }
                    $code = ($r['width']>$r['height'])?'w':'h';
                    $img[$image_id][$i][0] = $url?($url.'?'.$imglib[$image_id]['last_modified'].'#'.$code):'';
                }
            }
        }

        return implode('',$blocks);
    }
   
  
}