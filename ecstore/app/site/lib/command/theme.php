<?php

class site_command_theme extends base_shell_prototype 
{
    
    var $command_checkwidgets = '检查模板挂件';
    var $command_checkwidgets_options = array(
            'force'=>array('title'=>'强制更新','short'=>'f'),
        );
    public function command_checkwidgets(){
        $dir = new DirectoryIterator(THEME_DIR);
        $options = $this->get_options();
        foreach($dir as $file)
        {
            $filename = $file->getFilename();
            if($filename{0}=='.' || !$file->isDir()){
                continue;
            }else{
                kernel::single('site_theme_base')->update_theme_widgets($filename, $options['force']);
                kernel::log(sprintf('THEME %s Widgets Application OK...', $filename));
            }
        }
    }


}//End Class