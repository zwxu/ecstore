<?php


class site_view_compiler
{
    var $widgets_libs;
    function compile_main($attrs, &$compiler)
    {
        if ($attrs&&$attrs['view']){
            $o_themes = app::get('site')->model('themes')->getList('*', array('is_used'=>'true'));
            $compiler->controller->_vars['_MAIN_'] = $o_themes[0]['theme'].'/'.str_replace(array('"', '"'),array("", ""),$attrs['view']);
            //$compiler->controller->_vars['_MAIN_'] = $o_themes[0]['theme'].'/'.str_replace("'","",$attrs['view']);
            // echo $compiler->controller->_vars['_MAIN_'];exit();
            return " echo  \$this->_fetch_compile_include(null, '" . $compiler->controller->_vars['_MAIN_'] . "', array(), true); ";
        }
        else{
            return " echo  \$this->_fetch_compile_include('".($compiler->controller->get_tmpl_main_app_id() ? $compiler->controller->get_tmpl_main_app_id() : $compiler->controller->app->app_id)."', '" . $compiler->controller->_vars['_MAIN_'] . "', array()); ";
        }
    }

    function compile_require($attrs, &$compiler)
    {
        $is_preview = (isset($_COOKIE['site']['preview'])&&$_COOKIE['site']['preview']=='true')?'true':'false';
        return " echo \$this->_fetch_tmpl_compile_require({$attrs['file']},{$is_preview});";
    }

    function compile_widget($attrs, &$compiler)
    {
        if(($attrs['name']{0}=='\'' || $attrs['name']{0}=='"') && $attrs['name']{0}==$attrs['name'][strlen($attrs['name'])-1]){
            $name = substr($attrs['name'], 1, strlen($attrs['name'])-2);
        }else{
            $name = $attrs['name'];
        }
        if(($attrs['id']{0}=='\'' || $attrs['id']{0}=='"') && $attrs['id']{0}==$attrs['id'][strlen($attrs['id'])-1]){
            $id = substr($attrs['id'], 1, strlen($attrs['id'])-2);
        }else{
            $id = $attrs['id'];
        }
        $theme = $compiler->controller->get_theme();
        if($theme && $name){
            $widget = app::get('site')->model('widgets_proinstance')->select()->where('level = ?', 'theme')->where('flag = ?', $theme)->where('name = ?', $name)->instance()->fetch_row();
        }elseif($id){
            $widget = app::get('site')->model('widgets_proinstance')->select()->where('level = ?', 'system')->where('widgets_id = ?', $id)->instance()->fetch_row();
        }else{
            return '';
        }
        if($widget){
            $wg_compiler = &$compiler;
            $return = '$__THEME_URL = $this->_vars[\'_THEME_\'];';
            $return .= 'unset($this->_vars);';
            $return .= $this->__siet_parse_widget_instance($widget, $wg_compiler, 'widget');
            return $return.'$setting=null;$widgets_vary=null;$key_prefix=null;$__THEME_URL=null;$this->_vars = &$this->pagedata;';
        }else{
            return '';
        }
    }//End Function

