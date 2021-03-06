<?php
class cellphone_task
{
    public function pre_install()
    {
        kernel::log('Initial cellphone');
        kernel::single('base_initial', 'cellphone')->init();
    }
    
    public function post_install($options)
    {
        $conf = app::get('cellphone')->getConf('image.cellphone.set');
        app::get('cellphone')->setConf('image.set', $conf);
        $obj_image = app::get('image')->model('image');
        $app_dir = app::get('image')->app_dir;
        foreach($conf as $item){
            $obj_image->store($app_dir.'/initial/default_images/'.$item['default_image'].'.gif',$item['default_image']);
        }
        $cols = array(
            'cl_ident'=>array(
              'type' => 'varchar(200)',
            ),
            'cl_url'=>array(
              'type' => 'varchar(200)',
            ),
            'cs_ident'=>array(
              'type' => 'varchar(200)',
            ),
            'cs_url'=>array(
              'type' => 'varchar(200)',
            ),
        );
        $this->schema_install($cols,'image','image');
    }
    
    public function post_uninstall()
    {
        $this->schema_uninstall(array('cl_ident,cl_url,cs_ident,cs_url'),'image','image');
    }

    private function schema_install($col = array(),$app_id,$table_name,$index = array()){
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php')){
            $file_path = CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
        }else{
            $file_path = ROOT_DIR.'/app/'.$app_id.'/dbschema/'.$table_name.'.php';
        }
        include($file_path);
        if(defined('CUSTOM_CORE_DIR') && is_dir(CUSTOM_CORE_DIR.'/'.$app_id)){
            if(!is_dir(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema')){
                mkdir(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema', 0700);
                $filename=CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
                $fp=fopen("$filename", "w+"); //打开文件指针，创建文件
                if ( !is_writable($filename) ){
                    die("文件:" .$filename. "不可写，请检查！");
                }
                fclose($fp);  //关闭指针
            }
            $file_path = CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
        }
        $db[$table_name]['columns'] = array_merge($db[$table_name]['columns'],$col);
        if($index){
            if($db[$table_name]['index']){
                $db[$table_name]['index'] = array_merge($db[$table_name]['index'],$index);
            }else{
                $db[$table_name]['index'] = $index;
            }
        }
        $schema = "\$db['".$table_name."']=".var_export($db[$table_name],true);
        $schema = "<?php \r\n ".$schema.";";
        file_put_contents($file_path,$schema);        
    }

    private function schema_uninstall($col_names = array(),$app_id,$table_name,$index_names = array()){
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php')){
            $file_path = CUSTOM_CORE_DIR.'/'.$app_id.'/dbschema/'.$table_name.'.php';
        }else{
            $file_path = CORE_DIR.'/app/'.$app_id.'/dbschema/'.$table_name.'.php';
        }
        include($file_path);
        foreach($col_names as $col_name){
            if(array_key_exists($col_name,$db[$table_name]['columns'])){
                unset($db[$table_name]['columns'][$col_name]);
            }
        }
        foreach($index_names as $index_name){
            if(array_key_exists($index_name,$db[$table_name]['index'])){
                unset($db[$table_name]['index'][$index_name]);
            }
        }
        if(empty($db[$table_name]['index'])){
            unset($db[$table_name]['index']);
        }
        $schema = "\$db['".$table_name."']=".var_export($db[$table_name],true);
        $schema = "<?php \r\n ".$schema.";";
        file_put_contents($file_path,$schema);        
    }
}

