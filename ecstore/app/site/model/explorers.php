<?php


class site_mdl_explorers extends dbeav_model 
{

    public function pre_recycle($params) 
    {
        trigger_error(app::get('site')->_("此数据不能人为删除"), E_USER_ERROR);
        return false;
    }//End Function
}//End Class
