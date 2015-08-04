<?php



class site_theme_widget
{

    var $widgets_exists;

    public function count_widgets_by_theme($sTheme){
        $data = app::get('site')->model('widgets_instance')->db->selectlimit('select count("widgets_id") as num from sdb_site_widgets_instance where  core_file like "'.addslashes($sTheme).'%"');
        return $data[0]['num'];
    }

    public function delete_widgets_by_theme($sTheme){
        $flag = app::get('site')->model('widgets_instance')->db->exec('delete from sdb_site_widgets_instance where core_file like "'.addslashes($sTheme).'/%"');
        if($flag)
            return app::get('site')->model('themes_tmpl')->delete(array('theme'=>$sTheme));
        return $flag;
    }

    public function insert_widgets($aData)
    {
        //modfity by EDwin 2010/5/7
        if($aData['base_file']){
            $aData['core_file'] = substr($aData['base_file'], strpos($aData['base_file'], ':')+1);
            $aData['core_slot'] = $aData['base_slot'];
            $aData['core_id'] = $aData['base_id'];
            unset($aData['base_file']);
            unset($aData['base_slot']);
            unset($aData['base_id']);
        }//fix template install
        $aData['modified'] = time();
        return app::get('site')->model('widgets_instance')->insert($aData);
    }

    public function save_widgets($widgets_id, $aData)
    {
        if(!is_numeric($widgets_id))    return false;
        $aData['widgets_id'] = $widgets_id;
        $aData['modified'] = time();
        return app::get('site')->model('widgets_instance')->save($aData);
    }//End Function

    public function save_preview_all($widgetsSet, $files)
    {
        $i=0;
        $slots = array();
        $return = array();
        $_SESSION['WIDGET_TMP_DATA'] = array();

        $model = app::get('site')->model('widgets_instance');
        foreach((array)$widgetsSet as $widgets_id=>$widgets){
            $widgets['modified'] = time();
            $widgets['widgets_order'] = $i++;
            $sql = '';
            if(is_numeric($widgets_id)){
                $slots[$widgets['core_file']][]=$widgets_id;
        if(isset($_SESSION['_tmp_wg_update'][$widgets_id])){
            $sData = $_SESSION['_tmp_wg_update'][$widgets_id];
        }else{
            $sData = $model->getList('*',array('widgets_id'=>$widgets_id));
            $sData = $sData[0];
        }
                $sData = array_merge($sData,$widgets);
                $sData['widgets_id'] = $widgets_id;
                $_SESSION['WIDGET_TMP_DATA'][$widgets['core_file']][$sData['widgets_id']] = $sData;
            }elseif(preg_match('/^tmp_([0-9]+)$/i',$widgets_id,$match)){

                $wg = $_SESSION['_tmp_wg_insert'][$match[1]];

                $setting = $this->widgets_info($wg['widgets_type'], $wg['app'], $wg['theme']);

                $widgets = array_merge(
                    $widgets,
                    $wg,
                    array(  'vary'=>$setting['vary'],
                            'scope'=> is_array($setting['scope'])?(','.implode($setting['scope'],',').','):$setting['scope'])
                );

                if(!$widgets_id){
                    return false;
                }else{
                    $return[$_SESSION['_tmp_wg_insert'][$match[1]]['_domid']] = $widgets_id;
                    $slots[$widgets['core_file']][]=$widgets_id;

                    $_SESSION['WIDGET_TMP_DATA'][$widgets['core_file']][$widgets_id] = $widgets;
                }
            }
        }

        return $return;
    }//End Function