    function compile_widgets($attrs, &$compiler)
    {
        $current_file = $compiler->controller->_files[0];

        $slot = intval($compiler->_wgbar[$compiler->controller->_files[0]]++);

        if (!$compiler->is_preview){
            if(!isset($compiler->_cache[$current_file])){
                $all = app::get('site')->model('widgets_instance')->select()->where('core_file = ?', $current_file)->order('widgets_order ASC')->instance()->fetch_all();

                //echo '<PRE>';
                //print_r($all);

                foreach($all as $i=>$r){
                    if($r['core_id']){
                        $c['id'][$r['core_id']][] = &$all[$i];
                    }else{
                        $c['slot'][$r['core_slot']][] = &$all[$i];
                    }
                }
                $compiler->_cache[$current_file] = &$c;
            }

            if(isset($attrs['id'])){
                if($attrs['id']{0}=='"' || $attrs['id']{0}=='\''){
                    $attrs['id'] = substr($attrs['id'],1,-1);
                }
                $widgets_group = $compiler->_cache[$current_file]['id'][$attrs['id']];
            }else{
                $widgets_group = $compiler->_cache[$current_file]['slot'][$slot];
            }
        }else{
            $obj_session = kernel::single('base_session');
            $obj_session->start();

            if ($_SESSION['WIDGET_TMP_DATA'][$current_file]&&is_array($_SESSION['WIDGET_TMP_DATA'][$current_file])){
                $all = (array)$_SESSION['WIDGET_TMP_DATA'][$current_file];
            }else{
                $all = app::get('site')->model('widgets_instance')->select()->where('core_file = ?', $current_file)->order('widgets_order ASC')->instance()->fetch_all();
            }

            foreach($all as $i=>$r){
                if($r['core_id']){
                    $c['id'][$r['core_id']][] = &$all[$i];
                }else{
                    $c['slot'][$r['core_slot']][] = &$all[$i];
                }
            }

            if(isset($attrs['id'])){
                if($attrs['id']{0}=='"' || $attrs['id']{0}=='\''){
                    $attrs['id'] = substr($attrs['id'],1,-1);
                }
                $widgets_group = $c['id'][$attrs['id']];
            }else{
                $widgets_group = $c['slot'][$slot];
            }

            $obj_session->close();
        }

        /*--------------------- 获取全部widgets ------------------------------*/
        if(isset($widgets_group[0])){
            $wg_compiler = &$compiler;
            $return = '$__THEME_URL = $this->_vars[\'_THEME_\'];';
            $return .= 'unset($this->_vars);';
            foreach($widgets_group as $widget){
                $return .= $this->__siet_parse_widget_instance($widget, $wg_compiler, 'widgets');
            }

            return $return.'$setting=null;$widgets_vary=null;$key_prefix=null;$__THEME_URL=null;$this->_vars = &$this->pagedata;';
        }else{
            return '';
        }
    }

