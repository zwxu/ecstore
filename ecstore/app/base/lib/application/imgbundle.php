<?php

class base_application_imgbundle extends base_application_prototype_filepath 
{
    var $path = 'statics';

    public function install() 
    {
        $dir = $this->getPathname();
        if(is_dir($dir) && realpath($dir) == realpath($this->target_app->res_dir . '/bundle')){
            $spriteinfo = kernel::single('base_application_imgbundle_factory')
                ->reset()
                ->set_app($this->target_app->app_id)
                ->set_directory('bundle')
                ->set_output('ex_' . $this->target_app->app_id . '.png')
                ->create();
            kernel::log(sprintf('%s bundle create Ok!', $this->target_app->app_id));
            base_kvstore::instance('imgbundle')->store('imgbundle_' . $this->target_app->app_id, $spriteinfo);
            kernel::log(sprintf('%s spriteinfo save Ok!', $this->target_app->app_id));
        }
    }//End Function
    
    public function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        base_kvstore::instance('imgbundle')->delete('imgbundle_' . $app_id);
    }
    
    public function last_modified($app_id){
        $info_arr = array();
        foreach($this->detect($app_id) as $item){
            $dir = $this->getPathname();
            if(is_dir($dir) && realpath($dir) == realpath($this->target_app->res_dir . '/bundle')){
                foreach(utils::tree($dir) AS $k=>$v){
                    if(!is_file($v))  continue;
                    $info_arr[$v] = md5_file($v);
                }
                ksort($info_arr);
                continue;
            }
        }
        return md5(serialize($info_arr));
    }

}//End Class