<?php
 
/**
 * 这个类实现image表的实体
 */
class business_mdl_image extends image_mdl_image{
    public function __construct(&$app){
        $this->app_current = $app;
        $this->app_image = app::get('image');
        parent::__construct($this->app_image);
        
        $this->host_path = '';
        if(defined('HOST_MIRRORS')){
            /*$host_mirrors = preg_split('/[,\s]+/',constant('HOST_MIRRORS'));
            if(is_array($host_mirrors) && isset($host_mirrors[0])){
                $host_mirrors = &$host_mirrors;
                $host_mirrors_count = count($host_mirrors)-1;
                $this->host_path = $host_mirrors[rand(0,$host_mirrors_count)];            
            }*/
        }else{
            $this->host_path = ROOT_DIR;
        }
    }
	var $has_tag = true;
	var $has_many = array(
		'tag'=>'tag_rel@desktop:replace:image_id^rel_id'
	);
	/**
	 * @var array 定义这个实体查询列表默认的排序字段，排序方式
	 */
    var $defaultOrder = array('last_modified','desc');
    
    /**
     * 存储图片的信息的接口方法
     * @param string filename
     * @param string image_id唯一标识
     * @param string size规格类型
     * @param string 图片的名称
     * @param boolean 是否要大水印
     * @return stirng image_id唯一标识
     */
    function store($file,$image_id,$size=null,$name=null,$watermark=false,$store_id=0,$is_space=1){
        if(!$store_id) return false; 
        if(!defined(FILE_STORAGER))define('FILE_STORAGER','filesystem');
        list($w,$h,$t) = getimagesize($file);
  
        $extname = array(
                1 => '.gif',
                2 => '.jpg',
                3 => '.png',
                6 => '.bmp',
            );
    
        if(!isset($extname[$t])){
            return false;
        }

        if($image_id){
            $params = $this->dump($image_id);
            if($name)
                $params['image_name'] = $name;
            $params['image_id'] = $image_id;
        }else{
            $params['image_id'] = $this->gen_id();
            $params['image_name'] = $name;
            $params['storage'] = FILE_STORAGER;
        }
        if(!$image_id && substr($file,0,4)=='http'){
            $params['storage'] = 'network';
            $params['url'] = $file;
            $params['ident'] = $file;
            $params['width'] = $w;
            $params['height'] = $h;
            $params['store_id']=intval($store_id);
            $this->save($params);
            return $params['image_id'];
        }
        
        if($is_space)
        $fsize = abs(filesize($file));
        else
        $fsize = 0;
        $objstorem = app::get('business')->model('storemanger')->getList('store_space,store_usedspace',array('store_id'=>$store_id));
        if(!$objstorem || abs($objstorem[0]['store_space']) < ($usedspace = abs($objstorem[0]['store_usedspace']) + $fsize)){
            if($image_id){
                if(is_file($this->host_path.'/'.$params['url'])){
                    $this->file_rename($this->host_path.'/'.$params['url'], $this->host_path.'/recycle/'.$params['url']);
                }
                if(is_file($this->host_path.'/'.$params['l_url'])){
                    $this->file_rename($this->host_path.'/'.$params['l_url'], $this->host_path.'/recycle/'.$params['l_url']);
                }
                if(is_file($this->host_path.'/'.$params['m_url'])){
                    $this->file_rename($this->host_path.'/'.$params['m_url'], $this->host_path.'/recycle/'.$params['m_url']);
                }
                if(is_file($this->host_path.'/'.$params['s_url'])){
                    $this->file_rename($this->host_path.'/'.$params['s_url'], $this->host_path.'/recycle/'.$params['s_url']);
                }
                app::get('business')->model('storemanger')->db->exec('delete sdb_image_image where image_id='.intval($image_id));
                $dir_size = is_dir($this->host_path.'/public/images/store'.$store_id)?$this->getDirSize($this->host_path.'/public/images/store'.$store_id):0;
                app::get('business')->model('storemanger')->db->exec("update sdb_business_storemanger set store_usedspace = {$dir_size} where store_id=".intval($store_id));
            }
            return false;
        }
        

        $params['watermark'] = $watermark;
        if(is_bool($params['watermark'])){
        	$params['watermark'] = $params['watermark'] ? 'true' : 'false';
        }
        $storager = new base_storager($store_id);
        $params['last_modified'] = time();
        list($url,$ident,$no) = explode("|",$storager->save_upload($file,'image','',$msg,$extname[$t]));
        if($size){
            $size = strtolower($size);
            $params[$size.'_url'] = $url;
            $params[$size.'_ident'] = $ident;
        }else{
            $params['url'] = $url;
            $params['ident'] = $ident;
            $params['width'] = $w;
            $params['height'] = $h;
        }
        $params['store_id']=intval($store_id);
        parent::save($params);
        if($usedspace != abs($objstorem[0]['store_usedspace']))
        app::get('business')->model('storemanger')->db->exec("update sdb_business_storemanger set store_usedspace = {$usedspace} where store_id=".intval($store_id)); // add by cam
        return $params['image_id'];
    }
    
   
    function getDirSize($dir){ 
        $handle = opendir($dir);
        while (false!==($FolderOrFile = readdir($handle)))
        { 
            if($FolderOrFile != "." && $FolderOrFile != "..") 
            { 
                if(is_dir("$dir/$FolderOrFile"))
                { 
                    $sizeResult += getDirSize("$dir/$FolderOrFile"); 
                }
                else
                { 
                    $sizeResult += filesize("$dir/$FolderOrFile"); 
                }
            }    
        }
        closedir($handle);
        return $sizeResult;
    }
    function file_rename($source,$dest){
        if(is_file($dest)){
            if(PHP_OS=='WINNT'){
                @copy($source,$dest);
                @unlink($source);
                if(file_exists($dest)) return true;
                else return false;
            }else{
                return @rename($source,$dest);
            }
        }else{
            return false;
        }
    }
    

