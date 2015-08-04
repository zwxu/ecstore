<?php



class site_theme_tmpl_dbsave
{

    public function get_default($type, $theme)
    {
        return app::get('site')->getConf('custom_template_'.$theme.'_'.$type);
    }//End Function

    public function set_default($type, $theme, $value)
    {
        return app::get('site')->setConf('custom_template_'.$theme.'_'.$type, $value);
    }//End Function

    public function del_default($type, $theme)
    {
        return app::get('site')->setConf('custom_template_'.$theme.'_'.$type, '');
    }//End Function

    public function set_all_tmpl_file($theme)
    {
        $row = app::get('site')->model('themes_tmpl')->select()->columns('tmpl_path')->where('theme = ?', $theme)->instance()->fetch_col();
        return app::get('site')->setConf('custom_template_'.$theme.'_all_tmpl', $row['tmpl_path']);
    }//End Function

    public function get_all_tmpl_file($theme)
    {
        return app::get('site')->getConf('custom_template_'.$theme.'_all_tmpl');
    }//End Function

    public function tmpl_file_exists($tmpl_file, $theme)
    {
        $all = $this->get_all_tmpl_file($theme);
        $all[] = 'block/header.html';
        $all[] = 'block/nav.html';
        $all[] = 'block/footer.html';   //头尾文件
        if(is_array($all)){
            return in_array($tmpl_file, $all);
        }else{
            return false;
        }
    }//End Function

    public function get_edit_list($theme)
    {
        $data = app::get('site')->model('themes_tmpl')->getList('*', array("theme"=>$theme));
        if(is_array($data)){
            foreach($data AS $value){
                if($this->get_default($value['tmpl_type'], $theme) == $value['tmpl_path'])
                    $value['default'] = 1;

                $ret[$value['tmpl_type']][] = $value;
            }
        }
        return $ret;
    }//End Function

    public function install($theme)
    {
        $list = array();
        $this->__get_all_files(THEME_DIR . '/' . $theme, $list, false);
        if(file_exists(THEME_DIR.'/'.$theme.'/cart.html')){
            if(!file_exists(THEME_DIR.'/'.$theme.'/order_detail.html')){
                copy(THEME_DIR.'/'.$theme.'/cart.html',THEME_DIR.'/'.$theme.'/order_detail.html');
            }
            if(!file_exists(THEME_DIR.'/'.$theme.'/order_index.html')){
                copy(THEME_DIR.'/'.$theme.'/cart.html',THEME_DIR.'/'.$theme.'/order_index.html');
            }
        }
        $ctl = $this->get_name();
        foreach($list AS $key=>$value){
            $file_name = basename($value, '.html');
            if(!strpos($file_name,'.')){
                if(($pos=strpos($file_name,'-'))){
                    $type=substr($file_name,0,$pos);
                    $file[$type][$key]['name']=$ctl[substr($file_name,0,$pos)];
                    $file[$type][$key]['file']=$file_name.'.html';
                }else{
                    $type=$file_name;
                    $file[$file_name][$key]['name']=$ctl[$file_name];
                    $file[$file_name][$key]['file']=$file_name.'.html';
                    //$file[$key]['name']=$ctl[$file_name];
                }

                touch(THEME_DIR . '/' . $theme . '/' . $file_name . '.html');

                if($type && array_key_exists($type, $ctl)){
                    $array = array(
                        'theme'=>$theme,
                        'tmpl_type'=>$type,
                        'tmpl_name'=>$file_name.'.html',
                        'tmpl_path'=>$file_name.'.html',
                        'version'=>filemtime(THEME_DIR . '/' . $theme . '/' . $file_name . '.html'),
                        'content'=>file_get_contents(THEME_DIR . '/' . $theme . '/' . $file_name . '.html')
                    );
                    //先插入themes_file表，返回file_id
                    $filter = array('theme'=>$array['theme'],'fileuri'=>$array['theme'].':'.$array['tmpl_path']);
                    if($file_id = $this->get_themes_file_id($filter)){
                        $array['rel_file_id'] = $file_id;
                    }
                    $this->insert($array);
                    if(!$this->get_default($type, $theme)){
                        $this->set_default($type, $theme, $file_name.'.html');
                    }
                }
            }
        }
    }//End Function
    
    public function update($theme){
    }