    public function save_all($widgetsSet, $files)
    {
        $i=0;
        $slots = array();
        $return = array();
        $model = app::get('site')->model('widgets_instance');
        foreach((array)$widgetsSet as $widgets_id=>$widgets){
            $widgets['modified'] = time();
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

                if(!ecos_site_lib_theme_widget_save_all($widgets_id, $widgets, $match, $return, $slots)){
                    return false;
                }
            }
            if(!strpos($widgets['core_file'],':')){
                kernel::single('site_theme_tmpl')->touch_tmpl_file($widgets['core_file']);
            }
        }
        if(is_array($files)){
            foreach($files as $file){
                if(is_array($slots[$file])&&count($slots[$file])>0){
                    $model->db->exec('delete from sdb_site_widgets_instance where widgets_id not in('.implode(',',$slots[$file]).') and core_file="'.$file.'"');
                }else{
                    $model->db->exec('delete from sdb_site_widgets_instance where core_file="'.$file.'"');
                }
                if(!strpos($file, ':')){
                    kernel::single('site_theme_tmpl')->touch_tmpl_file($file);
                }
            }
        }
        return $return;
    }//End Function

    public function widgets_exists($name, $app, $theme)
    {
        $data = $this->widgets_config($name, $app, $theme);
        if(is_dir($data['dir'])||ECAE_MODE){
            return $data['dir'];
        }else{
            return false;
        }
    }//End Function


    public function widgets_info($name, $app, $theme, $key=null)
    {

       if($name&&$widgets_dir = $this->widgets_exists($name, $app, $theme)){
            if(ECAE_MODE){
                //get widgets.php code
                $widgets_code = kernel::single('site_theme_tmpl_file')->get_widgets_code($theme, $app, $widgets_dir);
                eval('?>'.$widgets_code);
           }else{
                include($widgets_dir . '/widgets.php');
           }
            $setting['type'] = $name;
            return (is_null($key)) ? $setting : (isset($setting[$key]) ? $setting[$key] : '');
        }else{
            return false;
        }
    }//End Function

    public function get_widgets_info($name, $app, $key=null)
    {
        //todo:兼容老版本，无模板挂件
        return $this->widgets_info($name, $app, '', $key);
    }//End Function

    public function widgets_config($name, $app, $theme)
    {
        if(empty($theme)){
            $app = ($app) ? $app : 'b2c';
            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR . '/' . $app . '/widgets/' . $name)){
                $data['dir'] = CUSTOM_CORE_DIR . '/' . $app . '/widgets/' . $name;
            }else{
                $data['dir'] = APP_DIR . '/' . $app . '/widgets/' . $name;
            }
            $data['url'] = kernel::base_url(1).'/app/'.$app.'/widgets/' . $name;
            ecos_site_lib_theme_widget_widgets_config_empty($name, $data, $app);
        }else{
            $data['dir'] = THEME_DIR . '/' . $theme . '/widgets/' . $name;
            $data['app'] = null;
            $data['url'] = kernel::base_url(1) . '/themes/' . $theme . '/widgets/' . $name;
            ecos_site_lib_theme_widget_widgets_config_theme($name, $data, $theme);
        }
        return $data;
    }//End Function

    public function get_libs($theme)
    {
        $data = app::get('site')->model('widgets')->select()->where('app != ?', '')->or_where('theme = ?', $theme)->instance()->fetch_all();
        $widgetsLib1 = array();
        $widgetsLib2 = array();
        foreach($data AS $val){
            if($val['theme']==$theme){
                $info1 = $this->widgets_info($val['name'], $val['app'], $val['theme']);
                ecos_site_lib_theme_widget_widgets_get_libs_notype($info1, $val, $widgetsLib1);
            }else{
                $info2 = $this->widgets_info($val['name'], $val['app'], $val['theme']);
                ecos_site_lib_theme_widget_widgets_get_libs_notype($info2, $val, $widgetsLib2);
            }
        }

        foreach($widgetsLib2['usual'] as $val2){
            $widgetsLib1['usual'][]=$val2;
        }
        $widgetsLib['themelist'] = $widgetsLib1['list'];
        $widgetsLib['systemlist'] = $widgetsLib2['list'];
        $widgetsLib['usual'] = $widgetsLib1['usual'];
        return $widgetsLib;
    }//End Function

    public function get_libs_extend($theme, $type='')
    {
        if($theme){
            $data = app::get('site')->model('widgets')->select()->where('theme = ?', $theme)->instance()->fetch_all();
        }else{
            $data = app::get('site')->model('widgets')->select()->where('app != ?', '')->where('theme = ?', '')->instance()->fetch_all();
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

    }//End Function

    public function get_this_widgets_info($widgets, $app, $theme){
        $info = $this->widgets_info($widgets, $app, $theme);
        $widgetsLib = array('description'=>$info['description'],'catalog'=>$info['catalog'],'label'=>$info['name']);
        return $widgetsLib;
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
                $widgets_box= '<div class="shopWidgets_box" widgets_id="'.$widgets['widgets_id'].'" title="'.$title.'" widgets_theme="'.$theme.'">'.$this->admin_wg_border($widgets,$theme).'</div>';
            }
            $widgets_box=preg_replace("/<object[^>]*>([\s\S]*?)<\/object>/i","<div class='sWidgets_flash' title='Flash'>&nbsp;</div>",$widgets_box);
            $replacement=array("'onmouse'i","'onkey'i","'onmousemove'i","'onload'i","'onclick'i","'onselect'i","'unload'i");
            $widgets_box=preg_replace($replacement,array_fill(0,count($replacement),'xshopex'),$widgets_box);
            $widgets_box = str_replace('%THEME%', kernel::base_url(1).'/themes/'.$theme, $widgets_box);
            $tmlStr = preg_replace("/<script[^>]*>([\s\S]*?)<\/script>/i","",$widgets_box);
            echo $tmlStr;

        }
        //echo "<script>var href = location.href.split('&').getLast();if(href != 'file=index.html'){if(\$('mall_nav_cat')){\$('mall_nav_cat').setStyle('display','none');}}</script>";
			echo '<script>new DataLazyLoad({lazyDataType:\'img\',img:\'lazyload\'});</script>';//懒加载 
        $smarty->_files = $files;
        $smarty->_wgbar = $_wgbar;

        $obj_session->close();
    }//End Function

    public function fetch($widgets, $widgets_id=null){

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

    public function admin_wg_border($widgets,$theme,$type=false){

        if($type){
            $content="{$widgets['html']}";
            $wReplace=Array('<{$body}>','<{$title}>','<{$widgets_classname}>','"<{$widgets_id}>"');
            $title=$widgets['title']?$widgets['title']:$widgets['widgets_type'];
            $wArt=Array($content,$widgets['title'],
                $widgets['classname']
                ,($widgets['domid']?$widgets['domid']:'widgets_'.$widgets['widgets_id']).' widgets_id="'.$widgets['widgets_id'].'"  title="'.$title.'"'.' widgets_theme="' . $theme . '"');
            if(!empty($widgets['border']) && $widgets['border']!='__none__'){
                $wights_border = kernel::single('site_theme_base')->get_border_from_themes($theme);
                $content=preg_replace("/(class\s*=\s*\")|(class\s*=\s*\')/","$0shopWidgets_box ",$wights_border[$widgets['border']],1);
                $tpl=str_replace($wReplace,$wArt, $content);
            }else{
                $tpl='<div class="shopWidgets_box" widgets_id="'.$widgets['widgets_id'].'" title="'.$title.'" widgets_theme="'.$theme.'">'.$content.'</div>';
            }
        }else{
            $tpl="{$widgets['html']}";
        }

        return trim(preg_replace('!\s+!', ' ', $tpl));
    }

	public function get_tpl($url){
		$url = explode('&',$url);
		$file = $url[4];
		$file = explode('=',$file);
		//$files = app::get('site')->model('themes_file')->getList('*',array('filename'=>$file[1]));
		$tpl = app::get('site')->model('themes_tmpl')->getList('*',array('tmpl_path'=>$file[1]));

		return $tpl[0]['type'];
	}

    public function editor($widgets, $widgets_app, $widgets_theme, $theme, $values=false){
		$pre_url = $_SERVER['HTTP_REFERER'];
		$tpl_type = $this->get_tpl($_SERVER['HTTP_REFERER']);

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
                    // $tmpl = substr($sFuncFile,strpos($sFuncFile,'/widgets/')+1);
                    if($widgets_app){
                        // $widgets_file = app::get('site')->model('widgets_file');
                        // $file_row = $widgets_file->getList('content',array('fileuri'=>$widgets_app.':'.$tmpl,'app'=>$widgets_app),0,1);
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
                    $smarty->pagedata['data'] = $sFunc($widgets_config['app'],$tpl_type);
                }
            }
			
            $smarty->pagedata['setting'] = &$values;
			$smarty->pagedata['cat_type'] = $tpl_type;
            $compile_code = $smarty->fetch_admin_widget($widgets_dir.'/_config.html',$widgets_app);
            if($compile_code){
                ecos_site_lib_theme_widget_prefix_content($compile_code, $widgets_config['url']);
            }
            $return['html'] = $compile_code;
        }
        return $return;
    }

}//End Class
