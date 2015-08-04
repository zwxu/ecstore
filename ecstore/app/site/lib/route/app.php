<?php


class site_route_app
{
    function __construct($app) 
    {
        $this->app = $app;
    }//End Function
    
    public function store_static($data) 
    {
        return app::get('site')->model('route_statics')->save($data);
    }//End Function

    public function fetch_static($filter) 
    {
        $rows = app::get('site')->model('route_statics')->getList('*', $filter);
        return $rows[0];
    }//End Function

    public function delete_static($filter) 
    {
        return app::get('site')->model('route_statics')->delete($filter);
    }//End Function
        
}//End Class
