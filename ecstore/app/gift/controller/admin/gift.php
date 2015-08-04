<?php

 
//*******************************************************************
//  赠品控制器
//  $ 2010-04-07 16:27 $
//*******************************************************************
class gift_ctl_admin_gift extends desktop_controller{

    public $workground = 'gift_ctl_admin_gift';
    
    
    
    public function index(){
         $this->finder('gift_mdl_ref',array(
            'title'=>app::get('gift')->_('赠品'),
            'actions'=>array(
                            array('label'=>app::get('gift')->_('添加赠品'),'icon'=>'add.gif','href'=>'index.php?app=gift&ctl=admin_gift&act=add', 'target'=>"_blank"),
                        ),//'finder_aliasname'=>'gift_mdl_goods','finder_cols'=>'cat_id',
                        'object_method' => array('count'=>'count_finder','getlist'=>'get_list_finder'),
            ));
    }


    /**
     * 添加新规则
     */
    public function add() {
        $this->get_subcat_list();
        
        //////////////////////////// 会员等级 //////////////////////////////
        $oMemberLevel = &$this->app->model('member_lv');
        $this->pagedata['member_level'] = $oMemberLevel->getList('*', array(), 0, 10000, 'member_lv_id ASC');
        
        $this->pagedata['image_dir'] = &app::get('image')->res_url;
        $this->pagedata['return_url'] = app::get('desktop')->router()->gen_url(array('app'=>'gift', 'ctl'=>'admin_gift', 'act'=>'get_goods_info'));
        $this->pagedata['callback_ajax_url'] = app::get('desktop')->router()->gen_url(array('app'=>'gift', 'ctl'=>'admin_gift', 'act'=>'get_goods_spec'));
		$this->pagedata['gift_filter'] = array('marketable'=>'true');
        $this->singlepage('admin/gift/add.html');
    }
    
    
    public function get_goods_info () {
        $data = $_POST['data'];
        $arr = $this->app->model('goods')->dump_b2c( array('goods_id'=>$data[0]) );
        echo json_encode( array('name'=>$arr['name'],'bn'=>$arr['bn'],'store'=>$arr['store'],'goods_id'=>$arr['goods_id'],'image'=>$arr['image_default_id'], 'brief'=>$arr['brief']) );
    }
    public function get_goods_spec () {
        $id = $_POST['id'];
        $arr = kernel::single(get_parent_class($this->app->model('products')))->getList( '*',array('goods_id'=>$id) );
        $this->pagedata['specs'] = $arr;
        if( count($arr)==1 )
            $this->pagedata['is_only_spec'] = 'true';
        $this->display( 'admin/gift/spec/spec.html' );
    }
    

    /**
     * 修改规则
     *
     * @param int $rule_id
     */
    public function edit() {
        if(($id=$_GET['gift_id'])) {
            $arr_info = $this->app->model('goods')->dump($id,'*','default');
            
            
            //后台编辑 无商品信息时提示数据错误
            if( !$arr_info ) {
                exit('操作失败！相关商品信息为空！数据异常！！');
            } else {
                
                $this->pagedata['goods'] = $arr_info;
                $this->pagedata['gift'] = $arr_info['gift'];
                foreach( (array)$arr_info['product'] as $row ) {
                    if( isset($row['gift']) && !empty($row['gift']) ) {
                        $default_in_gift[] = $row['product_id'];
                    }
                }
                $this->pagedata['default_in_gift'] = $default_in_gift;
                #print_r($default_in_gift);
                #$arr_member_lv_info = $this->app->model('member_ref')->getList('*', array('goods_id'=>$id));
                #$arr_member_lv_info = $arr_member_lv_info[0];
                #if(!empty($arr_member_lv_info['member_lv_ids'])) {
                #    $arr_member_lv_info['member_lv_ids'] = explode(',', $arr_member_lv_info['member_lv_ids']);
                #}
                #$this->pagedata['memberlv'] = $arr_member_lv_info;
                //print_r($this->pagedata);exit;
                $arr = $this->app->model('products')->getList( '*',array('goods_id'=>$arr_info['goods_id'],'marketable'=>'true') );
                $this->pagedata['specs'] = $arr;
                
                $this->add();
            }
        } else {
            exit('操作失败！相关商品信息为空！赠品id不能为空！');
        }
    }
    
    
    public function get_subcat_list() {
        $objCat = &$this->app->model('cat');
        $cat_path = $objCat->getList();
        $this->pagedata['cat_path'] = $cat_path;
    }

