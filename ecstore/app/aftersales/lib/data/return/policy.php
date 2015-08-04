<?php
 
 
/**
 * 用于获取售后服务的一些数据信息
 * 
 * @version 0.1
 * @package aftersales.lib
 */
class aftersales_data_return_policy extends b2c_api_rpc_request
{
    /**
     * @var model object
     */
    public $rProduct;
    
    /**
     * @var product list item status
     */
    private $arr_status = array(
        '1' => '退款协议等待卖家确认',
        '2' => '审核中',
        '3' => '审核通过',
        '4' => '完成',
        '5' => '审核未通过',
        '6' => '已退款',
        '10' => '已取消',
        '11' => '待修改',
        '12' => '已退货',
        '13' => '已修改',
        '14' => '卖家收到退货，拒绝退款',
        '15' => '卖家同意退款，等待卖家打款至平台',
        '16' => '卖家已退款，等待系统结算',
    );

    private $safeguard_require = array(
        '1' => '不退货部分退款',
        '2' => '需要退货退款',
        '3' => '要求换货',
        '4' => '要求维修',
        '5' => '已经退货，要求退款',
        '6' => '要求退款',
    );

    private $safeguard_type = array(
        '1' => '商品问题',
        '2' => '七天无理由退换货',
        '3' => '发票无效',
        '4' => '退回多付的运费',
        '5' => '未收到货',
    );
    
    /**
     * 构造方法 
     * @param object application
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->rProduct = &$this->app->model('return_product');
    }
    
    /**
     * 得到本类应用的配置参数
     * @param array 参数数组 - 取地址
     * @return boolean true or false
     */
    public function get_conf_data(&$arr_settings)
    {
        $arr_settings['is_open_return_product'] = $this->app->getConf('site.is_open_return_product');
        $arr_settings['return_product_comment'] = $this->app->getConf('site.return_product_comment');
        
        return ($arr_settings && is_array($arr_settings)) ? true : false;
    }
    
    /**
     * 得到满足条件的售后申请列表
     * @param string database table columns
     * @param array conditions
     * @param int page code
     * @return array 结果数组
     */
    public function get_return_product_list($clos='*', $filter = array(),$nPage=1,$limit=10)
    {   
        if(!isset($limit) || $limit == 0){
            $limit = 10;
        }
        $arr_return_products = array();
        
        $aData = $this->rProduct->getList($clos,$filter,($nPage-1)*$limit,$limit,'add_time DESC');
        $count = $this->rProduct->count($filter);
        
        return $arr_return_products = array(
            'data' => $aData,
            'total' => $count,
        );
    }
    
