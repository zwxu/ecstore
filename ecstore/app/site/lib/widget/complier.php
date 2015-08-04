<?php

 

class site_widget_complier 
{

    function compile_widget($tag_args, &$smarty){
        return '$s=$this->_files[0];
        $i = intval($this->_wgbar[$s]++);
        echo \'<div class="shopWidgets_panel">\';
        kernel::single(\'site_widget_proinstance\')->admin_load('.$tag_args['wid'].');echo \'</div>\';';

    }
}//End Class
