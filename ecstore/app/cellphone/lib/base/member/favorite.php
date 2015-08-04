<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_favorite extends cellphone_cellphone{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }
    
    //收藏的店铺
    public function store(){
        $params = $this->params;  

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
        
        if($params['pagelimit']){
            $pagelimit=$params['pagelimit'];
        }else{
            $pagelimit=10;
        }

        if($params['nPage']){
            $nPage=$params['nPage'];
        }else{
            $nPage=1;
        }
        
        if($params['picSize']){
            $picSize=trim($params['picSize']);        
        }else{
            $picSize='CL';
        }
        
        $aData = kernel::single('business_member_storefav')->get_favorite($member_id,$member['member_lv'],$nPage);
      
        if(!$aData){
            $this->send(true,null,app::get('b2c')->_('无收藏的店铺'));
        }
                                        
        $imageDefault = app::get('cellphone')->getConf('image.set');
        
        $aStore = $aData['data'];
        $oImage = app::get('image')->model('image');
        foreach($aStore as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aStore[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }
            
            $aStore[$k]['image_default_id'] =$this->get_img_url($aStore[$k]['image_default_id'],
                                                                   $picSize);
                                                                    
            $aStore[$k]['image'] =$this->get_img_url($aStore[$k]['image'],
                                                                    $picSize);                                                        
                  
        }
        //整理返回值数组
        $return = array();
        if(is_array($aStore)){
             $return=array_values($aStore);
             foreach($return as $pk => &$pv){
                 foreach($pv as $xpk => &$xpv){
                    if(is_array($xpv)){
                        $xpv=array_values($xpv);
                    }
                }
             }
        }
         
         
       
        //print_r('<pre>');print_r($return);print_r('</pre>');exit;
        $this->send(true,$return, app::get('b2c')->_('收藏的店铺'));
    
    }
    
    //收藏的商品
    public function goods(){
        $params = $this->params;  

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
        
        if($params['pagelimit']){
            $pagelimit=$params['pagelimit'];
        }else{
            $pagelimit=10;
        }

        if($params['nPage']){
            $nPage=$params['nPage'];
        }else{
            $nPage=1;
        }
        
        if($params['picSize']){
            $picSize=trim($params['picSize']);        
        }else{
            $picSize='CL';
        }
        
        $aData = kernel::single('b2c_member_fav')->get_favorite($member_id,
                                        $member['member_lv'],$nPage);
                                        
        if(!$aData){
            $this->send(true,null,app::get('b2c')->_('无收藏的商品'));
        }
        
        $imageDefault = app::get('cellphone')->getConf('image.set');
        
        $aProduct = $aData['data'];

        $oGoods = app::get('b2c')->model('goods');
        $oMGoods = app::get('b2c')->model('member_goods');

        foreach($aProduct as &$value){

            $goods = $oGoods->getList('bn',array('goods_id'=>$value['goods_id']));
            $value['bn'] = $goods[0]['bn'];

            $mgoods = $oMGoods->getList('create_time',array('goods_id'=>$value['goods_id'],'member_id'=>$member_id,'type'=>'fav'));
            $value['create_time'] = $mgoods[0]['create_time'];
        }
        
        
        $oImage = app::get('image')->model('image');
        foreach($aProduct as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aProduct[$k]['image_default_id'] = $imageDefault['CS']['default_image'];
            }

            if(!$oImage->getList("image_id",array('image_id'=>$v['thumbnail_pic']))) {
                $aProduct[$k]['thumbnail_pic'] = $imageDefault['CS']['default_image'];
            }
            
            $aProduct[$k]['thumbnail_pic'] =$this->get_img_url($aProduct[$k]['thumbnail_pic'],
                                                                    $picSize);
                                                                    
            $aProduct[$k]['image_default_id'] =$this->get_img_url($aProduct[$k]['image_default_id'],
                                                                    $picSize);
                                                                    
            //删除多余的返回数据
            unset($aProduct[$k]['udfimg'],
                  $aProduct[$k]['type_id'],
                  $aProduct[$k]['spec_desc_info'],            
                  $aProduct[$k]['bn'],            
                  $aProduct[$k]['bn']
                  );
        }
        
        //整理返回值数组
        $return = array();
        if(is_array($aProduct)){
             $return=array_values($aProduct);
        }
        
        
        //print_r('<pre>');print_r($return);print_r('</pre>');exit;
                                        
        $this->send(true,$return, app::get('b2c')->_('收藏的宝贝'));
    }
    
    //添加收藏商品
    public function addgoods(){
        $params = $this->params;  

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'goods_id'=>'商品ID'
        );
        $this->check_params($must_params);

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
        
        $goods_id=$params['goods_id'];
        $obj_goods = app :: get('b2c') -> model('goods');
        $goods=$obj_goods->getList('goods_id',array('goods_id'=>$goods_id,'disabled'=>'false'));
        
        if(!$goods){
            $this->send(false,null,app::get('b2c')->_('该商品不存在'));
        }
        
        
        if (!kernel::single('b2c_member_fav')->add_fav($member_id,'goods',$goods_id)){
            $this->send(false,null,'添加收藏失败');
        }else{
            $this->send(true,array('goods_id'=>$goods_id),'添加收藏成功');
        }

    }
    
    //删除收藏商品
    public function delgoods(){
    
        $params = $this->params;  

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);
        
        //删除指定的收藏商品如没有指定则删除所有
        $goods_id=$params['goods_id'];

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
    
        if(!kernel::single('b2c_member_fav')->del_fav($member_id,'goods',$goods_id,$maxPage)){
            $this->send(false,null,'删除收藏失败');
        }else{
            $this->send(true,array('goods_id'=>$goods_id),'删除收藏成功');
        }
    }
    //添加收藏店铺
    public function addstore(){
        $params = $this->params;  

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'store_id'=>'店铺ID'
        );
        $this->check_params($must_params);
        
        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
        
        $store_id=$params['store_id'];
        $obj_store= app :: get('business') -> model('storemanger');
        $stores=$obj_store->get_list_approved('store_id',array('store_id'=>$store_id));
        
        if(!$stores){
            $this->send(false,null,app::get('b2c')->_('该店铺不存在或未通过审核'));
        }
        
        if (!kernel::single('business_member_storefav')->add_fav($member_id,'stores',$store_id)){
            $this->send(false,null,app::get('b2c')->_('收藏店铺失败'));
        }else{
            $this->send(true,array('store_id'=>$store_id),app::get('b2c')->_('收藏店铺成功'));                header('Content-Type:text/jcmd; charset=utf-8');
        }
        
    }
    
    //删除收藏店铺
    public function delstore(){
        $params = $this->params;  

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID'
        );
        $this->check_params($must_params);
        
        //删除指定的收藏店铺如没有指定则删除所有
        $store_id=$params['store_id'];

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];
        
        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }
        
        if(!kernel::single('business_member_storefav')->del_fav($member_id,'stores',$store_id)){
            $this->send(false,null,'删除收藏失败');
        }else{
            $this->send(true,array('store_id'=>$store_id),'删除收藏成功');
        }
    
    }

}