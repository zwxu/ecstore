<?php

class site_widget_proinstance 
{

    public function insert_instances($instance) 
    {
        return app::get('site')->model('widgets_proinstance')->insert($instance);
    }//End Function

    public function count_instances_by_theme($theme) 
    {
        return app::get('site')->model('widgets_proinstance')->count(array('level'=>'theme', 'flag'=>$theme));
    }//End Function

    public function delete_instances_by_theme($theme) 
    {
        return app::get('site')->model('widgets_proinstance')->delete(array('level'=>'theme', 'flag'=>$theme));
    }//End Function

    public function admin_load($widgets_id=null, $edit_mode=false){
        if(empty($widgets_id))  return null;
        //print_r(func_get_args());
        if(!$this->fastmode && $edit_mode){
            $this->fastmode=true;
        }
        $widgets = app::get('site')->model('widgets_proinstance')->select()->where('widgets_id = ?', $widgets_id)->instance()->fetch_row();
        $smarty = kernel::single('site_admin_render');
        $files = $smarty->_files;
        $_wgbar = $smarty->_wgbar;
        
        if($widgets){
            $theme = ($widgets['flag']) ? $widgets['flag'] : '';

            if($theme){
                $wights_border= kernel::single('site_theme_base')->get_border_from_themes($theme);
            }

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
    }//End Function

    public function fetch($widgets, $widgets_id=null){
        
        $widgets_config = kernel::single('site_theme_widget')->widgets_config($widgets['widgets_type'], $widgets['app'], $widgets['theme']);
        $widgets_dir = $widgets_config['dir'];
                
        if(!is_dir($widgets_dir)){
            return app::get('site')->_('版块'). $widgets_config['app'] . '|' . $widgets['widgets_type'].app::get('site')->_('不存在.');
        }
        
        $func_file = $widgets_config['func'];
        
        if(file_exists($func_file)){
            $this->_errMsg = null;
            $this->_run_failed = false;
            include_once($func_file);
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
            $return = kernel::single('site_admin_render')->fetch_admin_widget($widgets_dir . '/_preview.html');
            if($return!==false){
                $this->prefix_content($return, $widgets_dir);
            }
            return $return;
        }else{
            if($this->fastmode){
                return '<div class="widgets-preview">'.$widgets['widgets_type'].'</div>';
            }
            $return = kernel::single('site_admin_render')->fetch_admin_widget($widgets_dir.'/'.$widgets['tpl']);
            if($return!==false){
                $this->prefix_content($return, $widgets_dir);
            }
            return $return;
            //return '<div class="widgets-preview">ddfdfdfddfdf</div>';
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

    public function prefix_content(&$content, $widgets_dir){
        $pattern = "/(\'|\")images/i";
        $replacement = "\$1".$widgets_dir.'/images/';
        $content = preg_replace($pattern, $replacement, $content);
    }

    public function editor($widgets, $widgets_app, $widgets_theme, $theme, $values=false){

        $return = array();
        $widgets_config = kernel::single('site_theme_widget')->widgets_config($widgets, $widgets_app, $widgets_theme);
        $widgets_dir = $widgets_config['dir'];

        $setting = kernel::single('site_theme_widget')->widgets_info($widgets, $widgets_app, $widgets_theme);

        //kxgsy163  默认配置未放入$values变量
        is_array($values) or $values=array();
        $values = array_merge($setting, $values);
        
        if(!empty($setting['template'])){
            $return['tpls'][$file]=$setting['template'];////////
        }else{
            if($widgets=='html'){
                $widgets='usercustom';
                if(!$values['usercustom']) $values['usercustom']= $values['html'];
            }
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
        }
        
        $return['borders'] = kernel::single('site_theme_base')->get_theme_borders($theme);
        $return['borders']['__none__']=app::get('site')->_('无边框');

        if(file_exists($widgets_dir.'/_config.html')){

            $smarty = kernel::single('site_admin_render');
            $smarty->tmpl_cachekey('widget_modifty' , true);

            $sFunc=$widgets_config['crun'];
            $sFuncFile = $widgets_config['cfg'];
            if(file_exists($sFuncFile)){
                include_once($sFuncFile);
                if(function_exists($sFunc)){
                    $smarty->pagedata['data'] = $sFunc($widgets_config['app']);
                }
            }

            $smarty->pagedata['setting'] = &$values;

            $compile_code = $smarty->fetch_admin_widget($widgets_dir.'/_config.html');
            if($compile_code){
                $this->prefix_content($compile_code, $widgets_dir);
            }
            $return['html'] = $compile_code;    
        }
        //echo '<PRE>';
        //print_r($return);exit;
        return $return;
    }

}//End Class