    public function toAdd() {
        $aData = $this->_prepareGoodsData($_POST);

        $obj = $this->app->model("goods");

        foreach( $aData as $key => $val ) {
            if( $val=='' ) unset($aData[$key]);
        }
        
        if( $aData['goods_type']=='gift' ) {
            $flag = $obj->save($aData);
        } else {
            $o = $this->app->model('ref');
            
            if( !isset($aData['old_bn']) ) {
                $o->delete2delete( array('goods_id'=>$aData['goods_id']) );
            }
            
            $obj_check_order = kernel::single('b2c_order_checkorder');
            
            
            foreach( $aData['product'] as $row ) {
                if( $row['gift'] && is_array($row['gift']) ) {
                    //验证是否可以删除
                    if( count($row['gift'])<=1 ) {
                        $_filter = array('gift');
                        if(!$obj_check_order->check_order_product(array('goods_id'=>$aData['goods_id'],'product_id'=>$row['product_id']),$msg,$_filter) ) {
                            $this->begin(  );
                            $this->end( false,'货号为 '.$row['bn'].' 的赠品不能删除！<BR>有未完成的订单购买了此赠品' );
                        }
                        
                        $o->delete2delete( array('product_id'=>$row['product_id']) );
                        continue;
                    }
                        
                    $gift = $row['gift'];
                    
                    if( !$aData['goods_id'] || !$row['bn'] ) continue;
                
                    $gift['bn'] = $row['bn'];
                    $gift['product_id'] = $row['product_id'];
                    $gift['max_limit'] = $row['max_limit'];
                    if( $gift['max_limit']==='0' ) {
                        $this->begin(  );
                        $this->end( false,'货品配额不能为0！' );
                    }
                    if( !$gift['max_limit'] ) $gift['max_limit'] = null;
                    
                    $gift['goods_id'] = $aData['goods_id'];
                    if( !$gift['goods_id'] ) continue;
                    !empty($gift['order']) or $gift['order'] = 0;
                    $flag = $o->save2save( $gift );
                    if( !$flag ) break;
                }
            }
        }

        header('Content-Type:text/jcmd; charset=utf-8');
        echo '{success:"'. ($flag ?  app::get('gift')->_('成功: 操作成功!') : app::get('gift')->_('失败: 操作失败！')) .'",_:null,goods_id:"'.$aData['goods_id'].'"}';
    }
    
