<?php
 
 

class b2c_ctl_admin_shoprelation extends desktop_controller
{
    var $workground = 'desktop_other';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    function index()
    {
        $callback_url = '';
        $api_url = kernel::base_url(1).kernel::url_prefix().'/api';
        $ceti_id = base_certificate::get('certificate_id');
        $node_id = base_shopnode::node_id($this->app->app_id);
        $obj_user = kernel::single('desktop_user');
        $user_id = $obj_user->user_data['user_id'];
        $user_name = $obj_user->user_data['name'];
        $api_v = $this->app->getConf("api.local.version");
        $this->finder('b2c_mdl_shop',array(
            'title'=>app::get('b2c')->_('数据互联') . app::get('b2c')->_('证书：') . $ceti_id . ', ' . app::get('b2c')->_('节点：') . $node_id,
            'actions' => array(
                    array('label'=>app::get('b2c')->_('新建绑定关系'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_shoprelation&act=addnew','target'=>'_blank'),
                    array('label'=>app::get('b2c')->_('查看绑定情况'),'icon'=>'add.gif','onclick'=>'new Request({evalScripts:true,url:\'index.php?ctl=shoprelation&act=index&p[0]=accept&p[1]=' . $this->app->app_id . '&p[2]=' . $callback . '&p[3]=' . $api_url.'&p[4]=' . $user_id . '&p[5]=' . $user_name . '&p[6]=' . $api_v . '\'}).get()'),
                ),
            ));
    }
    
    public function addnew()
    {
        if ($_POST['shop'])
        {
            $this->begin();
            
            if ($_POST['shop']['shop_id'])
                $arr_data['shop_id'] = $_POST['shop']['shop_id'];
            $arr_data['name'] = $_POST['shop']['name'];
            foreach ($_POST['shop'] as $key=>$value)
            {
                $arr_data[$key] = $value;
            }
            $obj_shop = $this->app->model('shop');
            
            if ($obj_shop->save($arr_data))
            {
                $this->end(true, app::get('b2c')->_('添加成功！'));
            }
            else
            {
                $this->end(false,app::get('b2c')->_('添加失败！'));
            }
        }
        else
        {
            $this->singlepage('admin/page_shoprelation.html');
        }
    }
    
    public function showEdit($shop_id=0)
    {
        $obj_shop = $this->app->model('shop');
        $arr_shop = $obj_shop->dump($shop_id);
        
        if ($arr_shop)
        {
            $this->pagedata['shoprelation'] = $arr_shop;
        }
        
        $this->singlepage('admin/page_shoprelation.html');
    }
}