    public function __siet_parse_widget_instance($widget, &$wg_compiler, $type)
    {
        $return = '';
        $widgets_config = kernel::single('site_theme_widget')->widgets_config($widget['widgets_type'], $widget['app'], $widget['theme']);

        $widget_dir = $widgets_config['dir'];
        $widget_flag = $widgets_config['flag'];
        $widget_run = $widgets_config['run'];
        $widgets_url = $widgets_config['url'];

        /*--------------------获取内容-----------------------------*/
        $tpl =  $widget_dir . '/' .$widget['tpl'];

        $cur_theme = kernel::single('site_theme_base')->get_default();

        if(!file_exists($tpl)&&!ECAE_MODE){
            return '';
            //trigger_error("tpl is empty", E_USER_ERROR);
        }

        $params = (is_array($widget['params'])) ? $widget['params'] : array();

        $func_file = $widgets_config['func'];
        $return .= '$setting = '.var_export($params,1).';$this->bundle_vars[\'setting\'] = &$setting;';
        $return .= '$widgets_vary = kernel::single(\'site_theme_widget\')->widgets_info(\''.$widget['widgets_type'].'\', \''.$widget['app'].'\', \''.$widget['theme'].'\', \'vary\');';
        $return .= '$key_prefix = $this->create_widgets_key_prefix($GLOBALS[\'runtime\'], explode(\',\', $widgets_vary));';
        //todo:由全局变量影响widgets的缓存key

        if(file_exists($func_file)||ECAE_MODE){
            $get_php_code = kernel::single('site_theme_tmpl_file')->get_func_phpcode($cur_theme, $func_file, $widget['app']);
            $return .= 'if(!isset($this->__widgets_exists[\''.$widget_flag.'\'][\''.$widget['widgets_type'].'\'])){';
            $return .= $get_php_code;
            $return .= '$this->__widgets_exists[\''.$widget_flag.'\'][\''.$widget['widgets_type'].'\']=1;}';

            $return .= '$widgets_cache_key = md5($key_prefix.\'_app_'.$widget['app'].'_theme_'.$widget['theme'].'_type_'.$widget['widgets_type'].'_\'.md5(serialize($setting)));';
            //todo:缓存相同设置的widgets
            $return .= 'if(!cachemgr::get($widgets_cache_key, $widgets_data)){';
            $return .= 'kernel::log("widget cache miss => app:'.$widget['app'].',theme:'.$widget['theme'].',type:'.$widget['widgets_type'].'.");';
            $return .= 'cachemgr::co_start();';
            if($type == 'widgets'){
                $return .= 'if(cachemgr::enable()) cachemgr::set_modified(\'DB\', app::get(\'site\')->model(\'widgets_instance\')->table_name(1), $now);';
                //$return .= 'app::get(\'site\')->model(\'widgets_instance\')->select()->columns(\'1=1\')->limit(1,1)->instance()->fetch_one();';
            }elseif($type == 'widget'){
                $return .= 'if(cachemgr::enable()) cachemgr::set_modified(\'DB\', app::get(\'site\')->model(\'widgets_proinstance\')->table_name(1), $now);';
                //$return .= 'app::get(\'site\')->model(\'widgets_proinstance\')->select()->columns(\'1=1\')->limit(1,1)->instance()->fetch_one();';
            }
            //todo:最简单的方式取一下数据，否则缓存控制器无法得知widgets_instance会影响到缓存
            $return .= 'if(function_exists("'.$widget_run.'")) $widgets_data = '.$widget_run.'($setting,$this);';
            $return .= 'cachemgr::set($widgets_cache_key, $widgets_data, cachemgr::co_end());}';
            $return .= 'else {kernel::log("widget cache hit => app:'.$widget['app'].',theme:'.$widget['theme'].',type:'.$widget['widgets_type'].'.");}';
            $return .= '$this->_vars = array(\'data\'=>$widgets_data,\'widgets_id\'=>\''.$widget['widgets_id'].'\');';
        }else{
            $return .= '$this->_vars = array(\'widgets_id\'=>\''.$widget['widgets_id'].'\');';
        }

        $content = kernel::single('site_theme_tmpl_file')->get_widgets_content($cur_theme, $tpl, $widget['app']);

        $pattern_from = array(
            '/(\'|\")(images\/)/is',
            '/((?:background|src|href)\s*=\s*["|\'])(?:\.\/|\.\.\/)?(images\/.*?["|\'])/is',
            '/((?:background|background-image):\s*?url\()(?:\.\/|\.\.\/)?(images\/)/is',
        );
        $pattern_to = array(
            "\$1" . $widgets_url .'/' . "\$2",
            "\$1" . $widgets_url .'/' . "\$2",
            "\$1" . $widgets_url .'/' . "\$2",
        );

        $content=preg_replace($pattern_from, $pattern_to, $content);
        $wg_compiler->bundle_vars = array('setting'=>&$params);
        $return .= 'ob_start();?'.'>'.$wg_compiler->compile($content).'<?'.'php ';
        $wg_compiler->bundle_vars = null;

        $div_id = ($widget['domid']) ? $widget['domid'] : 'site_widgetsid_' . $widget['widgets_id'];

        $return .= '$body = str_replace(\'%THEME%\',$__THEME_URL,ob_get_contents());ob_end_clean();';
        /*--------------------获取border-----------------------------*/
        if(file_exists($_border = THEME_DIR.'/'.$wg_compiler->controller->get_theme().'/'.$widget['border'])){
            $return .= "\$this->_vars = array('body'=>&\$body,'title'=>'{$widget['title']}','widgets_id'=>'".$div_id."','widgets_classname'=>'{$widget['classname']}');";

            $return.= '?'.'>'.$wg_compiler->compile(file_get_contents($_border)).'<?'.'php ';
        }else{
            $return .= 'echo $body;unset($body);';
        };

        return $return;
    }//End Function

}
