<?php

class sphinx_ctl_default extends desktop_controller 
{

    function save_search_config() {
    	$this->begin();
    	$conf = $_POST ;
        app::get('sphinx')->setConf('sphinx_search_goods', serialize($conf));
        $this->end(true, $this->app->_('配置保存成功'));
    }
}//End Class