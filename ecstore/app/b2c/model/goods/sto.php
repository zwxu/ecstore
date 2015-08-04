<?php



class b2c_mdl_goods_sto extends dbeav_model{

    function __construct(&$app){
        $this->app = $app;
        $this->columns = array(
                        'goods_name'=>array('label'=>app::get('b2c')->_('缺货商品名称'),'width'=>200),
                        'uname'=>array('label'=>app::get('b2c')->_('会员用户名'),'width'=>200),
                        'email'=>array('label'=>'Email','width'=>100),
                        'cellphone'=>array('label'=>'手机','width'=>100),
                        'send_time'=>array('label'=>app::get('b2c')->_('通知时间'),'width'=>100,'type'=>'time'),
                        'create_time'=>array('label'=>app::get('b2c')->_('登记时间'),'width'=>100,'type'=>'time'),
                        'sto_status'=>array('label'=>app::get('b2c')->_('库存状态'),'width'=>100),
                        'send_status'=>array('label'=>app::get('b2c')->_('通知状态'),'width'=>100),
                   );

        $this->schema = array(
                'default_in_list'=>array_keys($this->columns),
                'in_list'=>array_keys($this->columns),
                'idColumn'=>'gnotify_id',
                'columns'=>&$this->columns
            );
    }

     function table_name($real=false){
         $object = $this->app->model('member_goods');
        $table_name = substr(get_class($object),strlen($this->app->app_id)+5);
        if($real){
            return kernel::database()->prefix.$this->app->app_id.'_'.$table_name;
        }else{
            return $table_name;
        }
    }

    function get_schema(){
        return $this->schema;
    }

    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        if($filter && !isset($filter['status'])){
            $object = $this->app->model('member_goods');
            $data = $object->getList($cols, $filter, $offset, $limit, $orderby);
        }
        else
            $data = $this->get_sto_goods($filter['status']);
        return $data;
    }

    function count($filter=null){
        return count($this->getList());
    }

    function delete($filter){
        $object = $this->app->model('member_goods');
        $object->delete($filter);
    }

    function save(&$data,$mustUpdate = null){
        $object = $this->app->model('member_goods');
       return $object->save($data,$mustUpdate);
    }

    function dump($filter,$field = '*',$subSdf = null){
        $object = $this->app->model('member_goods');
        return $object->dump($filter,$field,$subSdf);
    }
    ##获取缺货登记货品列表
    public function get_sto_goods($filter){
        $obj_product = $this->app->model('products');
        $obj_goods = $this->app->model('goods');
        $member = $this->app->model('members');
        $member_goods = $this->app->model('member_goods');
        if($filter){
            $data = $member_goods->getList('*',array('type'=>'sto','object_type' => 'goods','status'=>$filter));
        }else{
            $data = $member_goods->getList('*',array('type'=>'sto','object_type' => 'goods'));
        }

        $result = array();
        foreach($data as $sto_product ){
            if($sto_product['status'] =='ready'){
                $send_status = app::get('b2c')->_('未通知');
            }
            if($sto_product['status'] =='send'){
                $send_status = app::get('b2c')->_('已通知');
            }
            $pam = $member->dump($sto_product['member_id'],"*",array(':account@pam'=>array('*')));
            if($pam){
                $uname = $pam['pam_account']['login_name'];
            }
            else{
                $uname = app::get('b2c')->_("非会员顾客");
            }
            $sdf = $obj_product->getList('*',array('product_id' =>$sto_product['product_id']));
            $aGoods  = $obj_goods->getList('store,name',array('goods_id' =>$sdf[0]['goods_id']));

            if(empty($sdf[0]['name'])){
                $goods_name = $aGoods[0]['name'];
            }else{
                $goods_name = $sdf[0]['name'];
            }
            $product_bn = $sdf[0]['bn'];
            $product_store = $sdf[0]['store'] - $sdf[0]['freez'];
            if($aGoods[0]['store']>0.00){
                if($product_store>0.00){
                    $sto_status = app::get('b2c')->_('已到货');
                }
                else{
                    $sto_status = app::get('b2c')->_('缺货中，请紧急备货');
                }
            }
            else{
                $sto_status = app::get('b2c')->_('缺货中，请紧急备货');
            }
            $result[] = array('gnotify_id'=>$sto_product['gnotify_id'],'goods_name'=>$goods_name,'sto_total'=>$total,'uname'=>$uname,'email'=>$sto_product['email'],'cellphone'=>$sto_product['cellphone'],'send_time'=>$sto_product['send_time'],'create_time'=>$sto_product['create_time'],'sto_status'=>$sto_status,'send_status'=> $send_status);
        }
        return $result;
    }


}
