<?php
class commenterprise_ask_setting{
    
    function __construct(&$app){
        $this->app = $app;
    }
    function get_Html(){
       $render = $this->app->render();
       $gask_type = unserialize(app::get('b2c')->getConf('gask_type'));
       $render->pagedata['gask_type'] = $gask_type;
       return $render->fetch('admin/member/gask_setting.html');
    }

    function save_setting($aData){
    	if(!$aData['gask_type_name']) return ;
    	app::get('b2c')->setConf('gask_type','');
    	$gask_type = array();
		foreach($aData['gask_type_name'] as $key => $v){
			if($v['name']){
				$_atp['type_id'] = $key;
				$_atp['name'] = $v['name'];
				$gask_type[] = $_atp;
			}
		}
		app::get('b2c')->setConf('gask_type',serialize($gask_type));
    }
}
?>