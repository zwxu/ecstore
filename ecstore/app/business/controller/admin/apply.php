<?php

class business_ctl_admin_apply extends desktop_controller{
    public $workground = 'b2c.wrokground.goods';
    
    public function __construct($app)
    {
        parent::__construct($app);
       
    }
    
    public function apply()
    {
        $this->finder('business_mdl_brand',array(
            'title'=>app::get('business')->_('品牌审核'),
            'actions'=>array(
                        ),'use_buildin_set_tag'=>true,'use_buildin_recycle'=>true,'use_buildin_filter'=>true,'use_buildin_export'=>true,
            ));
    }
    
    //品牌审核
    function verify(){

       $flag = $_GET['flag'];
       $filter['id'] = $_GET['id'];
        
       $obj_brand = app::get('business')->model('brand');
       $obrand = app::get('b2c')->model('brand');
	   $otype_brand = app::get('b2c')->model('type_brand');
       
       $orgin_data =  $obj_brand->getList('brand_name,brand_keywords,brand_url,brand_desc,brand_logo,brand_aptitude',$filter);
       $brand_name = $orgin_data[0]['brand_name'];

       if($flag == 'pass'){
            $data['status'] = 1;
            if($obj_brand->update($data,$filter)){
              //审核通过则写入brand表
               $b2c_brand_id = $obrand->getList('brand_id',array('brand_name'=>$brand_name));			  
			   if(count($b2c_brand_id) == 0){
                 
                  $orgin_data[0]['ordernum'] = 9999;
                  //--end
			      $obrand->save($orgin_data[0]);
				  $b2c_brand_id = $obrand->getList('brand_id',array('brand_name'=>$brand_name));
			   }
               $obj_brand->update($b2c_brand_id[0],$filter);

			   //申请通过的品牌绑定类型 ---start 
			   $brand_id = $b2c_brand_id[0]['brand_id'];
			   $store_cats = $obj_brand->getList('store_cat',array('brand_id'=>$brand_id));
			   $store_cat = $store_cats[0]['store_cat'];
			   $type_ids = $obj_brand->get_type_ids(array($store_cat),$type_id);

			   foreach($type_ids as $k=>$v){

					if($v){

                      $data = array();
					  $data['type_id'] = $v;
					  $data['brand_id'] = $brand_id;
					  $otype_brand->save($data);

					}

				}
			   //---end
 
               $this->splash('success', 'index.php?app=business&ctl=admin_apply&act=apply');
            }else{
               $this->splash('failed', 'back');
            }
       }else{
           $data['status'] = 2;
           $data['fail_reason'] = $_GET['fail_reason'];
            if($obj_brand->update($data,$filter)){
               $this->splash('success', 'index.php?app=business&ctl=admin_apply&act=apply');
            }else{
               $this->splash('failed', 'back');
            }
       }
    
    }

     
     public function _views(){
        $mdl_order = app::get('business')->model('brand');
        $sub_menu = array(
            0=>array('label'=>app::get('business')->_('全部'),'optional'=>false,'filter'=>array('disabled'=>'false')),
            1=>array('label'=>app::get('business')->_('审核中'),'optional'=>false,'filter'=>array('status'=>array('0'),'disabled'=>'false')),
            2=>array('label'=>app::get('business')->_('审核通过'),'optional'=>false,'filter'=>array('status'=>array('1'),'disabled'=>'false')),
            3=>array('label'=>app::get('business')->_('审核不通过'),'optional'=>false,'filter'=>array('status'=>array('2'),'disabled'=>'false')),
        );

        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array('order_refer'=>'local'),$v['filter']);
                }else{
                    $v['filter'] = array('order_refer'=>'local');
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                $show_menu[$k]['addon'] = $mdl_order->count($v['filter']);
                $show_menu[$k]['href'] = 'index.php?app=business&ctl=admin_apply&act=apply&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }
    //不通过的原因
    function no_apply($id){

        $this->pagedata['id'] = $_GET['id'];
        $this->page('admin/brand/no_apply.html');

    }

}