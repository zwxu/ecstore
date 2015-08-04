<?php

 

class site_mdl_widgets_proinstance extends dbeav_model 
{
    var $has_tag = true;

    public function searchOptions() 
    {
        $arr = parent::searchOptions();
        return array_merge($arr, array(
                'name' => app::get('site')->_('实例名称'),
            ));
    }//End Function

}//End Class
