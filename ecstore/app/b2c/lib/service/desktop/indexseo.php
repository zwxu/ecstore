<?php



class b2c_service_desktop_indexseo
{

    public function title() 
    {
        return app::get('b2c')->getConf('system.shopname');
    }//End Function

}//End Class