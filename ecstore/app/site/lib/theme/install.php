<?php



class site_theme_install{

    public function check_install(){
        $this->check_dir();
        $d = dir(THEME_DIR);
        while (false !== ($entry = $d->read())) {
            if(in_array($entry, array('.', '..', '.svn')))   continue;
            if(is_dir(THEME_DIR . '/' . $entry)&&!ECAE_MODE){
                $themeData = app::get('site')->model('themes')->select()->where('theme = ?', $entry)->instance()->fetch_row();
                if(empty($themeData)){
                    $this->init_theme($entry);
                }
            }
            if(!kernel::single('site_theme_base')->get_default()){
                kernel::single('site_theme_base')->set_default($entry);
            }
        }
        $d->close();
    }//End Function

    public function check_dir()
    {
        if(!is_dir(THEME_DIR))
            utils::mkdir_p(THEME_DIR);
    }//End Function

    public function allow_upload(&$message){
        if(!function_exists("gzinflate")){
            $message = 'gzip';
            return $message;
        }
        if(!is_writable(THEME_DIR)){
            $message = 'writeable';
            return $message;
        }
        return true;
    }

    public function remove_theme($theme){
        $dir = THEME_DIR . '/' . $theme;
        $this->flush_theme($theme);
        kernel::single('site_theme_base')->delete_theme_widgets($theme);        //todo:删除模板挂件
        kernel::single('site_theme_base')->set_last_modify();
        if(ECAE_MODE){
            $this->__remove_db_theme($theme);
            return true;
        }
        if(is_dir($dir)){
            $this->__remove_db_theme($theme);//删除theme_files的模板文件
            return $this->__remove_dir($dir);
        }else{
            return true;
        }
    }//End Function

    public function flush_theme($theme){
        app::get('site')->model('themes')->delete(array('theme'=>$theme));
        kernel::single('site_theme_tmpl')->delete_tmpl_by_theme($theme);
        kernel::single('site_theme_widget')->delete_widgets_by_theme($theme);
        // kernel::single('site_widget_proinstance')->delete_instances_by_theme($theme);
    }//End Function

    public function install($file, &$msg){
        $this->check_dir();
        if(!$this->allow_upload($msg)) return false;
        $tar = kernel::single('base_tar');
        $handle = fopen($file['tmp_name'], "r");
        $contents = file_get_contents($file['tmp_name']);
        preg_match('/\<id\>([a-zA-Z0-9]*)(.*?)\<\/id\>/',$contents,$tar_name);
        $filename=$tar_name[1]?$tar_name[1]:time();
        if(is_dir(THEME_DIR.'/'.trim($filename))){
           $filename=time();
        }
        $sDir=$this->__build_dir(str_replace('\\','/',THEME_DIR.'/'.trim($filename)));
        if($tar->openTAR($file['tmp_name'], $sDir)){
            if($tar->containsFile('theme.xml')) {
                //提前实例化，通过引用传递，减少foreach中循环实例化类导致的开销
                $files = app::get('base')->model('files');
                $obj = app::get('site')->model('themes_file');
                $storager = kernel::single('base_storager');
                foreach($tar->files as $id => $file) {
                    $fpath = $sDir.$file['name'];
                    if(!is_dir(dirname($fpath))){
                        if(mkdir(dirname($fpath), 0755, true)){
                            file_put_contents($fpath,$tar->getContents($file));
                        }else{
                            $msg = app::get('site')->_('权限不允许');
                            return false;
                        }
                    }else{
                        file_put_contents($fpath,$tar->getContents($file));
                    }

                    if(ECAE_MODE==true){
                        //ecae环境下保存模板文件到ecae系统和数据库中
                        $this->ecae_theme_file_save($sDir, $file, $filename,$tar,$files,$obj,$storager);
                    }
                }

                $tar->closeTAR();
                if(!$config=$this->init_theme(basename($sDir),'','upload')){
                    $this->__remove_dir($sDir);
                    $msg=app::get('site')->_('模板包创建失败');
                    return false;
                }

                kernel::single('site_theme_base')->install_theme_widgets($filename);        //todo:安装模板挂件

                foreach(kernel::servicelist('site_theme.post_install') AS $service){
                    if(is_object($service) && method_exists($service, 'post_theme_install')){
                        $service->post_theme_install($filename);
                    }
                }
                if(ECAE_MODE){
                    $this->__remove_dir($sDir);
                }
                return $config;
            }else{
                $msg = app::get('site')->_('不是标准的模板包');
                return false;
            }
        }else{
            $msg = app::get('site')->_('模板包已损坏，不是标准的模板包').$file['tmp_name'];
            return false;
        }
    }

