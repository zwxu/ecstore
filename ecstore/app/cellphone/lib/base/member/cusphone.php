
<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_cusphone extends cellphone_cellphone{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }
//  获得客服电话列表
   function getlist(){
        $params = $this->params;
      
        if($params['pagelimit']){
            $pagelimit=$params['pagelimit'];
        }else{
            $pagelimit=5;
        }

        if($params['nPage']){
            $nPage=$params['nPage'];
        }else{
            $nPage=1;
        }
        $filter = array('is_active'=>'true');
		$mobj_phone = app :: get('cellphone')->model('phone');
        $aData = $mobj_phone->getList('phone_id,phone_number,remark',$filter,$pagelimit*($nPage-1),$pagelimit);
		
        if($aData){
		
		$this->send(true,$aData,app::get('b2c')->_('客服电话'));

		}

        else{
		$this->send(true,null,app::get('b2c')->_('没有客服电话'));
		}
        
      

        

       
   
   }
}