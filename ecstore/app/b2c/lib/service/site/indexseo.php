<?php



class b2c_service_site_indexseo 
{

    public function title() 
    {
        return app::get('b2c')->getConf('system.shopname');
    }//End Function

}//End Class