    public function get_themes_file_id($filter){
        if($file_id = app::get('site')->model('themes_file')->getList('id',$filter)){
            return $file_id['0']['id'];
        }else{
            return false;
        }
    }

    public function insert($data)
    {
        if(app::get('site')->model('themes_tmpl')->insert($data)){
            $this->set_all_tmpl_file($data['theme']);
            return true;
        }else{
            return false;
        }
    }//End Function

    public function insert_tmpl($data,&$msg)
    {
        if(empty($data['tmpl_type']) || empty($data['content']))    return false;
        $data['tmpl_path'] = strtolower(preg_replace('/[^a-z0-9]/', '', $data['tmpl_path']));
        $data['tmpl_path'] =$data['tmpl_type'] . '-' . $data['tmpl_path'] . '.html';
        $data['version'] = time();
        $tmpl=app::get('site')->model('themes_tmpl')->dump(array(tmpl_path=>$data['tmpl_path']),'tmpl_path');
        if($tmpl){
            $msg=app::get('site')->_('文件名称重复,请检查!');
            return false;
        }

        $themes_file_data = array(
            'filename' => $data['tmpl_path'],
            'filetype' => $data['tmpl_type'],
            'fileuri'  => $data['theme'] . ':' . $data['tmpl_path'],
            'version'  => $data['version'],
            'theme'    => $data['theme'],
            'memo'     => '模板文件',
            'content'  => $data['content']
        );

        $obj_themes_file = app::get('site')->model('themes_file');
        if($obj_themes_file->save($themes_file_data)){
            $file_rows = $obj_themes_file->getList('id',array('theme'=>$data['theme'],'fileuri'=>$data['theme'] . ':' . $data['tmpl_path']));
            $data['rel_file_id'] = $file_rows['0']['id'];
        }

        if($this->insert($data)){
            // $data['filename']=$data['tmpl_path'];
            // $data['filetype']=$data['tmpl_type'];
            // $data['memo']='模板文件';
            // $data['fileuri']=$data['theme'].':'.$data['tmpl_path'];
            // app::get('site')->model('themes_file')->save($data,$filter);
            $msg=app::get('site')->_('添加成功');
            return true;
        }

        return false;
    }//End Function

    public function insert_themes_file($data){
        if($file_id = app::get('site')->model('themes_file')->save($data)){
            return $file_id;
        }else{
            return false;
        }
    }

    public function copy_tmpl($tmpl, $theme)
    {
        $fileInfo = pathinfo($tmpl);
        $fileObj = app::get('site')->model('themes_file');
        $rows = $fileObj->getList('content',array('theme'=>$theme,'filename'=>$tmpl));
        $content = $rows[0]['content'];
        if(!($content))   return false;
        $data = app::get('site')->model('themes_tmpl')->getList('*', array('theme'=>$theme, 'tmpl_path'=>$tmpl));
        $data = $data[0];
        if(empty($data))    return false;
        $flag = true;
        $obj = kernel::single('site_explorer_file');
        $obj->set_theme($theme);
        $instancelist = $obj->get_file_instancelist(array('dir'=>dirname($tmpl), 'show_bak'=>true, 'type'=>'all'), basename($tmpl));
        $loop = 1;
        if(is_array($instancelist)){
            foreach($instancelist AS $val){
                if($val['name'] !==  sprintf('%s-(%d).%s', $fileInfo['filename'], $loop, $fileInfo['extension'])){
                    break;
                }
                $loop++;
            }
        }

        $target = sprintf('%s%s-(%d).%s', dirname($tmpl)=='.'?'':dirname($tmpl).'/', $fileInfo['filename'], $loop, $fileInfo['extension']);

        $tmp_data = array(
            'filename'=>$target,
            'fileuri'=>$theme.':'.$target,
            'theme'=>$theme,
            'filetype'=>$fileInfo['extension'],
            'memo'=>$ftype[$fileInfo['extension']],
            'content'=>$content,
            );
        $fileObj->save($tmp_data);

        unset($data['id']);
        $data['tmpl_path'] = basename($target);
        $data['tmpl_name'] = basename($target);
        if($this->insert($data)){
            $widgets = app::get('site')->model('widgets_instance')->getList('*', array('core_file'=>$theme.'/'.$tmpl));
            foreach($widgets AS $widget){
                unset($widget['widgets_id']);
                $widget['core_file'] = $theme . '/' . basename($target);
                $widget['modified'] = time();
                app::get('site')->model('widgets_instance')->insert($widget);
            }
            return true;
        }else{
            return false;
        }
    }//End Function

