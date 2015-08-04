<?php


class site_finder_callback_modules 
{
    public function recycle($params) 
    {
        return kernel::single('site_module_base')->create_site_config();
    }//End Function

}//End Class
