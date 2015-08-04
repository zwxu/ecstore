<?php
 
 

class b2c_ctl_admin_shopnode extends desktop_controller
{
    var $workground = 'desktop_other';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    /**
     * 网店节点显示
     * @param null
     * @return null
     */
    public function index()
    {
        $this->pagedata['node_id'] = base_shopnode::node_id($this->app->app_id);
        $this->pagedata['node_type'] = base_shopnode::node_type($this->app->app_id);
        
        $this->page('admin/shopnode.html');
    }
}