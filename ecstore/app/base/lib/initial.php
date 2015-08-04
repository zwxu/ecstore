<?php


class base_initial
{

    function __construct($app_id) 
    {
        $this->app = app::get($app_id);
    }//End Function

    public function init() 
    {
        if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$this->app->app_id.'/initial')){
            $demo_dir = CUSTOM_CORE_DIR.'/'.$this->app->app_id.'/initial';
        }else{
            $demo_dir = $this->app->app_dir . '/initial';
        }
        if(is_dir($demo_dir)){
            $handle = opendir($demo_dir);
            while($file = readdir($handle)){
                $realfile = $demo_dir . '/' . $file;
                if(is_file($realfile)){
                    list($app_id, $model, $ext) = explode('.', $file);
                    if($ext == 'sdf'){
                        $this->init_sdf($app_id, $model, $realfile);
                    }elseif($ext=='php' && $model=='setting'){
                        $setting = include($realfile);
                        $this->init_setting($app_id, $setting);
                    }
                }
            }
            closedir($handle);
        }
    }//End Function

    public function init_setting($app_id, $setting) 
    {
        $app = app::get($app_id);
        if(is_array($setting)){
            foreach($setting AS $key=>$value){
                $app->setConf($key, $value);
            }
        }
    }//End Function

    public function init_sdf($app_id, $model, $file) 
    {
        $handle = fopen($file, 'r');
        if($handle){
            while(!feof($handle)){
                $buffer .= fgets($handle);
                if(!($sdf = unserialize($buffer))){
                    continue;
                }
                app::get($app_id)->model($model)->db_save($sdf);
                $buffer = '';
            }
            fclose($handle);
        }
    }//End Function
}//End Class
