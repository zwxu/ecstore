<?php


class site_mdl_themes extends dbeav_model
{

    public $defaultOrder = array('is_used', 'asc');
       
    public function delete_file($filter) 
    {
        $rows = $this->getList('*',$filter);
        foreach($rows AS $row){
            if($row['theme'] == kernel::single('site_theme_base')->get_default()){
                trigger_error(app::get('site')->_("默认模板不能删除，请重新选择。"), E_USER_ERROR);
                return false;
            }
        }
        foreach($rows AS $row){
            kernel::single('site_theme_install')->remove_theme($row['theme']);
        }
        return true;
    }//End Function

}//End Class
