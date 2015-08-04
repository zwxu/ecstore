<?php


class business_theme_complier 
{

    function compile_widgets($tag_args, &$smarty){
        $current_file = $smarty->controller->_files[0];
        $theme = $smarty->controller->get_theme();
        
        if($tag_args['id']){
            $id = ','.$tag_args['id'];
        }
        $class = "";
        if(in_array($current_file,array($theme.'/block/header.html',$theme.'/block/footer.html',$theme.'/block/shop_nav.html'))){
            $admin_load = 'kernel::single(\'business_theme_widget\')->admin_load($s,$i'.$id.');';
        }else{
            $class = 'class="shopWidgets_panel"';
            $admin_load = 'kernel::single(\'business_theme_widget\')->store_load($s,$i'.$id.',false,$this->pagedata[\'store_id\']);';  
        }
        
        
        if ($tag_args['id'])
            return '$s=$this->_files[0];
            $i = intval($this->_wgbar[$s]++);
            echo \'<div '.$class.' base_file="\'.$s.\'" base_slot="\'.$i.\'" base_id='.$tag_args['id'].' widgets_theme="">\';'.$admin_load.'echo \'</div>\';';
        else
            return '$s=$this->_files[0];
            $i = intval($this->_wgbar[$s]++);
            echo \'<div '.$class.' base_file="\'.$s.\'" base_slot="\'.$i.\'" base_id="" widgets_theme="">\';'.$admin_load.'echo \'</div>\';';

    }
}//End Class