	/**
	 * 改变售后状态
	 * @param mixed sdf 售后信息数组
	 * @return boolean 成功与否
	 */
    public function change_status(&$sdf)
    {
        $is_changed = $this->rProduct->change_status($sdf);
        
        if ($is_changed)
        {
            $arr_data = $this->rProduct->dump($sdf['return_id']);
            $sdf['return_id'] = $arr_data['return_id'];
            $sdf['order_id'] = $arr_data['order_id'];
            $sdf['status'] = $arr_data['status'];
            
            return $sdf['status'];
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 保存售后申请单的信息
     * @param array sdf 标准数组
     * @param string 保存消息
     * @return boolean true or false
     */
    public function save_return_product(&$sdf, &$msg='')
    {
        if(isset($sdf['old_return_id'])){
            $sdf['return_id'] = $sdf['old_return_id'];
            $sdf['return_bn'] = $sdf['old_return_id'];
            $money = $this->rProduct->dump($sdf['old_return_id'],'amount');
            $is_delete = $this->rProduct->delete(array('return_id'=>$sdf['old_return_id']));
            if(!$is_delete){
                $msg = '数据修改失败！';
                return false;
            }
        }else{
            $sdf['return_id'] = $this->rProduct->gen_id();
            $sdf['return_bn'] = $sdf['return_id'];
        }
        $is_save = $this->rProduct->save($sdf);

        if ($sdf['member_id'])
        {
            $obj_members = app::get('b2c')->model('members');
            $arrPams = $obj_members->dump($sdf['member_id'], '*', array(':account@pam' => array('*')));
        }

        //添加退款日志
        
        if(isset($sdf['old_return_id'])){
            $behavior = "updates";
            $log_text = "买家修改退款申请,修改金额从".$money['amount']."改为：".$sdf['amount'].'元！';
            if(!$is_save){
                $result = "FAILURE";
            }else{
                $result = "SUCCESS";
            }
        }else{
            $behavior = "creates";
            $log_text = "买家创建退款申请";
            if(!$is_save){
                $result = "FAILURE";
            }else{
                $result = "SUCCESS";
            }
        }

        $image_file = $sdf['image_file'].','.$sdf['image_file1'].','.$sdf['image_file2'];

        $returnLog = $this->app->model("return_log");
        $sdf_return_log = array(
            'order_id' => $sdf['order_id'],
            'return_id' => $sdf['return_id'],
            'op_id' => $sdf['member_id'],
            'op_name' => (!$sdf['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'behavior' => $behavior,
            'result' => $result,
            'role' => 'member',
            'log_text' => $log_text,
            'image_file' => $image_file,
        );

        $objOrderLog = app::get('b2c')->model("order_log");

        $sdf_order_log = array(
            'rel_id' => $sdf['order_id'],
            'op_id' => $sdf['member_id'],
            'op_name' => (!$sdf['member_id']) ? app::get('b2c')->_('顾客') : $arrPams['pam_account']['login_name'],
            'alttime' => time(),
            'bill_type' => 'order',
            'behavior' => 'refunds',
            'result' => $result,
            'log_text' => $log_text,
        );

        if (!$is_save)
        { 
            $log_id = $returnLog->save($sdf_return_log);
            $log_id = $objOrderLog->save($sdf_order_log);

            $msg = '数据保存失败！';
            
            return false;
        }
        $log_id = $objOrderLog->save($sdf_order_log);
        $log_id = $returnLog->save($sdf_return_log);

        return true;
    }
    
    /**
     * 得到特定的售后申请单的信息
     * @param string 售后申请单编号
     * @return array 售后的信息数组
     */
    public function get_return_product_by_return_id($return_id=0)
    {
        if (!$return_id)
            return array();
        
        $arr_data = $this->rProduct->dump($return_id);
        
        if ($arr_data)
        {
            $arr_data['product_data'] = unserialize($arr_data['product_data']);
            $arr_data['status'] = $this->arr_status[$arr_data['status']];
            $arr_data['safeguard_require'] = $this->safeguard_require[$arr_data['safeguard_require']];
            $arr_data['safeguard_type'] = $this->safeguard_type[$arr_data['safeguard_type']];
        }
        
        return $arr_data;
    }
    
	/**
	 * 下载指定的售后服务信息的附件
	 * @param string 售后主键ID
	 * @return null
	 */
    public function file_download($return_id=0,$image_file)
    {
        if ($return_id)
        {
            $rp = &$this->app->model('return_product');
			
			$is_remote = false;
            $info = $rp->dump($return_id);
            $filename = $info[$image_file];
            $obj_images = app::get('image')->model('image');
            $arr_image = $obj_images->dump($filename);
			if (strpos($arr_image['url'],'http://') === false)
				$filename = ROOT_DIR . '/' . $arr_image['url'];
			else
			{
				$is_remote = true;
				$filename = $arr_image['url'];
				$basename = substr($arr_image['url'], strrpos($arr_image['url'],'/')+1);
			}
			
            if ($filename)
            {
				if (!$is_remote)
				{
					$file = fopen($filename,"r");
					Header("Content-type: image/jpeg");
					Header("Accept-Ranges: bytes");
					Header("Accept-Length: ".filesize($filename));
					Header("Content-Disposition: attachment; filename=".basename($filename));
					echo fread($file,filesize($filename));
					fclose($file);
				}
				else
				{
					Header("Content-type: image/jpeg");
					Header("Accept-Ranges: bytes");
					//Header("Accept-Length: ".filesize($filename));
					Header("Content-Disposition: attachment; filename=".$basename);					
					$obj_base_http = kernel::single('base_httpclient');
					echo $obj_base_http->action('GET',$filename);
				}
            }
        }
    }
}