	/**
	 * 给图片打水印的接口
	 * @param string image_id唯一标识
	 * @param string size规格类型
	 * @param boolean 是否打水印
	 * @return null
	 */
    function rebuild($image_id,$sizes,$watermark=true,$store_id,$is_space=1){
        if(!$image_id) return false; 
        $storager = new base_storager($store_id);

        if($sizes){

            $cur_image_set = $this->app->getConf('image.set');
            $allsize = $this->app->getConf('image.default.set');
           
            foreach(kernel::servicelist('image_set') as $class_name=>$service){
                if($service instanceof image_interface_set){
                    if(method_exists($service,'getconfig')){
                        $service->getconfig($sizes, $cur_image_set, $allsize);
                    }
                }
            }
           
            $this->watermark_define = array();
            $this->watermark_default = '';

			if(constant("ECAE_MODE")) {
				$tmp_target = tempnam(sys_get_temp_dir(),'img');
			} else {
				$tmp_target = tempnam(DATA_DIR,'img');
			}
            $img = $this->dump($image_id);
            if(is_array($img))  $org_file = $img['url'];

            if(substr($org_file,0,4)=='http'){

                if($img['storage']=='network'){
                    $response = kernel::single('base_httpclient')->get($org_file);
                    if($response===false){
                        $data = array('image_id'=>$image_id,'last_modified'=>time());
                        parent::save($data);
                        return true;                    
                    }
                    $image_content = $response;
                }else{
                    $image_file = $storager->worker->getFile($img['ident'],'image');
                    if(!$image_file) return false;
                    $image_content = file_get_contents($image_file);
                }
				if(constant("ECAE_MODE")) {
					$org_file = tempnam(sys_get_temp_dir(), 'imgorg');
				} else {
					$org_file = tempnam(DATA_DIR, 'imgorg');
				}
            	file_put_contents($org_file, $image_content);
           }

            if(!file_exists($org_file)){
                $data = array('image_id'=>$image_id,'last_modified'=>time());
               // parent::save($data);
                return true;
            }
           foreach($sizes as $s){

                if(isset($allsize[$s])){

                    $w = $cur_image_set[$s]['width'];
                    $h = $cur_image_set[$s]['height'];
                    $wh = $allsize[$s]['height'];
                    $wd = $allsize[$s]['width'];
                    $w = $w?$w:$wd;
                    $h = $h?$h:$wh;
                    image_clip::image_resize($this,$org_file,$tmp_target,$w,$h);
                    if($watermark&&$cur_image_set[$s]['wm_type']!='none'&&($cur_image_set[$s]['wm_text']||$cur_image_set[$s]['wm_image'])){
                        $watermark = true;
                        image_clip::image_watermark($this,$tmp_target,$cur_image_set[$s]);
                    }
                    $this->store($tmp_target,$image_id,$s,null,$watermark,$store_id,$is_space);
					/** 删除指定规格图片 **/
					@unlink($this->host_path.'/'.$img[strtolower($s).'_url']);
                }
            }
            @unlink($tmp_target);
            if(strpos($org_file,'imgorg')!==false)@unlink($org_file);
         }
    }
	
