<?php

class site_command_sitemap extends base_shell_prototype 
{
    var $command_create = '刷新sitemap';
    public function command_create() 
    {
        kernel::single('site_sitemaps')->create();
        kernel::log('Sitemap Create OK...');
    }//End Function

}//End Class