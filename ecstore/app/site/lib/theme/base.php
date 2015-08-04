<?php



class site_theme_base
{

    public function set_last_modify()
    {
        return app::get('site')->setConf('site_theme_last_modify', time());
    }//End Function

    public function get_last_modify()
    {
        return app::get('site')->getConf('site_theme_last_modify');
    }//End Function

    public function set_default($theme){
        $theme_sytle = $this->get_theme_style($theme);
        if(empty($theme_sytle)){
            $this->set_theme_style($theme, '');
        }//todo：如果无样式或没有选择过样式，强制设置为空字符串
        app::get('site')->model('themes')->update(array('is_used'=>'false'), array());
        app::get('site')->model('themes')->update(array('is_used'=>'true'), array('theme'=>$theme));
        return app::get('site')->setConf('current_theme', $theme);
    }

    public function theme_exists()
    {
        return (is_null($this->get_default())) ? false : true;
    }//End Function

    public function get_default()
    {
        return app::get('site')->getConf('current_theme');
    }//End Function

    public function update_theme($aData)
    {
        return app::get('site')->model('themes')->save($aData);
    }//End Function

    public function set_theme_style($theme, $style)
    {
        return app::get('site')->setConf('theme_style.'.$theme, $style);
    }//End Function

    public function get_theme_style($theme)
    {
        return app::get('site')->getConf('theme_style.'.$theme);
    }//End Function

    public function set_theme_cache_version($theme)
    {
        return app::get('site')->setConf('theme_cache_version.'.$theme, time());
    }//End Function

    public function get_theme_cache_version($theme)
    {
        return app::get('site')->getConf('theme_cache_version.'.$theme);
    }//End Function

    public function get_view($theme)
    {
        if ($handle=opendir(THEME_DIR.'/'.$theme)){
            $views = array();
            while(false!==($file=readdir($handle))){
                if ($file{0}!=='.' && $file{0}!=='_' && is_file(THEME_DIR.'/'.$theme.'/'.$file) && (($t=strtolower(strstr($file,'.')))=='.html' || $t=='.htm')){
                    $views[] = $file;
                }
            }
            closedir($handle);
            return $views;
        }else{
            return false;
        }
    }//End Function


    public function get_basic_config($theme){
        $basic_config='on';
        $path = THEME_DIR.'/'.$theme;
        if(!is_dir($path)&&!ECAE_MODE)  return array();
        $workdir = getcwd();
        chdir($path);
        $xml = kernel::single('site_utility_xml');
        $content = kernel::single('site_theme_tmpl_file')->get_theme_xml($theme, 'theme.xml');
        $config = $xml->xml2arrayValues($content);

        $basic_config = $config['theme']['basic_config']?$config['theme']['basic_config']['value']:$basic_config;
        chdir($workdir);
        return $basic_config;
    }

    /*old functions*/

    public function get_border_from_themes($theme){
        $wights_border=Array();
        $path = THEME_DIR.'/'.$theme;
        if(!is_dir($path))  return array();
        $workdir = getcwd();
        chdir($path);
        $xml = kernel::single('site_utility_xml');
        $content=file_get_contents('theme.xml');
        $config = $xml->xml2arrayValues($content);

        if(isset($config['theme']['borders']['set']['attr'])){
                $wights_border[$config['theme']['borders']['set']['attr']['tpl']]=file_get_contents($config['theme']['borders']['set']['attr']['tpl']);
        }elseif( is_array($config['theme']['borders']['set'] ) ) {
            foreach($config['theme']['borders']['set'] as $k=>$v){
                $wights_border[$v['attr']['tpl']]=file_get_contents($v['attr']['tpl']);
            }
        }
        chdir($workdir);
        return $wights_border;
    }

    public function get_theme_styles($theme)
    {
        $aConfig = app::get('site')->model('themes')->select()->columns(array('config'))->where('theme = ?', $theme)->instance()->fetch_one();
        if(is_array($aConfig['config'])){
            foreach($aConfig['config'] AS $key=>$value){
                //if($value['type'] != 'fullstyle')   continue;
                $styles[] = $value;
            }
        }
        return $styles;
    }//End Function

    public function get_theme_borders($theme){
        $aConfig = app::get('site')->model('themes')->select()->columns(array('config'))->where('theme = ?', $theme)->instance()->fetch_one();
        for($i=0;$i<count($aConfig['borders']);$i++){
            $aData[$aConfig['borders'][$i]['tpl']]=$aConfig['borders'][$i]['key'];
        }
        return $aData;
    }

    public function get_theme_info($theme)
    {
        return app::get('site')->model('themes')->select()->where('theme = ?', $theme)->instance()->fetch_row();
    }//End Function
    
    public function update_theme_tmpl($theme)
    {
        kernel::single('site_theme_tmpl')->update($theme);
    }

    public function install_theme_widgets($theme)
    {
        foreach(kernel::servicelist('site_theme_content_detector') AS $service){
            $service->update($theme);
            base_kvstore::instance('site_themes')->store('theme_last_modified'.get_class($service).$theme, $service->last_modified($theme));
        }
    }//End Function

    public function update_theme_widgets($theme, $force=false)
    {
        foreach(kernel::servicelist('site_theme_content_detector') AS $service){
            $last_modified = $service->last_modified($theme);
            if( $force
            || base_kvstore::instance('site_themes')->fetch('theme_last_modified'.get_class($service).$theme, $modified) === false
            || $last_modified != $modified ){
                kernel::log('autofix theme widgets...');
                $service->update($theme);
                base_kvstore::instance('site_themes')->store('theme_last_modified'.get_class($service).$theme, $last_modified);
            }
        }
    }//End Function

    public function delete_theme_widgets($theme)
    {
        foreach(kernel::servicelist('site_theme_content_detector') AS $service){
            $service->clear_by_theme($theme);
            base_kvstore::instance('site_themes')->delete('theme_last_modified'.get_class($service).$theme);
        }
    }//End Function

    public function set_theme_views($theme, $views)
    {
        return app::get('site')->setConf('theme_views_' . $theme, $views);
    }//End Function

    public function get_theme_views($theme)
    {
        return app::get('site')->getConf('theme_views_' . $theme);
    }//End Function


    public function maintenance_theme_files($theme_dir=''){
        if (!$theme_dir) return;

        set_time_limit(0);
        cachemgr::init(false);
        header('Content-type: text/html;charset=utf-8');
        ignore_user_abort(false);
        ob_implicit_flush(1);
        ini_set('implicit_flush',true);
        kernel::$console_output = true;
        while(ob_get_level()){
            ob_end_flush();
        }
        echo str_repeat("\0",1024);
        echo '<pre>';
        echo '>update themes'."\n";

        if ($theme_dir==THEME_DIR){
            $dir = new DirectoryIterator($theme_dir);
            foreach($dir as $file)
            {
                $filename = $file->getFilename();
                if($filename{0}=='.'){
                    continue;
                }else{
                    $this->update_theme_widgets($filename);
                }
            }
        }
        else{
            $this->update_theme_widgets($theme_dir);
        }
        echo 'ok.</pre>';
    }

}//End Class
