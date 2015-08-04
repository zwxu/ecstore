<?php
 
class dev_finder_project{

    var $detail_basic = '信息';
    
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function detail_basic($order_id)
    {
        $render = $this->app->render();
        return $render->fetch('project/detail.html');
    }
}