    //ecae环境下保存模板文件到ecae系统和数据库中
    private function ecae_theme_file_save($sDir, $file, $filename,&$tar,&$files,&$obj,&$storager){
        $arr_fext = $this->get_file_ext($file['name']);
        if(!$arr_fext) continue;
        $fext = $arr_fext['ext'];
        $fmemo = $arr_fext['memo'];

        if($fext=='html' || $fext=='xml' || preg_match('/\.php/',$file['name'])){
            $file_content = $tar->getContents($file);
        }elseif($fext=='js' || $fext=='css'){
            $index = $file['name'];
            $fpath = $sDir.$file['name'];
            $file_content = $tar->getContents($file);
            file_put_contents($fpath,$file_content);
            $addons = array('name'=>basename($index),'path'=>dirname('/theme/'.$filename.'/'.$index).'/');
            $file_content = $storager->save($fpath,'file',$addons);
            $save_file = array('file_path'=>$file_content,'file_type'=>'public');
            $files->save($save_file);
        }else{//image
            $index = $file['name'];
            $fpath = $sDir.$file['name'];
            $addons = array('name'=>basename($index),'path'=>dirname('/theme/'.$filename.'/'.$index).'/');
            $file_content = $storager->save($fpath,'file',$addons);
            $save_file = array('file_path'=>$file_content,'file_type'=>'public');
            $files->save($save_file);
        }

        $save_data = array(
            'fileuri'=>$filename.':'.$file['name'],
            'filename'=>$file['name'],
            'theme'=>$filename,
            'content'=>$file_content,
            'filetype'=>$fext,
            'memo'=>$fmemo,
            );
        $obj->save($save_data);
    }

