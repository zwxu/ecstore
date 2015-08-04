<?php

class business_theme_widget extends site_theme_widget
{
    public function theme_modified($store_id, $current_file) {
        $data = app::get('business')->model('widgets_instance')->select()->where('store_id = ?', $store_id)->where('core_file = ?', $current_file)->instance()->fetch_all();
        if (count($data)) {
            return true;
        }
        return false;
    }
    
    public function admin_load($file, $slot, $id=null, $edit_mode=false){
        if(!$this->fastmode && $edit_mode){
            $this->fastmode=true;
        }
        $selectObj = app::get('site')->model('widgets_instance')->select()->where('core_file = ?', $file)->order('widgets_order ASC');
        if(!$id){
            $rows = $selectObj->where('core_slot = ?', $slot)->instance()->fetch_all();
        }else{
            $rows = $selectObj->where('core_id = ?', $id)->instance()->fetch_all();
        }
        $smarty = kernel::single('site_admin_render');
        $files = $smarty->_files;
        $_wgbar = $smarty->_wgbar;

        if(!strpos($file, ':')){
            $theme= substr($file,0,strpos($file,'/'));
        }else{
            $theme = kernel::single('site_theme_base')->get_default();
        }
        $obj_session = kernel::single('base_session');
        $obj_session->start();
        $wights_border= kernel::single('site_theme_base')->get_border_from_themes($theme);

        foreach($rows as $widgets){
            //$_SESSION['WIDGET_TMP_DATA'][$widgets['core_file']][$widgets['widgets_id']] = $widgets;
            $_SESSION['_tmp_wg_update'][$widgets['widgets_id']] = null;
            if($widgets['widgets_type']=='html')$widgets['widgets_type']='usercustom';
            $widgets['html'] = $this->fetch($widgets);

            $title=$widgets['title']?$widgets['title']:$widgets['widgets_type'];
            $wReplace=Array('<{$body}>','<{$title}>','<{$widgets_classname}>','"<{$widgets_id}>"');
            $wArt=Array($this->admin_wg_border($widgets,$theme),$widgets['title'],
                $widgets['classname']
                ,($widgets['domid']?$widgets['domid']:'widgets_'.$widgets['widgets_id']).' widgets_id="'.$widgets['widgets_id'].'"  title="'.$title.'"'.' widgets_theme="' . $theme . '"');

            if($widgets['border']!='__none__' && $wights_border[$widgets['border']]){
                $content=preg_replace("/(class\s*=\s*\")|(class\s*=\s*\')/","$0shopWidgets_box ",$wights_border[$widgets['border']],1);
                $widgets_box=str_replace($wReplace,$wArt, $content);
            }else{
                $widgets_box= '<div widgets_id="'.$widgets['widgets_id'].'" title="'.$title.'" widgets_theme="'.$theme.'">'.$this->admin_wg_border($widgets,$theme).'</div>';
            }
            $widgets_box=preg_replace("/<object[^>]*>([\s\S]*?)<\/object>/i","<div class='sWidgets_flash' title='Flash'>&nbsp;</div>",$widgets_box);
            $replacement=array("'onmouse'i","'onkey'i","'onmousemove'i","'onload'i","'onclick'i","'onselect'i","'unload'i");
            $widgets_box=preg_replace($replacement,array_fill(0,count($replacement),'xshopex'),$widgets_box);
            $widgets_box = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $widgets_box);
            echo preg_replace("/<script[^>]*>([\s\S]*?)<\/script>/i","",$widgets_box);

        }
        echo '<script>new DataLazyLoad({lazyDataType:\'img\',img:\'lazyload\'});</script>';//by huoxh
        $smarty->_files = $files;
        $smarty->_wgbar = $_wgbar;

