<?php

class business_finder_brand
{
	var $column_editbutton ;
    var $column_editbutton_order;
    var $column_shopname ;
    var $column_shopname_order;
    var $detail_items = '基本信息';
    
    public function __construct($app)
    {
       
        $this->app = $app;
        $this->column_editbutton = app::get('business')->_('操作');    
        $this->column_editbutton_order = app::get('business')->_('1');

        $this->column_shopname = app::get('business')->_('商铺名称');    
        $this->column_shopname_order = app::get('business')->_('2');
    }

    /**
     * 商铺名称显示
     * @params row
     * @return 商铺名称
     */
    function column_shopname($row){
        $obj_brand = app::get('business')->model('brand');
        $render = $this->app->render();

        $store_id = $obj_brand->getList('store_id', array('id'=>$row['id']));
        $store_id = $store_id['0']['store_id'];

        $shopname = $obj_brand->getStoreNameBySid($store_id);

        return $shopname;
    }

    /**
     * 审核显示
     * @params row
     */
    public function column_editbutton($row)
    {
   
        $obj_brand = app::get('business')->model('brand');
        $render = $this->app->render();
        
         $arr_links = array(
            
            'accept'=>array(
                'href'=>"index.php?app=business&ctl=admin_apply&act=verify&flag=pass&id=".$row['id'],
                'label'=>app::get('business')->_('审核通过'),
            ),
            'noaccept'=>array( 
                'href'=>"javascript:no_apply(".$row['id'].")",
                'label'=>app::get('business')->_('审核不通过'),
            ),
        );
         
        $status = $obj_brand->getList('status', array('id'=>$row['id']));
        $status = $status['0']['status'];

        if($status !=0){
           unset($arr_links);
        }
        
        $render->pagedata['arr_links'] = $arr_links;
        return $render->fetch('admin/brand/actions.html');
    }
    
    /**
     * 后台查看
     * @params id
     */
	public function detail_items($id)
    {
        $render = app::get('base')->render();
        $obrand = $this->app->model('brand');

		$brand = $obrand->getList('*',array('id'=>$id));
        $brand = $brand[0];

		$brand['brand_logo'] = base_storager::image_path($brand['brand_logo'],'s');
		$brand['shop_name'] = $obrand->getStoreNameBySid($brand['store_id']);
        $brand['brand_aptitude'] = base_storager::image_path($brand['brand_aptitude'],'s');

        $render->pagedata['brand'] = $brand;
        return $render->fetch('admin/brand/brand_items.html',$this->app->app_id);
    }

   
        
}