    /**
     * 下载或者获取一张图片
     * @param string image_id唯一标识
     * @param string size规格
     * @return mixed 成功拿到一张图片文件，失败false
     */
    function fetch($image_id,$size=null){
        $img = $this->dump($image_id);
        $k = $size?(strtolower($size).'_ident'):'ident';
        if($img['storage']=='network'){
			$response = kernel::single('httpclient')->get($org_file);
            if($response===false){
                $data = array('image_id'=>$image_id,'last_modified'=>time());
                parent::save($data);
                return true;                    
            }
            $image_content = $response;
        }else{
            $storager = new base_storager();
            $image_file = $storager->worker->getFile($img[$k],'image');
            $image_content = file_get_contents($image_file);
        }
		if(constant("ECAE_MODE")) {
			$target_file = tempnam(sys_get_temp_dir(), 'targetfile');
		} else {
			$target_file = tempnam(DATA_DIR, 'targetfile');
		}
        file_put_contents($target_file, $image_content);
        return $target_file;
    }
	
    function attach($image_id,$target_type,$target_id){
    }
	
    /**
     * 生成image的唯一标识的image_id
     * @param null
     * @return string image_id
     */
    function gen_id(){
        return md5(rand(0,9999).microtime());
    }
	
    /**
     * 获取所有的引擎信息（目前为实现）
     * @param null
     * @return mixed 引擎信息
     */
    function all_storages(){
        return; 
    }
	
   /**
    * 修改引擎列的信息（finder）
    * @param array 一行数据
    * @return null
    */
    function modifier_storage(&$list){
        $all_storages = $this->all_storages();
        $all_storages['network'] = app::get('image')->_('远程');
        $list = (array)$list;
        foreach($list as $k=>$v){
            $list[$k] = $all_storages[$k];
        }
    }
	
	/**
	 * 删除图片image_id
	 * @param string image_id
	 * @param string target_type
	 * @return boolean
	 */
	public function delete_image($image_id,$target_type,$store_id,$allow=1)
	{
		if (!$image_id || !$target_type) return true;
    if(!$store_id) return false;
		
		/** 商品图片资源被其他模块关联就不需要删除了 **/
		$filter = array(
			'image_id'=>$image_id,
			'target_type|ne'=>$target_type,
		);
		$obj_image_attachment = $this->app->model('image_attach');
		$tmp = $obj_image_attachment->getList('*',$filter);
		if ($tmp) return true;
		
		$tmp = $this->getList('*',array('image_id'=>$image_id,'storage'=>'filesystem'));
		if ($tmp){
      $fsize = 0;
			if ($allow && file_exists($this->host_path.'/'.$tmp[0]['url'])){
        $fsize += abs(filesize($this->host_path.'/'.$tmp[0]['url']));
				@unlink($this->host_path.'/'.$tmp[0]['url']);
      }
			if (file_exists($this->host_path.'/'.$tmp[0]['l_url'])){
        $fsize += abs(filesize($this->host_path.'/'.$tmp[0]['l_url']));
        @unlink($this->host_path.'/'.$tmp[0]['l_url']);
      }
			if (file_exists($this->host_path.'/'.$tmp[0]['m_url'])){
        $fsize += abs(filesize($this->host_path.'/'.$tmp[0]['m_url']));
        @unlink($this->host_path.'/'.$tmp[0]['m_url']);
      }
			if (file_exists($this->host_path.'/'.$tmp[0]['s_url'])){
        $fsize += abs(filesize($this->host_path.'/'.$tmp[0]['s_url']));
        @unlink($this->host_path.'/'.$tmp[0]['s_url']);
      }
    
      foreach(kernel::servicelist('image_set') as $class_name=>$service){
          if($service instanceof image_interface_set){
              if(method_exists($service,'delete_image')){
                  $service->delete_image($tmp, $host, $fsize);
              }
          }
      }
     
      
      $objstorem = app::get('business')->model('storemanger')->getList('store_space,store_usedspace',array('store_id'=>$store_id));
      $usedspace = abs($objstorem[0]['store_usedspace']) - $fsize;
      $usedspace = $usedspace<0?0:$usedspace;
      app::get('business')->model('storemanger')->db->exec("update sdb_business_storemanger set store_usedspace = {$usedspace} where store_id=".intval($store_id));
		}
    if(!$allow) return;
		return $this->delete(array('image_id'=>$image_id,'storage'=>'filesystem'));
	}
}
