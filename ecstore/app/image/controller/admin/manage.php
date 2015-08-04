<?php
 
/**
 * 后台图片管理类
 */
class image_ctl_admin_manage extends desktop_controller 
{
    /**
     * @var 定义控制器属于哪个菜单区域
     */
    var $workground = 'image_ctl_admin_manage';

    /**
     * act==index页面入口
     * @param null
     * @return string html内容
     */
    function index(){
        $action = array(
                array('label'=>app::get('image')->_('上传新图片'),'href'=>'index.php?app=image&ctl=admin_manage&act=image_swf_uploader'
                            ,'target'=>'dialog::{title:\''.app::get('image')->_('上传图片').'\',width:500,height:350}'),
                array('label'=>app::get('image')->_('添加网络图片'),'href'=>'index.php?app=image&ctl=admin_manage&act=image_www_uploader'
                            ,'target'=>'dialog::{title:\''.app::get('image')->_('添加网络图片').'\',width:550,height:200}'),
                array('label'=>app::get('image')->_('水印与缩略图'),'submit'=>'index.php?app=image&ctl=admin_manage&act=rebuild'
                            ,'target'=>'dialog::{title:\''.app::get('image')->_('水印与尺寸').'\',width:500,height:300}'),
                //array('label'=>'切换存储引擎','submit'=>'index.php?ctl=image&act=ch_storage'
                //            ,'target'=>'dialog::{title:\'切换存储引擎\',width:300,height:300}'),
            );
        $this->finder('image_mdl_image',array(
            'title'=>app::get('image')->_('图片管理'),
            'actions'=>$action,
            'use_buildin_set_tag'=>true,
            'use_buildin_filter'=>true,
            'use_buildin_tagedit'=>true
        ));
    }
     public function _views(){
        $sub_menu = array(
            0=>array('label'=>app::get('image')->_('全部'),'optional'=>false,'filter'=>array()),
            1=>array('label'=>app::get('image')->_('平台图片'),'optional'=>false,'filter'=>array('store_id'=>'0')),
            2=>array('label'=>app::get('image')->_('店铺图片'),'optional'=>false,'filter'=>array('store_id|noequal'=>'0')),
            
        );
        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                $show_menu[$k]['addon'] = $this->app->model('image')->count($v['filter']);
                $show_menu[$k]['href'] = 'index.php?app=image&ctl=admin_manage&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }
        }
        return $show_menu;
     }
    /**
     * 显示上传swf的入口
     * @param null
     * @return string html
     */
    function image_swf_uploader(){
       $mdl_img = $this->app->model('image');
       $this->pagedata['currentcount'] = $mdl_img->count();
       $this->pagedata['ssid'] =  kernel::single('base_session')->sess_id();
        $this->pagedata['IMAGE_MAX_SIZE'] = IMAGE_MAX_SIZE;
       $this->display('image_swf_uploader.html');
    }

    /**
     * 执行图片重新生成的入口
     * @param null
     * @return null
     */
    function execu(){
        $o = new image_rebuild();
        $a = array (
          'filter' => 
          array (
            'image_id' => 
            array (
              0 => '8846e250a4234fb517cb81540eeef3b2',
            ),
          ),
          'watermark' => 'true',
          'size' => 
          array (
            0 => 'L',
            1 => 'M',
            2 => 'S',
          ),
          'queue_time' => 1279776055,
        );
        $c = 1;
        $o->run($c,$a);
    }    

    /**
     * 图片上传的接口
     * @param null
     * @return string 上传的消息
     */
    function image_upload(){
       
       $mdl_img   = $this->app->model('image');
       $image_name = $_FILES['upload_item']['name'];
       $image_id  = $mdl_img->store($_FILES['upload_item']['tmp_name'],null,null,$image_name);
       if(!$image_id) {
            header('Content-Type:text/html; charset=utf-8');
            echo "{error:'".app::get('image')->_('图片上传失败')."',splash:'true'}";
            exit;
       }
       //非商品图片不生成小中大图。
//       $mdl_img->rebuild($image_id,array('L','M','S'));
     
       if(isset($_REQUEST['type'])){
            $type=$_REQUEST['type'];
       }else{
            $type='s';
       }
           
       $image_src = base_storager::image_path($image_id,$type);
      
       $this->_set_tag($image_id);
       if($callback = $_REQUEST['callbackfunc']){
            
             $_return = "<script>try{parent.$callback('$image_id','$image_src')}catch(e){}</script>";
       
       }
       
       $_return.="<script>parent.MessageBox.success('".app::get('image')->_('图片上传成功')."');</script>";

       echo $_return;
    
    }

    /**
     * 设置图片的tag-本类私有方法
     * @param null
     * @return null
     */
    function _set_tag($image_id){
       $tagctl   = app::get('desktop')->model('tag');
       $tag_rel   = app::get('desktop')->model('tag_rel');
       $data['rel_id'] = $image_id;
       $tags = explode(' ',$_POST['tag']['name']);
       $data['tag_type'] = 'image';
       $data['app_id'] = 'image';
       //add buy Huoxh 2013-08-15
       $data['store_id'] = '0';
       foreach($tags as $key=>$tag){
           if(!$tag) continue;
            $data['tag_name'] = $tag;
            $tagctl->save($data);
            if($data['tag_id']){
                $data2['tag']['tag_id'] = $data['tag_id'];
                $data2['rel_id'] = $image_id;
                $data2['tag_type'] = 'image';
                $data2['app_id'] = 'image';
                $tag_rel->save($data2);
                unset($data['tag_id']);
            }
       }
    }

    /**
     * 上传网络图片地址-本类私有方法
     * @param null
     * @return string html内容
     */
    function image_www_uploader(){
        if($_POST['upload_item']){
            $image = $this->app->model('image');
            $image_name = substr(strrchr($_POST['upload_item'],'/'),1);
            $image_id = $image->store($_POST['upload_item'],null,null,$image_name);
            $image_src = base_storager::image_path($image_id);
            $this->_set_tag($image_id);
            if($callback = $_REQUEST['callbackfunc']){
                
                 $_return = "<script>try{parent.$callback('$image_id','$image_src')}catch(e){}</script>";

            }

            $_return.="<script>parent.MessageBox.success('".app::get('image')->_('图片上传成功')."');</script>";

            echo $_return;
            echo <<<EOF
<div id="upload_remote_image"></div>
<script>
try{
    if($('upload_remote_image').getParent('.dialog'))
    $('upload_remote_image').getParent('.dialog').retrieve('instance').close();
}catch(e){}
</script>
EOF;
        }else{
            $html  ='<div class="division"><h5>'.app::get('image')->_('网络图片地址：').'</h5>';
            $ui = new base_component_ui($this);
            $html .= $ui->form_start(array('method'=>'post'));
            $html .= $ui->input(array(

                'type'=>'url',
                'name'=>'upload_item',
                'value'=>'http://',
                
                'style'=>'width:70%'
                ));
            $html .='</div>';
            $html .= $ui->form_end();
            echo $html."";

        }
    }
    
    /**
     * 远程swf的页面显示
     * @param null
     * @return string html内容
     */
    function image_swf_remote(){
        $image = $this->app->model('image');
        $image_name = $_FILES['Filedata']['name'];
        $image_id = $image->store($_FILES['Filedata']['tmp_name'],null,null,$image_name);
        $this->pagedata['image_id'] = $image_id;
        
        header('Content-Type:text/html; charset=utf-8');
        echo $this->fetch('image_swf_uploader_reponse.html');
    
    }
    
    /**
     * 动态的swf页面显示
     * @param null
     * @return string html内容
     */
    function gimage_swf_remote(){
                
        $image = $this->app->model('image');
        $image_name = $_FILES['Filedata']['name'];
       
        $image_id = $image->store($_FILES['Filedata']['tmp_name'],null,null,$image_name);
        
        $image->rebuild($image_id,array('L','M','S'));
        
        $this->pagedata['gimage']['image_id'] = $image_id;
        
        header('Content-Type:text/html; charset=utf-8');
        echo $this->fetch('gimage.html');
        
        
    }
    
    /**
     * 图片浏览器
     * @param int 第几页的页面
     * @return string html内容
     */
    function image_broswer($page=1){

        $pagelimit = 10;

        $otag = app::get('desktop')->model('tag');
        $oimage = $this->app->model('image');
        $tags = $otag->getList('*',array('tag_type'=>'image','store_id'=>'0'));
    
        $this->pagedata['type'] = $_GET['type'];
        $this->pagedata['tags'] = $tags;
        $this->display('image_broswer.html');
    
    }

    /**
     * 图片管理列表内容显示
     * @param string 图片的tag
     * @param int 第几页的页面
     * @return string html内容
     */
    function image_lib($tag='',$page=1){
        $pagelimit = 12;

        //$otag = $this->app->model('tag');
        $oimage = $this->app->model('image');

        //$tags = $otag->getList('*',array('tag_type'=>'image'));
        $filter = array();
       
        $filter['store_id']='0';
        
        if($tag){
            $filter = array('tag'=>array($tag));
        }
        $images = $oimage->getList('*',$filter,$pagelimit*($page-1),$pagelimit);
        $count = $oimage->count($filter);

        $limitwidth = 100;
        
        
        

        foreach($images as $key=>$row){
            $maxsize = max($row['width'],$row['height']);
            if($maxsize>$limitwidth){
                $size ='width=';
                $size.=$row['width']-$row['width']*(($maxsize-$limitwidth)/$maxsize);
                $size.=' height=';
                $size.=$row['height']-$row['height']*(($maxsize-$limitwidth)/$maxsize);
            }else{
                $size ='width='.$row['width'].' height='.$row['height'];
            }
            $row['size'] = $size;
            $images[$key] = $row;
        }

        $this->pagedata['images'] = $images;
        $ui = new base_component_ui($this->app);
        $this->pagedata['pagers'] = $ui->pager(array(
            'current'=>$page,
            'total'=>ceil($count/$pagelimit),
            'link'=>'index.php?app=image&ctl=admin_manage&act=image_lib&p[0]='.$tag.'&p[1]=%d',
            ));
         $this->display('image_lib.html');

     }
    

    function ch_storage(){
    
        
    
    }

    /**
     * 删除图片
     * @param nulll
     * @return string 图片删除信息json
     */
    function image_del(){
        $image_id = $_GET['image_id'];
        $oimage = $this->app->model('image');
        if($oimage->delete(array('image_id'=>$image_id))){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('image')->_('删除成功').'"}';  
        }
   }

    /**
     * 重新生成图片入口
     * @param nulll
     * @return string html js刷新finder
     */
    function rebuild(){
        $ui = new base_component_ui($this);
        if($_POST['size']){
            $queue = app::get('base')->model('queue');
            parse_str($_POST['filter'],$filter);
            $data = array(
                'queue_title'=>app::get('image')->_('重新生成图片'),
                'start_time'=>time(),
                'params'=>array(
                    'filter'=>$filter,
                    'watermark'=>$_POST['watermark'],
                    'size'=>$_POST['size'],
                    'queue_time'=>time(),
                ),
                'worker'=>'image_rebuild.run',
                );
            $queue->insert($data);
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{success:"'.app::get('image')->_('执行成功').'"}';
        }else{
            $html .= $ui->form_start(array('id'=>'rebuild_form','method'=>'post'));
            $size = array(
                'L'=>app::get('image')->_('大图'),
                'M'=>app::get('image')->_('中图'),
                'S'=>app::get('image')->_('小图'),
            );
            foreach($size as $k=>$v){
                $html .= $ui->form_input(array(
                    'title'=>app::get('image')->_('生成').$v,
                    'type'=>'checkbox',
                    'name'=>'size[]',
                    'value'=>$k,
                    'checked'=>'checked',
                    ));
            }

            $html.='<tr><td colspan="2" style="height:1px;background:#ccc;overflow:hidden;padding:0"></td><tr>';

            $filter = $_POST;
            unset($filter['_finder']);
            $filter = htmlspecialchars(utils::http_build_query($filter));

            $html .= $ui->form_input(array(
                'title'=>app::get('image')->_('使用水印'),
                'type'=>'bool',
                'name'=>'watermark',
                'value'=>1,
                ));
            $html.='<tr><td><input type="hidden" name="filter" value="'.$filter.'" /></td></tr>';

            $html .=$ui->form_end();
            echo $html;
            echo <<<EOF
<script>
   $('rebuild_form').store('target',{
        
        onComplete:function(){
                 $('rebuild_form').getParent('.dialog').retrieve('instance').close();
             
        }
   
   });

</script>
EOF;
        }
    }

    /**
     * 图片大小配置
     * @param nulll
     * @return string 显示配置页面内容
     */
    function imageset(){
        header("cache-control: no-store, no-cache, must-revalidate");
        $image = &app::get('image')->model('image');

       $allsize = array();
        if($_POST['pic']){
            $image_set = $_POST['pic'];

            $cur_image_set = $this->app->getConf('image.set');


            foreach(kernel::servicelist('image_set') as $class_name=>$service){
                if($service instanceof image_interface_set){
                    if(method_exists($service,'setconfig')){
                       $service->setconfig($_POST);
                    }
                }
            }
            
            foreach($image_set as $size=>$item){
                if($item['wm_type']=='text'){
                    $image_id = '';
                    if($cur_image_set && $cur_image_set[$size] && $cur_image_set[$size]['wm_text_image']){
                        $image_id = $cur_image_set[$size]['wm_text_image'];
                    }
                    //生产文字水印图
					if(constant("ECAE_MODE")) {
						$tmpfile = tempnam(sys_get_temp_dir(),'img');
					} else {
						$tmpfile = tempnam(DATA_DIR,'img');
					}
                    $url = 'http://chart.apis.google.com/chart?chst=d_text_outline&chld=000000|20|h|ffffff|_|'.urlencode($item['wm_text']);
                    file_put_contents($tmpfile,file_get_contents($url));
                    $image_id = $image->store($tmpfile,$image_id,null,$item['wm_text']);

                    $image_set[$size]['wm_text_image'] = $image_id;
                }
            }
            $this->app->setConf('image.set',$image_set);

            $cur_image_set = $this->app->getConf('image.set');
            
        }
        $def_image_set = $this->app->getConf('image.default.set');
		
		$minsize_set = false;
		foreach($def_image_set as $k=>$v){
			if(!$minsize_set||$v['height']<$minsize_set['height']){
				$minsize_set = $v;
			}
		}
		

        $this->pagedata['allsize'] = $def_image_set;

		$this->pagedata['minsize'] = $minsize_set;
		
		
		
		

        $cur_image_set = $this->app->getConf('image.set');
        $this->pagedata['image_set'] = $cur_image_set;
        $this->pagedata['this_url'] = $this->url;
        $this->page('imageset.html');
    }

    /**
     * 查看图片
     * @param nulll
     * @return string html页面内容
     */
    function view_gimage($image_id){
      //  $oImage = $this->app->model('image');
        $this->pagedata['image_id'] = $image_id;
        $this->page('view_gimages.html');
    }

    /**
     * 配置好图片的预览
     * @param nulll
     * @return string html预览页面
     */
    function img_preview(){
        $size = $_GET['size']?$_GET['size']:'L';
        $setting = $_POST['pic'][$size];
        $w = $setting['width'];
        $h = $setting['height'];
        $storager = new base_storager();
        $mdl_img = $this->app->model('image');
        $img_row = $mdl_img->dump($setting['default_image']);

        $tmp_image_id = $mdl_img->gen_id();

        if($setting['wm_type']=='text'&&$setting['wm_text']){
            $url = 'http://chart.apis.google.com/chart?chst=d_text_outline&chld=000000|20|h|ffffff|_|'.urlencode($setting['wm_text']);
			if(constant("ECAE_MODE")) {
				$tmp_water_file = tempnam(sys_get_temp_dir(),'img');
			} else {
				$tmp_water_file = tempnam(DATA_DIR,'img');
			}
            file_put_contents($tmp_water_file,file_get_contents($url));
            $setting['wm_text_preview'] = true;
            $setting['wm_text_image'] = $tmp_water_file;
           
        }

        if($img_row['storage']=='network'){
			if(constant("ECAE_MODE")) {
				$tmp_file = tempnam(sys_get_temp_dir(),'img');
			} else {
				$tmp_file = tempnam(DATA_DIR,'img');
			}
            file_put_contents($tmp_file,file_get_contents($img_row['url']));
        }else{
            $tmp_file = $storager->worker->getFile($img_row['ident'],'image');
        }

    	if(constant("ECAE_MODE")) {
			$tmp_target = tempnam(sys_get_temp_dir(),'img');
		} else {
			$tmp_target = tempnam(DATA_DIR,'img');
		}
        image_clip::image_resize($mdl_img,$tmp_file,$tmp_target,$w,$h);
        if($setting['wm_type']!='none'&&($setting['wm_text']||$setting['wm_image'])){
            image_clip::image_watermark($mdl_img,$tmp_target,$setting);
        }
        $type = (getimagesize($tmp_target));
        if(file_exists($tmp_water_file))unlink($tmp_water_file);
        header("Content-Type: {$type[mime]}");
        readfile($tmp_target);
		@unlink($tmp_target);
        if($img_row['storage']=='network')@unlink($tmp_file);
    }
}//End Class