    public function init_theme($theme, $replaceWg=false, $upload='', $loadxml=''){
        if(empty($loadxml)){
            $loadxml='theme.xml';
        }
        $sDir=THEME_DIR.'/'.$theme.'/';
        $xml = kernel::single('site_utility_xml');
        $loadxml_content = kernel::single('site_theme_tmpl_file')->get_xml_content($theme, $sDir, $loadxml);
        if($loadxml_content){
            $wightes_info = $xml->xml2arrayValues($loadxml_content);
        }
        if(!empty($wightes_info)){
            $config = $wightes_info;
        }else{
            return false;
        }

        if(ECAE_MODE){
            $model_file = app::get('site')->model('themes_file');
            $filter = array('theme'=>$theme);
            $file_rows = $model_file->getList('*',$filter);
            $http = kernel::single('base_httpclient');
            $http->set_timeout(10);
            $storager = kernel::single('base_storager');
            $theme_dir = THEME_DIR.'/'.$theme;
            if(!is_dir($theme_dir)){
                mkdir($theme_dir,0777);
            }

            foreach($file_rows as $file){
                if(!is_dir(dirname($theme_dir.'/'.$file['filename']))){
                    mkdir(dirname($theme_dir.'/'.$file['filename']),0777,true);
                }
                if($file['filetype']=='jpg'||$file['filetype']=='png'||$file['filetype']=='gif'||$file['filetype']=='jpeg'||$file['filetype']=='js'||$file['filetype']=='css'){
                    $ident = $storager->parse($file['content']);
                    $url = $ident['url'];
                    $content = $http->action(__FUNCTION__,$url,null,null,array());
                }else{
                    $content = $file['content'];
                }
                file_put_contents($theme_dir.'/'.$file['filename'],$content);
            }
        }

        if($upload=="upload" && $config['theme']['id']['value']){
            $config['theme']['id']['value']=preg_replace('@[^a-zA-Z0-9]@','_',$config['theme']['id']['value']);
            if($this->file_rename(THEME_DIR.'/'.$theme,THEME_DIR.'/'.$config['theme']['id']['value'])){
                $sDir=THEME_DIR.'/'.$config['theme']['id']['value'];
                $theme=$config['theme']['id']['value'];
                $replaceWg=false;
            }
        }
        $aTheme=array(
            'name'=>$config['theme']['name']['value'],
            'id'=>$config['theme']['id']['value'],
            'version'=>$config['theme']['version']['value'],
            'info'=>$config['theme']['info']['value'],
            'author'=>$config['theme']['author']['value'],
            'site'=>$config['theme']['site']['value'],
            'update_url'=>$config['theme']['update_url']['value'],
            'config'=>array(
                'config'=>$this->__change_xml_array($config['theme']['config']['set']),
                'borders'=>$this->__change_xml_array($config['theme']['borders']['set']),
                'views'=>$this->__change_xml_array($config['theme']['views']['set'])
            )
        );

        $aWidgets=$wightes_info['theme']['widgets']['widget'];
        if(isset($aWidgets['value'])){
            $aWidgetsTmep = $aWidgets;
            unset($aWidgets);
            $aWidgets[0] = $aWidgetsTmep;
        }
        // $aWidgetsProinstance = $wightes_info['theme']['proinstances']['instance'];
        // if(isset($aWidgetsProinstance['value'])){
        //     $aWidgetsTmep = $aWidgetsProinstance;
        //     unset($aWidgetsProinstance);
        //     $aWidgetsProinstance[0] = $aWidgetsTmep;
        // }
        $aTheme['theme']=$theme;
        $aTheme['stime']=time();

        //todo: views
        $views = array();
        if(is_array($aTheme['config']['views']) && count($aTheme['config']['views'])>0){
            foreach($aTheme['config']['views'] as $v){
                $views[$v['app']][$v['view']] = $v['tpl'];
            }
        }

        for($i=0;$i<count($aWidgets);$i++){
            if($aWidgets[$i]['attr']['coreid']) {
                $aTmp[$i]['core_file']=$aTheme['theme'].'/'.$aWidgets[$i]['attr']['file'];
                $aTmp[$i]['core_slot']=$aWidgets[$i]['attr']['slot'];
                $aTmp[$i]['core_id']=$aWidgets[$i]['attr']['coreid'];
            } else {
                $aTmp[$i]['base_file']='user:'.$aTheme['theme'].'/'.$aWidgets[$i]['attr']['file'];
                $aTmp[$i]['base_slot']=$aWidgets[$i]['attr']['slot'];
                $aTmp[$i]['base_id']=$aWidgets[$i]['attr']['baseid'];
            }
            $aTmp[$i]['widgets_type']=$aWidgets[$i]['attr']['type'];
            $aTmp[$i]['widgets_order']=$aWidgets[$i]['attr']['order'];
            $aTmp[$i]['title']=$aWidgets[$i]['attr']['title'];
            $aTmp[$i]['domid']=$aWidgets[$i]['attr']['domid'];
            $aTmp[$i]['border']=$aWidgets[$i]['attr']['border'];
            $aTmp[$i]['classname']=$aWidgets[$i]['attr']['classname'];
            $aTmp[$i]['tpl']=$aWidgets[$i]['attr']['tpl'];
            $aTmp[$i]['app']=($aWidgets[$i]['attr']['app']) ? $aWidgets[$i]['attr']['app'] : ((empty($aWidgets[$i]['attr']['theme'])) ? 'b2c' : '');
            $aTmp[$i]['theme']=(empty($aWidgets[$i]['attr']['theme']))?'':$theme;

            $params = unserialize($aWidgets[$i]['value']);
            $aTmp[$i]['params']= $params;
        }
        $aWidgets=$aTmp;

        // for($i=0;$i<count($aWidgetsProinstance);$i++){
        //     $iTmp[$i]['name']=$aWidgetsProinstance[$i]['attr']['name'];
        //     $iTmp[$i]['memo']=$aWidgetsProinstance[$i]['attr']['memo'];
        //     $iTmp[$i]['widgets_type']=$aWidgetsProinstance[$i]['attr']['type'];
        //     $iTmp[$i]['title']=$aWidgetsProinstance[$i]['attr']['title'];
        //     $iTmp[$i]['domid']=$aWidgetsProinstance[$i]['attr']['domid'];
        //     $iTmp[$i]['border']=$aWidgetsProinstance[$i]['attr']['border'];
        //     $iTmp[$i]['classname']=$aWidgetsProinstance[$i]['attr']['classname'];
        //     $iTmp[$i]['tpl']=$aWidgetsProinstance[$i]['attr']['tpl'];
        //     $iTmp[$i]['app']=($aWidgetsProinstance[$i]['attr']['app']) ? $aWidgetsProinstance[$i]['attr']['app'] : ((empty($aWidgetsProinstance[$i]['attr']['theme'])) ? 'b2c' : '');
        //     $iTmp[$i]['theme']=(empty($aWidgetsProinstance[$i]['attr']['theme']))?'':$theme;
        //     $iTmp[$i]['level']='theme';
        //     $iTmp[$i]['flag']=$theme;
        //     $params = unserialize($aWidgetsProinstance[$i]['value']);
        //     $iTmp[$i]['params']= $params;
        // }
        // $aWidgetsProinstance=$iTmp;

        //确定修改theme的同时，不修改theme表中的is_used字段的值。
        $theme_objs = app::get('site')->model('themes')->dump(array('theme'=>$aTheme['theme']));
        if($theme_objs){
            $aTheme['is_used'] = $theme_objs['is_used'];
        }

        $this->flush_theme($theme); //flush数据

        $aNumber= kernel::single('site_theme_widget')->count_widgets_by_theme($theme);
        // $iNumber= kernel::single('site_widget_proinstance')->count_instances_by_theme($theme);
        $nNumber=intval($aNumber);
        $iNumber=intval($iNumber);
        $insertWidgets=false;
        // $insertInstances=false;

        if($replaceWg){
            if($nNumber){
                kernel::single('site_theme_widget')->delete_widgets_by_theme($theme);
            }
            // if($iNumber){
            //     kernel::single('site_widget_proinstance')->delete_instances_by_theme($theme);
            // }
            $insertWidgets=true;
            // $insertInstances=true;
        }else{
            if($nNumber==0){
                $insertWidgets=true;
            }
            if($iNumber==0){
                // $insertInstances=true;
            }
        }
        if($insertWidgets && count($aWidgets)>0){
            foreach($aWidgets as $k=>$wg){
                kernel::single('site_theme_widget')->insert_widgets($wg);
            }
        }

        // if($insertInstances && count($aWidgetsProinstance)>0){
        //     foreach($aWidgetsProinstance as $k=>$instance){
        //         kernel::single('site_widget_proinstance')->insert_instances($instance);
        //     }
        // }

        kernel::single('site_theme_tmpl')->install($theme);

        if(!kernel::single('site_theme_base')->update_theme($aTheme)){
            return false;
        }else{
            kernel::single('site_theme_base')->update_theme_widgets($theme);    //todo:升级模板挂件
            kernel::single('site_theme_base')->set_theme_views($theme, $views);
            kernel::single('site_theme_base')->set_last_modify();
            return $aTheme;
        }
    }