    function _prepareGoodsData( &$data ){
        $goods = $data['goods'];
        $gift = $data['gift'];
        if( !$gift['order'] )  $gift['order'] = 50;
        
        
        if( $data['__type']=='add' ) {
            if( $goods['goods_id'] ) {
                $count = $this->app->model('ref')->count( array('goods_id'=>$goods['goods_id']) );
                if( $count ) {
                    $this->begin(  );
                    $this->end(false,app::get('gift')->_('该商品在赠品列表中已经存在！'));
                }
            }
        }
        
        $goods['image_default_id'] = $data['image_default'];
        $images = array();
        foreach( (array)$goods['images'] as $imageId ){
            $images[] = array(
                'target_type'=>'goods',
                'image_id'=>$imageId,
                );
        }
        $goods['images'] = $images;
        
        unset($images);
        
        $goods['type']['type_id'] = 1;

        
        
        $goods['image_default_id'] = $data['image_default'];
        
        
        // 开始时间&结束时间
        foreach ($data['_DTIME_'] as $val) {
            $temp['from_time'][] = $val['from_time'];
            $temp['to_time'][] = $val['to_time'];
        }
        
        $gift['from_time'] = strtotime($data['from_time'].' '. implode(':', $temp['from_time']));
        $gift['to_time'] = strtotime($data['to_time'].' '. implode(':', $temp['to_time']));

        if(!$gift['from_time'] || $gift['from_time'] == -1){
            $this->begin("index.php?app=gift&ctl=admin_gift&act=index");
            $this->end(false,app::get('gift')->_('开始时间格式不正确'));
        }
        if(!$gift['to_time'] || $gift['to_time'] == -1){
            $this->begin("index.php?app=gift&ctl=admin_gift&act=index");
            $this->end(false,app::get('gift')->_('结束时间格式不正确'));
        }
        
        if($gift['from_time'] >= $gift['to_time']){
            $this->begin("index.php?app=gift&ctl=admin_gift&act=index");
            $this->end(false,app::get('gift')->_('开始时间不能晚于或等于结束时间'));
        }

        $gift['member_lv_ids'] = ($gift['member_lv_ids'] ? implode(',', $gift['member_lv_ids']) : '');
        if( $gift['max_buy_store']=='' ) unset($gift['max_buy_store']);
        
        $goods['name'] = $gift['name'];
        
        
        if( strlen($gift['brief']) > 255 ){
            $this->begin('index.php?app=gift&ctl=admin_gift&act=index');
            $this->end(false,app::get('gift')->_('赠品介绍请不要超过255字节'));
        }
        
        if( empty($gift['max_buy_store']) ){
            $this->begin('index.php?app=gift&ctl=admin_gift&act=index');
            $this->end(false,app::get('gift')->_('每人限购数量不能未空！'));
        }
        
        $gift['consume_score'] = (int)$gift['consume_score'];
        
        if( $data['new_bn'] ) {
            $o = $this->app->model('products');
            $product = $goods['product'];
            $old_bn = $goods['old_bn'];
            
            $arr = $this->app->model('goods')->getList( '*', array('goods_id'=>$goods['goods_id']) );
            $goods = $arr[0];
            $goods['product'] = $product;
            $goods['goods_type'] = 'gift';
            if( $old_bn==$goods['bn'] ) unset( $goods['bn'] );
            
            foreach( $goods['product'] as &$val ) {
                $arr = $o->dump( $val['product_id'] );
                if( $arr['bn']==$val['bn'] ) { //bn号相同时 删除bn号。 系统自动生成
                    $unset_bn = true;
                }
                $val = array_merge( (array)$arr, $val );
                unset( $val['goods_id'] ); unset( $val['product_id'] );
                if( $unset_bn ) unset( $val['bn'] );
            }
            
            
            $this->app->model('ref')->delete2delete( array('goods_id'=>$goods['goods_id']) );
            unset($goods['goods_id']);
        }
        
        
        $oGoods = $this->app->model('goods');
        
        if( $goods['bn']  ){
            if( $oGoods->checkProductBn($goods['bn'], $goods['goods_id']) ){
                $this->begin();
                $this->end(false,app::get('gift')->_('您所填写的赠品编号已被使用，请检查！'));
            }
        }
        

        foreach( $goods['product'] as &$row ) {
            if( in_array($row['product_id'],(array)$data['to_gift']) || $goods['goods_type']=='gift' ) {
                $row['gift'] = $gift;
                if( $row['weight'] ==='' ) unset($row['weight']);
                if( $row['store'] ==='' ) $row['store']=null;
            }
            #if( $data['__type']=='add' ) {
                if( $row['max_limit']>$row['product_store']&&$row['product_store']!='' ) {
                    $this->begin(  );
                    $this->end( false,'配额不能大于库存！' );
                }
            #}
            
            if(empty($row['bn'])) continue;
            
            if($t = $oGoods->checkProductBn($row['bn'], $goods['goods_id']) ){
                $this->begin();
                $this->end(false,app::get('gift')->_('您所填写的货号已被使用，请检查！'));
            }
            if( !$row['gift']['max_limit'] ) $row['gift']['max_limit']=$row['store'];
        }
        
        
        
        unset($goods['spec']);
        return $goods;
    }
}