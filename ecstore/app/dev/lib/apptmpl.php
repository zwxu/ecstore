<?php
class dev_apptmpl{
    
    function init($name,$template){
        
        switch($template){
            case 'desktop':
                $source_dir = APP_DIR.'/base/examples/app';
                break;
                
            case 'storage':
                $source_dir= APP_DIR.'/base/examples/app';
                break;
            
            case 'cache':
                $source_dir = APP_DIR.'/base/examples/app';
                break;
            
            default:
                $source_dir = APP_DIR.'/base/examples/app';
        }
        
        kernel::log('Creating application '.$name.'...');
        utils::cp($source_dir,APP_DIR.'/'.$name);
        utils::replace_p(APP_DIR.'/'.$name,array(''=>$name));
        kernel::log('ok.');
        return APP_DIR.'/'.$name;
    }
    
    function get_name(){
        return app::get('dev')->_('应用程序');
    }
    
    function get_templates(){
        return array(
                'web'=>array(app::get('dev')->_('web应用'),app::get('dev')->_('完整的web应用, MVC风格, 有desktop组件')),
                'desktop'=>array(app::get('dev')->_('控制界面扩展'),app::get('dev')->_('控制界面扩展展展展展展展展')),
                'file'=>array(app::get('dev')->_('文件存储插件'),app::get('dev')->_('文件存储插件件件件件件件件件')),
                'cache'=>array(app::get('dev')->_('缓存插件'),app::get('dev')->_('缓存插件件件件件件件件件')),
                'passport'=>array(app::get('dev')->_('登录方式接口'),'passport'),
            );
    }
    
    function get_command(){
        return array(
                'dev:app package'=>app::get('dev')->_('打包'),
            );
    }
    
}