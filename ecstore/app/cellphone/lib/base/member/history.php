<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class cellphone_base_member_history extends cellphone_cellphone{
    public function __construct($app){
        parent::__construct();
        $this->app = $app;
    }

    //浏览的店铺
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


        $obj_view_history = &app::get('business')->model('store_view_history');
        $aData = $obj_view_history->get_view_history($member_id,$nPage,$pagelimit);



        if(!$aData){
            $this->send(true,null,app::get('b2c')->_('无浏览过的店铺'));
        }

        $imageDefault = app::get('cellphone')->getConf('image.set');

        $aStore = $aData['data'];
        $oImage = app::get('image')->model('image');

        $objStoremanager = &app::get('business')->model('storemanger');
        $objgoods=&app::get('b2c')->model('goods');


        foreach($aStore as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                //$aStore[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }

            $aStore[$k]['image_default_id'] =$this->get_img_url($aStore[$k]['image_default_id'],
                                                                   $picSize);

            $aStore[$k]['image'] =$this->get_img_url($aStore[$k]['image'],
                                                                    $picSize);

            //收藏人气
            $ostore=$objStoremanager->getList('fav_count',array('store_id'=>$v['store_id']));

            if($ostore[0]){
                $aStore[$k]['fav_count'] =$ostore[0]['fav_count'];
            }

            //相关商品
            $oGoods=$objgoods->getList('goods_id',array('store_id'=>$v['store_id']));
            if($oGoods){
                $aStore[$k]['goods_count'] =count($oGoods);
            }

        }

         //整理返回值数组
        $return = array();
        if(is_array($aStore)){
             $return=array_values($aStore);
        }

        //print_r('<pre>');print_r($return);print_r('</pre>');exit;


        $this->send(true,$return, app::get('b2c')->_('浏览过的店铺'));

    }

    //浏览的商品
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

        $obj_view_history = &app::get('b2c')->model('goods_view_history');
        $aData = $obj_view_history->get_view_history($member_id,$member['member_lv'],$nPage,$pagelimit);

        if(!$aData){
            $this->send(true,null,app::get('b2c')->_('无商品浏览记录'));
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
        $objStoremanager = &app::get('business')->model('storemanger');
        $objgoods=&app::get('b2c')->model('goods');

        foreach($aProduct as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                //$aProduct[$k]['image_default_id'] = $imageDefault['CS']['default_image'];
            }

            if(!$oImage->getList("image_id",array('image_id'=>$v['thumbnail_pic']))) {
                //$aProduct[$k]['thumbnail_pic'] = $imageDefault['CS']['default_image'];
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
                  $aProduct[$k]['create_time'],
                  $aProduct[$k]['bn']
                  );


            $oGoods=$objgoods->getList('store_id,buy_m_count,mktprice,freight_bear',array('goods_id'=>$v['goods_id']));
            if($oGoods[0]){
                //月销量
                $aProduct[$k]['buy_m_count']=$oGoods[0]['buy_m_count'];

                $aProduct[$k]['mktprice']=$oGoods[0]['mktprice'];

                //包邮
                if($oGoods[0]['freight_bear'] !='member'){
                    $aProduct[$k]['freight_bear']='true';
                }else{
                    $aProduct[$k]['freight_bear']='false';
                }

                //店铺名称
                $ostore=$objStoremanager->getList('store_name,area',array('store_id'=>$oGoods[0]['store_id']));
                if($ostore[0]){
                  $aProduct[$k]['store_name'] =$ostore[0]['store_name'];
                   //地址
                    $str=substr($ostore[0]['area'],strpos($ostore[0]['area'],":")+1,
                        strpos($ostore[0]['area'],"/")-strpos($ostore[0]['area'],":")-1);
                    $aProduct[$k]['area'] =$str;
                }

            }

        }

         //整理返回值数组
        $return = array();
        if(is_array($aProduct)){
             $return=array_values($aProduct);
        }

        //print_r('<pre>');print_r($return);print_r('</pre>');exit;

        $this->send(true,$return, app::get('b2c')->_('商品浏览历史纪录'));
    }

    //添加浏览商品
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

        $obj_view_history = &app :: get('b2c')->model('goods_view_history');
        if (!$obj_view_history->add_history($member_id,$goods_id)){
            $this->send(false,null,'添加浏览商品失败');
        }else{
            $this->send(true,array('goods_id'=>$goods_id),'添加浏览商品成功');
        }

    }

    //删除浏览商品
    public function delgoods(){

        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'goods_id'=>'商品ID'
        );
        $this->check_params($must_params);

        $goods_id=$params['goods_id'];

        //$member_id=$params['member_id'];
        //$obj_members = &app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }

        $obj_view_history = &app :: get('b2c')->model('goods_view_history');
        if(!$obj_view_history->del_history($member_id,$goods_id)){
            $this->send(false,null,'删除浏览商品失败');
        }else{
            $this->send(true,array('goods_id'=>$goods_id),'删除浏览商品成功');
        }
    }
    //添加浏览店铺
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

        $obj_view_history = &app :: get('business')->model('store_view_history');
        if (!$obj_view_history->add_history($member_id,$store_id)){
            $this->send(false,null,app::get('b2c')->_('添加浏览的店铺失败'));
        }else{
            $this->send(true,array('store_id'=>$store_id),app::get('b2c')->_('添加浏览的店铺成功'));                header('Content-Type:text/jcmd; charset=utf-8');
        }

    }

    //删除浏览店铺
    public function delstore(){
        $params = $this->params;

        //检查应用级必填参数
        $must_params = array(
            'session'=>'会员ID',
            'store_id'=>'店铺ID'
        );
        $this->check_params($must_params);

        //删除指定的浏览店铺
        $store_id=$params['store_id'];

        //$member_id=$params['member_id'];
        //$obj_members = app :: get('b2c') -> model('members');
        //$member=$obj_members->get_member_info($member_id);
        $member=$this->get_current_member();
        $member_id=$member['member_id'];

        if(!$member){
            $this->send(false,null,app::get('b2c')->_('该会员不存在'));
        }

        $obj_view_history = &app :: get('business')->model('store_view_history');
        if (!$obj_view_history->del_history($member_id,$store_id)){
            $this->send(false,null,'删除浏览的店铺失败');
        }else{
            $this->send(true,array('store_id'=>$store_id),'删除浏览的店铺成功');
        }

    }

}