    public function delete_tmpl_by_theme()
    {
        //不删除实体文件，只处理数据库和conf
        $datas = app::get('site')->model('themes_tmpl')->getList('tmpl_path', array('theme'=>$theme));
        foreach($datas AS $data){
            $this->delete_tmpl($data['tmpl_path'], $theme);
        }
    }//End Function

    public function delete_tmpl($tmpl, $theme)
    {
        $data = app::get('site')->model('themes_tmpl')->getList('*', array('theme'=>$theme, 'tmpl_path'=>$tmpl));
        if($data[0]['id'] > 0){
            if(app::get('site')->model('themes_tmpl')->delete(array('id'=>$data[0]['id']))){
                //删除模板文件的同时删除themes_file的对应文件
                app::get('site')->model('themes_file')->delete(array('theme'=>$theme,'filename'=>$tmpl));
                app::get('site')->model('widgets_instance')->delete(array('core_file'=>$theme.'/'.$tmpl));
                $this->set_all_tmpl_file($data[0]['theme']);
                if($this->get_default($data[0]['tmpl_type'], $theme) == $data[0]['tmpl_path']){
                    $this->del_default($data[0]['tmpl_type'], $theme);
                }
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }//End Function

    public function get_name(){
        $ctl = $this->__get_tmpl_list();
        return $ctl;
    }

    private function __get_all_files($sDir, &$aFile, $loop=true){
        if($rHandle=opendir($sDir)){
            while(false!==($sItem=readdir($rHandle))){
                if ($sItem!='.' && $sItem!='..' && $sItem!='' && $sItem!='.svn' && $sItem!='_svn'){
                    if(is_dir($sDir.'/'.$sItem)){
                        if($loop){
                            $this->__get_all_files($sDir.'/'.$sItem,$aFile);
                        }
                    }else{
                        $aFile[]=$sDir.'/'.$sItem;
                    }
                }
            }
            closedir($rHandle);
        }
    }

    public function get_list_name($name)
    {
        $name = rtrim(strtolower($name),'.html');
        $ctl = $this->__get_tmpl_list();
        return $ctl[$name];
    }//End Function

    public function __get_tmpl_list() {
        $ctl = array(
            'index'=>app::get('site')->_('首页'),
            'shop'=>app::get('site')->_('店铺首页'),
            'shopgallery'=>app::get('site')->_('店铺商品列表页'),
            'gallery'=>app::get('site')->_('商品列表页'),
            'product'=>app::get('site')->_('商品详细页'),
            'comment'=>app::get('site')->_('商品评论/咨询页'),
            'article'=>app::get('site')->_('文章页'),
            'articlelist'=>app::get('site')->_('文章列表页'),
            'gift'=>app::get('site')->_('赠品页'),
            'package'=>app::get('site')->_('捆绑商品页'),
            'brandlist'=>app::get('site')->_('品牌专区页'),
            'brand'=>app::get('site')->_('品牌商品展示页'),
            'cart'=>app::get('site')->_('购物车页'),
            'search'=>app::get('site')->_('高级搜索页'),
            'passport'=>app::get('site')->_('注册页'),
            'login'=>app::get('site')->_('登录页'),
            'member'=>app::get('site')->_('会员中心页'),
            'page'=>app::get('site')->_('站点栏目单独页'),
            'order_detail'=>app::get('site')->_('订单详细页'),
            'order_index'=>app::get('site')->_('订单确认页'),
            'splash'=>app::get('site')->_('信息提示页'),
            'mp_timedbuy'=>app::get('site')->_('名品特卖页'),
            'grouplist'=>app::get('site')->_('团购列表页'),
            'investment'=>app::get('site')->_('招商首页'),
            'default'=>app::get('site')->_('默认页'),
        );
        foreach( kernel::servicelist("site.site_theme_tmpl") as $object ) {
            if( method_exists($object,'__get_tmpl_list') ) {
                $arr = $object->__get_tmpl_list($ctl);
                if( $arr ) {
                    foreach( $arr as $key => $val ) {
                        if( $ctl[$key] ) continue;
                        $ctl[$key] = $val;
                    }
                }
            }
        }
        return $ctl;
    }

    public function touch_theme_tmpl($theme)
    {
        $rows = app::get('site')->model('themes_tmpl')->select()->columns('tmpl_path')->where('theme = ?', $theme)->instance()->fetch_all();
        if($rows){
            array_push($rows, array('tmpl_path'=>'block/header.html'), array('tmpl_path'=>'block/footer.html'));
            foreach($rows AS $row){
                $this->touch_tmpl_file($theme . '/' . $row['tmpl_path']);
            }
            kernel::single('site_theme_base')->set_theme_cache_version($theme);
        }

        $cache_keys = kernel::database()->select('SELECT `prefix`, `key` FROM sdb_base_kvstore WHERE `prefix` IN ("cache/template", "cache/theme")');
        foreach($cache_keys as $value)
        {
            base_kvstore::instance($value['prefix'])->get_controller()->delete($value['key']);
        }
        kernel::database()->exec('DELETE FROM sdb_base_kvstore WHERE `prefix` IN ("cache/template", "cache/theme")');

        cachemgr::init(true);
        cachemgr::clean($msg);
        cachemgr::init(false);

        return true;
    }//End Function


    public function touch_tmpl_file($tmpl, $time=null)
    {
        if(empty($time))    $time = time();
        $source = THEME_DIR . '/' . $tmpl;
        if(is_file($source)){
            return @touch($source, $time);
        }else{
            return false;
        }
    }//End Function

    function output_pkg($theme){
        $tar = kernel::single('base_tar');
        $fileObj = app::get('site')->model('themes_file');

        if($file_list = $fileObj->getList('*',array('theme'=>$theme))){
            $storager = kernel::single('base_storager');
            $http = kernel::single('base_httpclient');
            //get db theme files
            foreach($file_list as $key=>$value){
                if($value['filename']!='theme.xml'){
                    if($value['filetype']=='php'||$value['filetype']=='html'||$value['filetype']=='xml'){
                        $tar->addFile($value['filename'], $value['content'] );
                    }elseif($value['filetype']=='css'||$value['filetype']=='js'){
                        $ident = $storager->parse($value['content']);
                        $content = $http->action(__FUNCTION__,$ident['url'],null,null,array());
                        $tar->addFile($value['filename'], $content );
                    }else{
                        $ident = $storager->parse($value['content']);
                        $content = $http->action(__FUNCTION__,$ident['url'],null,null,array());
                        $tar->addFile($value['filename'], $content );
                    }
                }
            }

            $tar->addFile('theme.xml',$this->make_configfile($theme));

            $aTheme = kernel::single('site_theme_base')->get_theme_info($theme);

            kernel::single('base_session')->close();

            $name = kernel::single('base_charset')->utf2local(preg_replace('/\s/','-',$aTheme['name'].'-'.$aTheme['version']),'zh');
            @set_time_limit(0);

            header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header('Content-type: application/octet-stream');
            header('Content-type: application/force-download');
            header('Content-Disposition: attachment; filename="'.$name.'.tgz"');
            $tar->getTar('output');
        }else{
            return false;
        }
    }

    public function make_configfile($theme){
        $aTheme = kernel::single('site_theme_base')->get_theme_info($theme);

        //$aWidget['widgets'] = app::get('site')->model('widgets_instance')->getList('*', array('core_file|head'=>$theme.'/'));
        $aWidget['widgets'] = app::get('site')->model('widgets_instance')->select()->where("core_file LIKE '".$theme."/%'")->instance()->fetch_all();
        foreach($aWidget['widgets'] as $i => &$widget){
            $widget['core_file'] = str_replace($theme.'/', '', $widget['core_file']);
            $widget['params'] = serialize($widget['params']);
        }
        //$aWidget['widgets_proinstance'] = app::get('site')->model('widgets_proinstance')->select()->where('level = ?', 'theme')->where('flag = ?', $theme)->instance()->fetch_all();
        //foreach($aWidget['widgets_proinstance'] AS $k=>&$instance){
        //    $instance['params'] = serialize($instance['params']);
        //}
        //$aTheme['config']['config'] = $aTheme['config']['config'];
        //$aTheme['config']['views'] = $aTheme['views'];
        $aTheme['id'] = $aTheme['theme'];
        $aTheme=array_merge($aTheme, $aWidget);

        $render = kernel::single('base_render');
        $render->pagedata = $aTheme;
        $sXML = $render->fetch('admin/theme/theme.xml', 'site');

        return $sXML;
    }

}//End Class