        $obj_session->close();
    }


    public function store_load($file, $slot, $id=null, $edit_mode=false,$store_id){
        if(!$this->fastmode && $edit_mode){
            $this->fastmode=true;
        }
        $selectObj = app::get('business')->model('widgets_instance')->select()->where('core_file = ?', $file)->where('store_id = ?', $store_id)->order('widgets_order ASC');
        if(!$id){
            $rows = $selectObj->where('core_slot = ?', $slot)->instance()->fetch_all();
        }else{
            $rows = $selectObj->where('core_id = ?', $id)->instance()->fetch_all();
        }

        $smarty = kernel::single('site_admin_render');
        $files = $smarty->_files;
        $_wgbar = $smarty->_wgbar;

        if(!strpos($file, ':')){
            $theme= substr($file,0,strpos($file,'/'));
        }else{
            $theme = kernel::single('site_theme_base')->get_default();
        }
        $obj_session = kernel::single('base_session');
        $obj_session->start();
        $wights_border= kernel::single('site_theme_base')->get_border_from_themes($theme);

        foreach($rows as $widgets){
            //$_SESSION['WIDGET_TMP_DATA'][$widgets['core_file']][$widgets['widgets_id']] = $widgets;
            $_SESSION['_tmp_wg_update'][$widgets['widgets_id']] = null;
            if($widgets['widgets_type']=='html')$widgets['widgets_type']='usercustom';
            $widgets['html'] = $this->fetch($widgets);
            $title=$widgets['title']?$widgets['title']:$widgets['widgets_type'];
            $wReplace=Array('<{$body}>','<{$title}>','<{$widgets_classname}>','"<{$widgets_id}>"');
            $wArt=Array($this->admin_wg_border($widgets,$theme),$widgets['title'],
                $widgets['classname']
                ,($widgets['domid']?$widgets['domid']:'widgets_'.$widgets['widgets_id']).' widgets_id="'.$widgets['widgets_id'].'"  title="'.$title.'"'.' widgets_theme="' . $theme . '"');

            if($widgets['border']!='__none__' && $wights_border[$widgets['border']]){
                $content=preg_replace("/(class\s*=\s*\")|(class\s*=\s*\')/","$0shopWidgets_box ",$wights_border[$widgets['border']],1);
                $widgets_box=str_replace($wReplace,$wArt, $content);
            }else{
                $widgets_box= '<div class="shopWidgets_box" widgets_id="'.$widgets['widgets_id'].'" title="'.$title.'" widgets_theme="'.$theme.'">'.$this->admin_wg_border($widgets,$theme).'</div>';
            }
            $widgets_box=preg_replace("/<object[^>]*>([\s\S]*?)<\/object>/i","<div class='sWidgets_flash' title='Flash'>&nbsp;</div>",$widgets_box);
            $replacement=array("'onmouse'i","'onkey'i","'onmousemove'i","'onload'i","'onclick'i","'onselect'i","'unload'i");
            $widgets_box=preg_replace($replacement,array_fill(0,count($replacement),'xshopex'),$widgets_box);
            $widgets_box = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $widgets_box);
            echo preg_replace("/<script[^>]*>([\s\S]*?)<\/script>/i","",$widgets_box);

        }
        $smarty->_files = $files;
        $smarty->_wgbar = $_wgbar;

        $obj_session->close();
    }

    public function get_libs($theme)
    {
        $data = app::get('site')->model('widgets')->select()->where('app = ?', 'business')->where('theme = ?', $theme)->instance()->fetch_all();
        
        $widgetsLib3= array();
       
        foreach($data AS $val){
            $info = $this->widgets_info($val['name'], $val['app'], $val['theme']);
            ecos_site_lib_theme_widget_widgets_get_libs_notype($info, $val, $widgetsLib3);
        }
        $widgetsLib['storelist'] = $widgetsLib3['list'];
        $widgetsLib['usual'] = $widgetsLib3['usual'];
        return $widgetsLib;
    }

    public function get_libs_extend($theme, $type='')
    {
        if($theme){
            $data = app::get('site')->model('widgets')->select()->where('app = ?', 'business')->where('theme = ?', $theme)->instance()->fetch_all();
        }else{
            $data = app::get('site')->model('widgets')->select()->where('app = ?', 'business')->where('theme = ?', '')->instance()->fetch_all();
        }
        $widgetsLib = array();
        $order=array();
        if($type==null){
            foreach($data AS $val){
                $info = $this->widgets_info($val['name'], $val['app'], $val['theme']);
                ecos_site_lib_theme_widget_widgets_get_libs_notype($info, $val, $widgetsLib);
            }
        }else{
            foreach($data AS $val){
                $info = $this->widgets_info($val['name'], $val['app'], $val['theme']);
                ecos_site_lib_theme_widget_widgets_get_libs_type($info, $type, $val, $widgetsLib);
            }
            array_multisort($order, SORT_DESC, $widgetsLib['list']);
        }
        return $widgetsLib;

    }

    
    public function save_widgets($widgets_id, $aData)
    {
        if(!is_numeric($widgets_id))    return false;
        $aData['widgets_id'] = $widgets_id;
        $aData['modified'] = time();
        return app::get('business')->model('widgets_instance')->save($aData);
    }

    public function save_all($widgetsSet, $files,$store_id)
    {
        $i=0;
        $slots = array();
        $return = array();
        $model = app::get('business')->model('widgets_instance');
        foreach((array)$widgetsSet as $widgets_id=>$widgets){
            $widgets['modified'] = time();
            $widgets['store_id'] = $store_id;
            $widgets['widgets_order'] = $i++;
            $sql = '';
            if(is_numeric($widgets_id)){
                $slots[$widgets['core_file']][]=$widgets_id;
                $sData = $_SESSION['_tmp_wg_update'][$widgets_id];
                $sData['widgets_id'] = $widgets_id;
                $sData['widgets_order'] = $widgets['widgets_order'];
                if(!$model->save($sData)){
                    return false;
                }
            }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){

                $wg = $_SESSION['_tmp_wg_insert'][$match[1]];
                $setting = $this->widgets_info($wg['widgets_type'], $wg['app'], $wg['theme']);

                $widgets = array_merge(
                    $widgets,
                    $wg,
                    array(  'vary'=>$setting['vary'],
                            'scope'=> is_array($setting['scope'])?(','.implode($setting['scope'],',').','):$setting['scope'])
                );

                $widgets_id = $model->insert($widgets);

                // if(!ecos_site_lib_theme_widget_save_all($widgets_id, $widgets, $match, $return, $slots)){
                //     return false;
                // }
                if(!$widgets_id){
                    return false;
                }else{
                    $return[$_SESSION['_tmp_wg_insert'][$match[1]]['_domid']] = $widgets_id;
                    unset($_SESSION['_tmp_wg_insert'][$match[1]]);
                    $slots[$widgets['core_file']][]=$widgets_id;
                }
            }
            if(!strpos($widgets['core_file'],':')){
                kernel::single('site_theme_tmpl')->touch_tmpl_file($widgets['core_file']);
            }
        }
        if(is_array($files)){
            foreach($files as $file){
                if(is_array($slots[$file])&&count($slots[$file])>0){
                    $model->db->exec('delete from sdb_business_widgets_instance where store_id='.intval($store_id).' AND widgets_id not in('.implode(',',$slots[$file]).') and core_file="'.$file.'"');
                }else{
                    $model->db->exec('delete from sdb_business_widgets_instance where store_id='.intval($store_id).' AND core_file="'.$file.'"');
                }
                if(!strpos($file, ':')){
                    kernel::single('site_theme_tmpl')->touch_tmpl_file($file);
                }
            }
        }
        return $return;
    }
    /**
     * [store_editor description]
     * @param  [type]  $widgets       [description]
     * @param  [type]  $widgets_app   [description]
     * @param  [type]  $widgets_theme [description]
     * @param  [type]  $theme         [description]
     * @param  boolean $values        [description]
     * @param  [type]  $store         [description]
     * @return [type]                 [description]
     */
    public function store_editor($widgets, $widgets_app, $widgets_theme, $theme, $values=false ,$store){
        $return = array();
        $widgets_config = $this->widgets_config($widgets, $widgets_app, $widgets_theme);
        $widgets_dir = $widgets_config['dir'];

        $setting = $this->widgets_info($widgets, $widgets_app, $widgets_theme);

        if(ECAE_MODE){
            if(!empty($setting['template'])){
                if(!is_array($setting['template'])){
                    $setting['template'] = array($setting['template']=>'DEFAULT');
                }
                $return['tpls'][$file]=$setting['template'];
            }else{
                if($widgets=='html'){
                    $widgets='usercustom';
                    if(!$values['usercustom']) $values['usercustom']= $values['html'];
                }
                if($widgets_app){
                    // $objfile = app::get('site')->model('widgets_file');
                    // $files = $objfile->getList('filename,filetype',array('app'=>$widgets_app),0,-1);
                    if ($handle = opendir($widgets_dir)) {
                        while (false !== ($file = readdir($handle))) {
                            if(substr($file,0,1)!='_' && strtolower(substr($file,-5))=='.html' && file_exists($widgets_dir.'/'.$file)){
                                $return['tpls'][$file]=$file;
                            }
                        }
                        closedir($handle);
                    }else{
                        return false;
                    }
                }elseif($theme){
                    $objfile = app::get('site')->model('themes_file');
                    $files = $objfile->getList('filename,filetype',array('theme'=>$theme),0,-1);
                    foreach($files as $file){
                        if(substr($file,0,1)!='_' && strtolower(substr($file,-5))=='.html'){
                            $return['tpls'][$file]=$file;
                        }
                    }
                }
            }
            is_array($values) or $values=array();
            $values = array_merge($setting, $values);
        }else{
            ecos_site_lib_theme_widget_editor($widgets, $values, $setting, $widgets_dir, $return);
        }
        $return['borders'] = kernel::single('site_theme_base')->get_theme_borders($theme);
        $return['borders']['__none__']=app::get('site')->_('无边框');

        $cur_theme = $theme;
        if(file_exists($widgets_dir.'/_config.html')||ECAE_MODE){
            $smarty = kernel::single('site_admin_render');
            $smarty->tmpl_cachekey('widget_modifty' , true);

            $sFunc=$widgets_config['crun'];
            $sFuncFile = $widgets_config['cfg'];
            if(file_exists($sFuncFile)||ECAE_MODE){
                if(ECAE_MODE){
                    if($widgets_app){
                        include_once($sFuncFile);
                    }elseif($cur_theme){
                        $tmpl = substr($sFuncFile,strpos($sFuncFile,'/widgets/')+1);
                        $theme_file = app::get('site')->model('themes_file');
                        $file_row = $theme_file->getList('content',array('fileuri'=>$cur_theme.':'.$tmpl,'theme'=>$cur_theme),0,1);
                        eval('?>'.$file_row[0]['content']);
                    }
                }else{
                    include_once($sFuncFile);
                }
                if(function_exists($sFunc)){
                    $smarty->pagedata['data'] = $sFunc($widgets_config['app'],$store);
                }
            }

            $smarty->pagedata['setting'] = &$values;

            $compile_code = $smarty->fetch_admin_widget($widgets_dir.'/_config.html',$widgets_app);
            if($compile_code){
                ecos_site_lib_theme_widget_prefix_content($compile_code, $widgets_config['url']);
            }
            $return['html'] = $compile_code;
        }
        return $return;
    }

    public function store_fetch($widgets, $widgets_id=null,$store){
        $widgets_config = $this->widgets_config($widgets['widgets_type'], $widgets['app'], $widgets['theme']);
        $widgets_dir = $widgets_config['dir'];

        if(!is_dir($widgets_dir)&&!ECAE_MODE){
            return app::get('site')->_('版块'). $widgets_config['app']->app_id . '|' . $widgets['widgets_type'].app::get('site')->_('不存在.');
        }

        $func_file = $widgets_config['func'];
        $cur_theme = kernel::single('site_theme_base')->get_default();

        if(file_exists($func_file)||ECAE_MODE){
            $this->_errMsg = null;
            $this->_run_failed = false;
            if(ECAE_MODE){
                // $tmpl = substr($func_file,strpos($func_file,'/widgets/')+1);
                if($widgets['app']){
                    // $theme_file = app::get('site')->model('widgets_file');
                    // $file_row = $theme_file->getList('content',array('fileuri'=>$widgets['app'].':'.$tmpl,'app'=>$widgets['app']),0,1);
                    include_once($func_file);
                }else{
                    $tmpl = substr($func_file,strpos($func_file,'/widgets/')+1);
                    $theme_file = app::get('site')->model('themes_file');
                    $file_row = $theme_file->getList('content',array('fileuri'=>$cur_theme.':'.$tmpl,'theme'=>$cur_theme),0,1);
                    if(!$this->widgets_exists[$tmpl])
                        eval('?>'.$file_row[0]['content']);
                    $this->widgets_exists[$tmpl] = true;
                }
            }else{
                include_once($func_file);
            }
            if(function_exists($widgets_config['run'])){
                $menus = array();
                $func = $widgets_config['run'];
                kernel::single('site_admin_render')->pagedata['store_id'] = $store['store_id'];
                kernel::single('site_admin_render')->pagedata['data'] = $func($widgets['params'], kernel::single('site_admin_render'));
                kernel::single('site_admin_render')->pagedata['menus'] = &$menus;
            }
            if($this->_run_failed)
                return $this->_errMsg;
        }

        kernel::single('site_admin_render')->pagedata['setting'] = $widgets['params'];
        kernel::single('site_admin_render')->pagedata['widgets_id'] = $widgets_id;

        if(file_exists($widgets_dir . '/_preview.html')){
            $return = kernel::single('site_admin_render')->fetch_admin_widget($widgets_dir . '/_preview.html',$widgets['app']);
            if($return!==false){
                ecos_site_lib_theme_widget_prefix_content($return, $widgets_config['url']);
            }
            return $return;
        }else{
            if($this->fastmode){
                return '<div class="widgets-preview">'.$widgets['widgets_type'].'</div>';
            }
            $return = kernel::single('site_admin_render')->fetch_admin_widget($widgets_dir.'/'.$widgets['tpl'],$widgets['app']);
            if($return!==false){
                ecos_site_lib_theme_widget_prefix_content($return, $widgets_config['url']);
            }
            return $return;
        }
    }//End Function
    
    /**
     * 为新入驻店铺添加默认模板
     */
    public function copy_site_theme_to_store($store_id, $theme_id){
    	
    	$theme = app::get('business')->model('theme')->getRow('shop_tmpl_id,gallery_tmpl_id',array('theme_id'=>$theme_id));
    	//无系统默认模板
    	if($theme){
        	$tmppath = app::get('site')->model('themes_tmpl')->getList('tmpl_path,theme',array('id|in'=>array($theme['shop_tmpl_id'], $theme['gallery_tmpl_id'])));
            $files = array();
            foreach ($tmppath as $row) {
                $files []= $row['theme'].'/'.$row['tmpl_path'];
            }
        	$allwidfiles = app::get('site')->model('widgets_instance')->getList('*',array('core_file|in'=>$files));
        	$buswidgetsinstance = app::get('business')->model('widgets_instance');
            foreach ($allwidfiles as $row) {
                unset($row['widgets_id']);
        		$row['theme_id'] = $theme_id;
        		$row['store_id'] = $store_id;
                $buswidgetsinstance->save($row);
            }
            return true;
    	}
    	
    	return false;
    }

}//End Class
