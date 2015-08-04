<?php

 
class site_misc_task implements base_interface_task{

    function rule() {
	return '0 0 */1 * *';
    }

    function exec() {
	$this->auto_sitemaps();
    }

    function description() {
	return '生成sitemaps';
    }

    private function auto_sitemaps() 
    {
        kernel::single('site_sitemaps')->create();
    }//End Function 
    
    
    

}
