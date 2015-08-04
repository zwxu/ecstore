<?php

 

/**
 * 快递中心快递单据管理
 * version 0.1
 */
 
class express_ctl_admin_delivery_center extends desktop_controller
{
    public $workground = 'ectools_ctl_admin_order';
    public $defaultWorkground = "ectools.wrokground.order";
    
    /**
     * 类公开的构造方法
     * @params object app
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    /**
     * filter的列表页方法
     * @params null
     * @return null
     */
    public function index()
    {
        if($_GET['action'] == 'export') $this->_end_message = '导出发货信息';
        $this->finder('express_mdl_dly_center',array(
            'title'=>app::get('express')->_('发货信息管理'),
            'actions'=>array(
            array('label'=>app::get('express')->_('添加发货信息'),
    'target'=>'dialog::{title:\''.app::get('express')->_('添加发货信息').'\',width:0.7,height:0.8}',
	  'href'=>'index.php?app=express&ctl=admin_delivery_center&act=addnew'),
                        ),'use_buildin_set_tag'=>false,'use_buildin_recycle'=>true,'use_buildin_filter'=>false,'use_buildin_export'=>true,
            ));
    }
    
    /**
     * 添加发货信息方法
     * @params null
     * @return null
     */
    public function addnew()
    {
		$this->pagedata['save_action'] = 'create';
        $this->display('admin/delivery/delivery_edit.html');
    }
    
    /**
     * 建立发货单方法
     * @params null
     * @return null
     */
    public function create()
    {   
		$this->begin("javascript:finderGroup['".$_POST['finder_id']."'].refresh();");
        if (isset($_POST) && $_POST)
        {
            $arr_deliverys = $_POST['delivery_center'];
            
            // 检查必填项目
            if (!isset($arr_deliverys['name']) || !$arr_deliverys['name'])
            {
               
                $this->end(false,app::get('express')->_('出货点名称不能为空！'));
            }
            
            if (!isset($arr_deliverys['address']) || !$arr_deliverys['address'])
            {
              
                $this->end(false,app::get('express')->_('出货点地址不能为空！'));
            }
            
            $obj_delivery_center = $this->app->model('dly_center');
            if ($dly_center_id = $obj_delivery_center->insert($arr_deliverys))
			{
				if (isset($_POST['is_default']) && $_POST['is_default'])
				{
					app::get('b2c')->setConf('system.default_dc', $dly_center_id);
				}
				
				$this->end(true, app::get('express')->_('添加成功！'));
			}
			else
			{
				$this->end(false,app::get('express')->_('出货信息不正确！'));
			}
        }
    }
	
	/**
	 * 修改发货信息
	 * @param null
	 * @return null
	 */
	public function modify()
	{
		$this->begin("javascript:finderGroup['".$_POST['finder_id']."'].refresh();");
        if (isset($_POST) && $_POST)
        {
            $arr_deliverys = $_POST['delivery_center'];
            
            // 检查必填项目
            if (!isset($arr_deliverys['name']) || !$arr_deliverys['name'])
            {
               
                $this->end(false,app::get('express')->_('出货点名称不能为空！'));
            }
            
            if (!isset($arr_deliverys['address']) || !$arr_deliverys['address'])
            {
              
                $this->end(false,app::get('express')->_('出货点地址不能为空！'));
            }
            
            $obj_delivery_center = $this->app->model('dly_center');
            if ($obj_delivery_center->save($arr_deliverys))
			{
				if (isset($_POST['is_default']) && $_POST['is_default'])
				{
					app::get('b2c')->setConf('system.default_dc', $arr_deliverys['dly_center_id']);
				}
				
				$this->end(true, app::get('express')->_('修改成功！'));
			}
			else
			{
				$this->end(false,app::get('express')->_('出货信息不正确！'));
			}
        }
	}
    
    public function showEdit($dly_center_id)
    {
		if (!isset($dly_center_id) || !$dly_center_id)
        {
            $this->begin("");
            $this->end(false, app::get('express')->_("发货地址不存在！"));
        }
        
        $obj_dly_center = $this->app->model('dly_center');
        $arr_dly_center = $obj_dly_center->dump($dly_center_id);
        $this->pagedata['dly_center'] = $arr_dly_center;
        $this->pagedata['default_dc'] = app::get('b2c')->getConf('system.default_dc');
		$this->pagedata['save_action'] = 'modify';
        $this->display('admin/delivery/delivery_edit.html');
    }
    
    public function instance($dly_center_id){
        $obj_delivery_center = $this->app->model('dly_center');
        $aData = $obj_delivery_center->dump($dly_center_id);
        
        $this->pagedata['the_dly_center'] = $aData;
        $this->display('admin/delivery/dly_center.html');
    }
}