    private function __remove_db_theme($theme) {
        $filter = array('theme'=>$theme);
        return app::get('site')->model('themes_file')->delete($filter);
    }

    private function __remove_dir($sDir) {
        if($rHandle=opendir($sDir)){
            while(false!==($sItem=readdir($rHandle))){
                if ($sItem!='.' && $sItem!='..'){
                    if(is_dir($sDir.'/'.$sItem)){
                        $this->__remove_dir($sDir.'/'.$sItem);
                    }else{
                        if(!unlink($sDir.'/'.$sItem)){
                            trigger_error(app::get('site')->_('因权限原因，模板文件').$sDir.'/'.$sItem.app::get('site')->_('无法删除'),E_USER_NOTICE);
                        }
                    }
                }
            }
            closedir($rHandle);
            rmdir($sDir);
            return true;
        }else{
            return false;
        }
    }

    private function __build_dir($sDir){
        if(file_exists($sDir)){
            $aTmp=explode('/',$sDir);
            $sTmp=end($aTmp);
            if(strpos($sTmp,'(')){
                $i=substr($sTmp,strpos($sTmp,'(')+1,-1);
                $i++;
                $sDir=str_replace('('.($i-1).')','('.$i.')',$sDir);
            }else{
                $sDir.='(1)';
            }
            return $this->__build_dir($sDir);
        }else{
            if(!is_dir($sDir)){
                mkdir($sDir,0755,true);
            }
            return $sDir.'/';
        }
    }

