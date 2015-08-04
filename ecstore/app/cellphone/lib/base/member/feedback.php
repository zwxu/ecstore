<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_feedback extends cellphone_cellphone{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    public function add(){
		$params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'content'=>'内容',
            'contact'=>'联系方式'
        );
        $this->check_params($must_params);

        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }


		$backMod = $this->app->model('feedback');
		$data = array(
				'member_id'=>$member['member_id'],
				'content'=>$params['content'],
				'contact'=>$params['contact'],
			);
		if($backMod->save($data)){
			$this->send(true,null,app::get('b2c')->_('反馈成功'));
		}else{
			$this->send(false,null,app::get('b2c')->_('反馈失败'));
		}
	}
    public function getappinfo(){
	    $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'type'=>'类型标识',//copyright或license或description或                        version
            
        );
        $this->check_params($must_params);
		
        if($params['type']=='license'){
		$data['text'] = $this->app->getConf('cellphone.appinfo.license');
		$this->send(true,$data,app::get('cellphone')->_('软件许可协议'));
		
		}
		if($params['type']=='copyright'){
		$data['text'] = $this->app->getConf('cellphone.appinfo.copyright');
		$this->send(true,$data,app::get('cellphone')->_('版权信息'));
		}
		if($params['type']=='description'){
		$data['text'] = $this->app->getConf('cellphone.appinfo.description');
		$this->send(true,$data,app::get('cellphone')->_('说明'));
		
		}
		if($params['type']=='version'){
		$data['text'] = $this->api_version;
		$this->send(true,$data,app::get('cellphone')->_('版本号'));
		
		}
        else{
		$this->send(false,null,app::get('cellphone')->_('参数值错误'));
		
		}	
		
	
		
	
	}
    





}