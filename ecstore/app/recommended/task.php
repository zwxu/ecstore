<?php


 /**
 * 安装此APP时执行的任务
 */
class recommended_task 
{
	/**
	* 安装时执行的操作,安装初始化数据
	* @access public
	*/
    public function post_install()
    {
        kernel::log('Initial recommended');
        kernel::single('base_initial', 'recommended')->init();
    }
	
	/**
	 * 维护过程中做数据的兼容处理
	 * @access public
	 */
	public function post_update($dbinfo)
	{
		// 由于老版本的数据结构的局限性，导致情况发生变化
		$dbver = $dbinfo['dbver'];
		$app_xml = kernel::single('base_xml')->xml2array(file_get_contents(app::get('recommended')->app_dir.'/app.xml'),'base_app');
		if ($app_xml['version'] == '0.2' && $app_xml['version'] > $dbver){
			$goods = app::get('recommended')->model('goods');
			$filter = array('secondary_goods_id|has'=>',');
			$arr = $goods->getList('*',$filter);
			if ($arr){
				foreach ($arr as $_arr_goods){
					$temp = explode(',',$_arr_goods['secondary_goods_id']);
					if ($temp&&is_array($temp)){
						foreach ($temp as $_arr){
							$item = array(
								'primary_goods_id'=>$_arr_goods['primary_goods_id'],
								'secondary_goods_id'=>$_arr,
								'last_modified'=>$_arr_goods['last_modified'],
							);
							$goods->replace($item, array('primary_goods_id'=>$item['primary_goods_id'],'secondary_goods_id'=>$item['secondary_goods_id']));
						}
					}
					$goods->delete(array('primary_goods_id'=>$_arr_goods['primary_goods_id'],'secondary_goods_id'=>$_arr_goods['secondary_goods_id']));
				}
			}
			$goods_period = app::get('recommended')->model('goods_period');
			$arr = $goods_period->getList('*',$filter);
			if ($arr){
				foreach ($arr as $_arr_goods){
					$temp = explode(',',$_arr_goods['secondary_goods_id']);
					if ($temp&&is_array($temp)){
						foreach ($temp as $_arr){
							$item = array(
								'primary_goods_id'=>$_arr_goods['primary_goods_id'],
								'secondary_goods_id'=>$_arr,
								'last_modified'=>$_arr_goods['last_modified'],
							);
							$goods_period->replace($item, array('primary_goods_id'=>$item['primary_goods_id'],'secondary_goods_id'=>$item['secondary_goods_id']));
						}
					}
					$goods_period->delete(array('primary_goods_id'=>$_arr_goods['primary_goods_id'],'secondary_goods_id'=>$_arr_goods['secondary_goods_id']));
				}
			}
		}
	}
}