    private function __change_xml_array($aArray){
        $aData = array();
        if(isset($aArray['attr'])){
            $aArray = array('0'=>$aArray);
        }
        if(is_array($aArray)){
            foreach($aArray as $i=>$v){
                unset($v['attr']);
                $aData[$i]=array_merge($v,$aArray[$i]['attr']);
            }
        }
        return $aData;
    }

    /*private function separatXml($theme){
        $workdir = getcwd();
        chdir(THEME_DIR.'/'.$theme);
        if(!is_file('info.xml')){
            $content=file_get_contents('theme.xml');
            $rContent=substr($content,0,strpos($content,'<widgets>'));
            file_put_contents('info.xml',$rContent.'</theme>');
        }
        chdir($workdir);
    }*/

    private function file_rename($source,$dest){
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

    public function ini_get_size($sName){
        $sSize = ini_get($sName);
        $sUnit = substr($sSize, -1);
        $iSize = (int) substr($sSize, 0, -1);
        switch (strtoupper($sUnit)){
            case 'Y' : $iSize *= 1024; // Yotta
            case 'Z' : $iSize *= 1024; // Zetta
            case 'E' : $iSize *= 1024; // Exa
            case 'P' : $iSize *= 1024; // Peta
            case 'T' : $iSize *= 1024; // Tera
            case 'G' : $iSize *= 1024; // Giga
            case 'M' : $iSize *= 1024; // Mega
            case 'K' : $iSize *= 1024; // kilo
                       break;
            default: $iSize = 5 * 1024 * 1024; //todo Default 2M
        };
        return $iSize;
    }

    public function get_file_ext($file_name){
        $ftype = array(
            'html' => app::get('site')->_('模板文件'),
            'gif'  => app::get('site')->_('图片文件'),
            'jpg'  => app::get('site')->_('图片文件'),
            'jpeg' => app::get('site')->_('图片文件'),
            'png'  => app::get('site')->_('图片文件'),
            'bmp'  => app::get('site')->_('图片文件'),
            'css'  => app::get('site')->_('样式表文件'),
            'js'   => app::get('site')->_('脚本文件'),
            'xml'  => app::get('site')->_('theme.xml'),
            'php'  => app::get('site')->_('模板挂件'),
        );
        if(strrpos($file_name,'.')===false) return false;
        $fext = strtolower(substr($file_name,strrpos($file_name,'.')+1));
        if(!$ftype[$fext])  return false;
        return array('ext'=>$fext,'memo'=>$ftype[$fext]);
    }

    public function initthemes(){
        if ($dh = opendir(THEME_DIR)){
            while (($file = readdir($dh)) !== false){
                if(substr($file,-4,4)=='.tgz'){
                    //$filename_arr[] = $file;
                    $theme_file['tmp_name'] = THEME_DIR.'/'.$file;
                    $theme_file['name'] = $file;
                    $theme_file['type'] = 'application/octet-stream';
                    $theme_file['error'] = '0';
                    $theme_file['size'] = filesize(THEME_DIR.'/'.$file);
                    $res = $this->install($theme_file,$msg);
                }
            }
            closedir($dh);
        }
    }
}//End Class
