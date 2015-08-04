<?php
class business_view_compiler extends site_view_compiler
{
    function compile_widgets($attrs, &$compiler)
    {
        $store_id = $compiler->controller->pagedata['store_id'];
        $theme = $compiler->controller->get_theme();
        $current_file = $compiler->controller->_files[0];
        $is_store_widgets=false;
        
        //$is_store_widgets = !($store_id && ($theme.'/block/header.html' == $current_file|| $theme.'/block/footer.html' == $current_file));
        if(!in_array($current_file,array($theme.'/block/header.html',$theme.'/block/footer.html',$theme.'/block/shop_nav.html'))){
            if($store_id){
                $is_store_widgets=true;
            }
        }
        if (!kernel::single('business_theme_widget')->theme_modified($store_id, $current_file)) {
            $is_store_widgets=false;
        }
    
        
        
        $slot = intval($compiler->_wgbar[$compiler->controller->_files[0]]++);

        if (!$compiler->is_preview){
            if(!isset($compiler->_cache[$current_file])){
                if($is_store_widgets){
                    $all = app::get('business')->model('widgets_instance')->select()->where('core_file = ?', $current_file)->where('store_id = ?', intval($store_id))->order('widgets_order ASC')->instance()->fetch_all();
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
                if($is_store_widgets){
                    $all = app::get('site')->model('widgets_instance')->select()->where('core_file = ?', $current_file)->where('store_id = ?', intval($store_id))->order('widgets_order ASC')->instance()->fetch_all();
                }else{
                    $all = app::get('site')->model('widgets_instance')->select()->where('core_file = ?', $current_file)->order('widgets_order ASC')->instance()->fetch_all();
                }
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

    

}
    