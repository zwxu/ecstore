<?php

 
/**
 * 这个类实现finder操作的方法
 * 
 * @version 0.1
 * @package aftersales.lib
 */
class aftersales_finder_return_product
{
	/**
	 * @var 定义方法名称的变量
	 */
    public $detail_basic = '基本信息';
    
	/**
	 * @var 所有售后的状态
	 */
    private $arr_status = array();
    
    /**
     * 构造方法，定义全局变量app和状态值
     * @param object app 类
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->arr_status = array(
            '1' => app::get('aftersales')->_('退款协议等待卖家确认'),
            '2' => app::get('aftersales')->_('审核中'),
            '3' => app::get('aftersales')->_('接受申请'),
            '4' => app::get('aftersales')->_('完成'),
            '5' => app::get('aftersales')->_('拒绝'),
            '6' => app::get('aftersales')->_('已收货'),
            '7' => app::get('aftersales')->_('已质检'),
            '8' => app::get('aftersales')->_('补差价'),
            '9' => app::get('aftersales')->_('已拒绝退款'),
            '10' => app::get('aftersales')->_('已取消'),
            '11' => app::get('aftersales')->_('卖家不同意协议，等待买家修改'),
            '12' => app::get('aftersales')->_('买家已退货，等待卖家确认收货'),
            '13' => app::get('aftersales')->_('已修改'),
            '14' => app::get('aftersales')->_('卖家收到退货，拒绝退款'),
            '15' => app::get('aftersales')->_('卖家同意退款，等待卖家打款至平台'),
            '16' => app::get('aftersales')->_('卖家已退款，等待系统结算'),
        );
    }

    var $column_control = '操作';
    var $column_control_width = 100;

 	function column_control($row){
		$rp = app::get('aftersales')->model('return_product');
        $rp_data = $rp->dump($row['return_id'],'*');
        if($rp_data['is_safeguard'] == '2' && $rp_data['is_return_money'] == '2' && $rp_data['status'] == '16'){
            return '<a href="index.php?app=aftersales&ctl=admin_returnproduct&act=balance_refund_finish&return_id='.$row['return_id'].'"  >'.app::get('aftersales')->_('确认结算').'</a>';
        }
    }
    
   	/**
   	 * finder的下拉详细页面
   	 * @param sring 售后序号
   	 * @return string 售后详细的内容
   	 */
    public function detail_basic($return_id)
    {
        $render = $this->app->render();
        $obj_return_product = $this->app->model('return_product');
        $arr_return_product = $obj_return_product->dump($return_id);
        //if ($arr_return_product['comment'])
            //$arr_return_product['comment'] = unserialize($arr_return_product['comment']);
        if ($arr_return_product['product_data'])
            $arr_return_product['product_data'] = unserialize($arr_return_product['product_data']);
        //添加商品链接
        $obj_products = app::get('b2c')->model('products');
        $obj_store = app::get('business')->model('storemanger');
        $store_info = $obj_store->dump(array('store_id'=>$arr_return_product['store_id']),'zip,tel');
        $arr_return_product['store_phone'] = $store_info['tel'];
        $arr_return_product['store_mail'] = $store_info['zip'];
        foreach($arr_return_product['product_data'] as $key=>$val){
            $gid = $obj_products->dump(array('bn'=>$val['bn']),'goods_id');
            $arr_return_product['product_data'][$key]['url'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$gid['goods_id']));
        }
        if ($arr_return_product['status'])
        {
			$arr_return_product['status_code'] = $arr_return_product['status'];
			$arr_return_product['status'] = $this->arr_status[$arr_return_product['status']];
            
        }
        if ($_GET['status'])
            $arr_return_product['return_status'] = $_GET['status'];
        else
            $arr_return_product['return_status'] = '1';
        $render->pagedata['info'] = $arr_return_product;

        //判断是否对接OCS
        $obj_b2c_shop = app::get('b2c')->model('shop');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type'=>'ecos.ome'));
        if($cnt>0){
            $render->pagedata['showBtn'] = false;
        }else{
            $render->pagedata['showBtn'] = true;
        }

        $obj_order = app::get('aftersales')->model('return_log');
        $return_logs = $obj_order->getList('*',array('return_id'=>$return_id),-1,-1,'alttime DESC');

        foreach($return_logs as $key=>$val){
             $images = explode(',',$val['image_file']);
             foreach($images as $k=>$v){
                 if($v){
                    $real_images[] = base_storager::image_path($v,'s');
                 }
             }
             $return_logs[$key]['images'] = $real_images;
             unset($real_images);
        }

        $render->pagedata['return_logs'] = $return_logs;
      
        return $render->fetch('admin/return_product/detail.html');
    }
    
    /**
     * @var 定义finder操作按钮的方法名称变量
     */
    //public $column_editbutton = '操作';
    public $column_editbutton_order = '1';
    /**
     * finder操作按钮的方法实现
     * @param array dump数据库该行的信息
     * @return string 操作链接的html信息
     */
    public function column_editbutton($row)
    {
        //判断是否对接OCS
        $obj_b2c_shop = app::get('b2c')->model('shop');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type'=>'ecos.ome'));
        if($cnt>0) return '';

        $render = $this->app->render();
        $arr = array(
            'app'=>$_GET['app'],
            'ctl'=>$_GET['ctl'],
            'act'=>$_GET['act'],
            'action'=>'detail',
            'finder_name'=>$_GET['_finder']['finder_id'],
            'finder_id'=>$_GET['_finder']['finder_id'],
            'finderview'=>'detail_basic',
        );
        
        $link = 'index.php?'.utils::http_build_query($arr).'&id='.$row['return_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'];
        $arr_links = array(
            'audit'=>array(
                'id'=>'x-return-status_'.$row['return_id'].'_2',
                'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
                //'data'=>'return_id='.$row['return_id'].'&status=2',
                'target'=>'request::{url:\''.$link.'&status=2\',data:\'return_id='.$row['return_id'].'&status=2\'}',
                'comment'=>'false',
                'label'=>app::get('aftersales')->_('审核中'),
            ),
            'accept'=>array(
                'id'=>'x-return-status_'.$row['return_id'].'_3',
                'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
                //'data'=>'return_id='.$row['return_id'].'&status=3',
                'target'=>'request::{url:\''.$link.'&status=3\',data:\'return_id='.$row['return_id'].'&status=3\'}',
                'comment'=>'true',
                'label'=>app::get('aftersales')->_('接受申请'),
            ),
            'finish'=>array(
                'id'=>'x-return-status_'.$row['return_id'].'_4',
                'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
                //'data'=>'return_id='.$row['return_id'].'&status=4',
                'target'=>'request::{url:\''.$link.'&status=4\',data:\'return_id='.$row['return_id'].'&status=4\'}',
                'comment'=>'true',
                'label'=>app::get('aftersales')->_('完成'),
            ),
            'reduce'=>array(
                'id'=>'x-return-status_'.$row['return_id'].'_5',
                'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
                //'data'=>'return_id='.$row['return_id'].'&status=5',
                'target'=>'request::{url:\''.$link.'&status=5\',data:\'return_id='.$row['return_id'].'&status=5\'}',
                'comment'=>'true',
                'label'=>app::get('aftersales')->_('拒绝'),
            ),
        );
        $render->pagedata['arr_links'] = $arr_links;        
        return $render->fetch('admin/actions.html');
    }
}