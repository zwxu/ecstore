<?php

/**
* 安装卸载时执行的任务类
*/
class operatorlogmanage_task{

	/**
	* 安装时执行的方法
	* @param array $options 安装选择后的参数
	*/
	public function post_install($options)
    {
		$rows = app::get('base')->model('apps')->getList('app_id',array('installed'=>1));
		foreach($rows as $r){
			if($r['app_id'] == 'base')  continue;
			$args[] = $r['app_id'];
		}
		
		foreach ((array)$args as $app)
		{
			$this->xml_update($app);
		}
	}
	/**
	* xml文件的更新操作
	* @param object $app app对象实例
	*/
	private function xml_update($app)
	{
		if (!$app) return;		
		
		$detector = kernel::single('operatorlogmanage_application_register');
		foreach($detector->detect($app) as $name=>$item){
			$item->install();
		}
		
	}	
}