<?php

class business_ctl_site_brand extends business_ctl_site_member{

	public function __construct(&$app)
	{
        parent::__construct($app);
        $this->cur_view = 'brand';

        $shopname = $app->getConf('system.shopname');
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->title=app::get('b2c')->_('品牌');
        $this->objMath = kernel::single("ectools_math");
        $this->action = $this->_request->get_act_name();
        if(!$this->action) $this->action = 'index';
        $this->action_view = $this->action.".html";

        //设置不读缓存 
        $GLOBALS['runtime']['nocache']=microtime();

        kernel::single('base_session')->start();
		$this->app->member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)];

		$sto = kernel::single("business_memberstore",$this->app_b2c->member_id);
        //判断是否是店员（长）
		$isshoper = $sto->isshoper;
		$isshopmember = $sto->isshopmember;

		//拦截不是店员（长）
		if($isshoper == 'true' || $isshopmember == 'true'){
			 //nothing to do
		}else{
		    $this->splash('failed', 'back', app::get('b2c')->_('您无权操作'));
		}
    }

    
    //品牌列表
	public function return_brand($page=1,$id='',$app='',$ctl='',$act='')
    {   
		$this->path[] = array('title'=>app::get('business')->_('店铺管理'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('宝贝品牌管理'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $obj_brand = app::get('business')->model('brand');
        $pagelimit =8;

		$member_id = $this->app->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];
        
        $shopinfo = $sto->storeinfo;

		 //判断是否是店员（长）
		$isshoper = $sto->isshoper;
		$isshopmember = $sto->isshopmember;

		if($isshoper == 'true'){
		    $shopinfo = $shopinfo;
		}else{
			if($isshopmember == 'true'){
			   $shopinfo = $shopinfo[0];
			}
		}

		$obj_storegrade = $this -> app_current -> model('storegrade');
		$stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));
/*		if ($stype[0]['issue_type'] >= '0') {
            $shopinfo['issue_type'] =$stype[0]['issue_type'];
            $this -> pagedata['storetype'] = array('parent_id' => '0'); 
            if ($shopinfo['store_region']) {
                    switch ($stype[0]['issue_type']) {
                        case '0':
                            $storeregion = array();
							$m = app :: get('b2c') -> model('goods_cat');
							foreach($m -> getList('*', array('parent_id' => '0')) as $item) {
								$storeregion[$item['cat_id']] = $item['cat_name'];
							}
							$shopinfo['store_region'] = $storeregion;
                            break;

                        case '2':
                            $shopinfo['store_region'] = str_replace(',', "", $shopinfo['store_region']);
                            break;
                        default:

                            $shopinfo['store_region'] = explode(",", $shopinfo['store_region']);
                            break;
                    } 
              }else{
				if($stype[0]['issue_type'] == '0') {

						$storeregion = array();
							$m = app :: get('b2c') -> model('goods_cat');

							foreach($m -> getList('*', array('parent_id' => '0')) as $item) {
								$storeregion[$item['cat_id']] = $item['cat_name'];
							}

							$shopinfo['store_region'] = $storeregion;
                    }
			  } 
        }
*/      
        if ($shopinfo['store_region']) {
            $this -> pagedata['storeregion'] = $shopinfo['store_region'];
        }
        //品牌编辑
        if($id){
            $obrand = $obj_brand->getList('*',array('id'=>$id));
            $this->pagedata['brand_name'] = $obrand[0]['brand_name'];
            $this->pagedata['brand_keywords'] = $obrand[0]['brand_keywords'];
            $this->pagedata['brand_aptitude'] = $obrand[0]['brand_aptitude'];
            $this->pagedata['brand_desc'] = $obrand[0]['brand_desc'];
            $this->pagedata['brand_url'] = $obrand[0]['brand_url'];
            $this->pagedata['store_cat'] = $obrand[0]['store_cat'];
            $this->pagedata['brand_logo'] = $obrand[0]['brand_logo'];
            $this->pagedata['id'] = $id;
        }
        //品牌申请使用过滤条件--start
        $obrand = app::get('b2c')->model('brand');
        //$brand_use = app::get('business')->model('brand');
        $ogoods_cat = app::get('b2c')->model('goods_cat');
       //store_id
        $sto= kernel::single("business_memberstore",$this->app->member_id);
        $store_id = $sto->storeinfo['store_id'];
        $business_brand = $obj_brand->getList('*',array('status'=>'1','store_id'=>$store_id));

        $sto = kernel::single("business_memberstore",$this->app_b2c->member_id);
        $shopinfo = $sto->storeinfo;

        //判断是否是店员（长）
        $isshoper = $sto->isshoper;
        $isshopmember = $sto->isshopmember;

        if($isshoper == 'true'){
            $shopinfo = $shopinfo;
        }else{
            if($isshopmember == 'true'){
                $shopinfo = $shopinfo[0];
            }
        }

        $obj_storegrade = $this -> app_current -> model('storegrade');
        $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));

       if($stype[0]['issue_type'] == '2'){

        $store_cat = $obj_brand->getStoreregion($store_id);
		$type_id = $ogoods_cat->getList('type_id',$store_cat);
		$region_brand = $obj_brand->getBrndsByCtd(array($store_cat),$type_id[0]['type_id']);

        }

        if($stype[0]['issue_type'] == '0'){
            $region_brands = $obrand->getList('brand_id');
            foreach($region_brands as $k=>$v){
                $region_brand[] = $v['brand_id'];
            }
        }
        //已经使用过的品牌
        if($business_brand){
            foreach($business_brand as $k=>$v){
                $has_used_brand[$k] = $v['brand_id'];
            }
        }

       //过滤使用的品牌
//       if($business_brand){
//
//           foreach($region_brand as $k=>$v){ 
//             foreach($business_brand as $v1){
//                  if($v1['brand_id'] ==  $v){
//                      unset($region_brand[$k]);
//                  }
//             }
//            }
//       
//       }
       //店铺类型
//       $issue_type = $stype[0]['issue_type'];
//       $this->pagedata['issue_type'] = $issue_type;
        //店铺类型
       //$this->pagedata['goodslink_filter']['brand_id|notin'] = $has_used_brand;
       //修改过滤条件 --start
       if($has_used_brand){
           $has_used_brand = implode(',',$has_used_brand);
           $this->pagedata['goodslink_filter']['filter_sql'] = " brand_id not in (".$has_used_brand.")";
       }
       //--end

        //品牌申请使用过滤条件--end
        $filter = array(
			'store_id'=>$store_id,
        );
        
        $data = $obj_brand->getList('*',$filter,($page-1)*$pagelimit,$pagelimit,'id DESC');
        foreach($data as &$v){
	      $v['brand_logo'] = base_storager::image_path($v['brand_logo'],'s');
          $v['brand_aptitude'] = base_storager::image_path($v['brand_aptitude'],'s');
          
          if($v['brand_id']){
            $brand_name = $obrand->getList('brand_name,brand_aptitude',array('brand_id'=>$v['brand_id']));
            $v['brand_name'] = $brand_name[0]['brand_name'];
            $v['brand_aptitude'] = $brand_name[0]['brand_aptitude'];
          }
          
	   }
       $this->pagedata['data'] = $data;
	   $data1 = $obj_brand->getList('*',$filter);
       $brandCount = count($data1);
       $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($brandCount/$pagelimit),
            'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_brand', 'act'=>'return_brand','args'=>array(($tmp = time())))),
            'token'=>$tmp
            );

      $imageDefault = app::get('image')->getConf('image.set');
      $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
      $this->output();
    }
    
    /**
     * 新品牌申请
     * @params 
     * @return 
     */
    function brand_apply(){
	  
	    $obj_brand = app::get('business')->model('brand');

		$sto = kernel::single("business_memberstore",$this->app_b2c->member_id);
		$shopinfo = $sto->storeinfo;

		 //判断是否是店员（长）
		$isshoper = $sto->isshoper;
		$isshopmember = $sto->isshopmember;

		if($isshoper == 'true'){
		    $shopinfo = $shopinfo;
		}else{
			if($isshopmember == 'true'){
			   $shopinfo = $shopinfo[0];
			}
		}

		$obj_storegrade = $this -> app_current -> model('storegrade');
		$stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));
		if ($stype[0]['issue_type'] >= '0') {
            $shopinfo['issue_type'] =$stype[0]['issue_type'];
            $this -> pagedata['storetype'] = array('parent_id' => '0'); 
            if ($shopinfo['store_region']) {
                    switch ($stype[0]['issue_type']) {
                        case '0':
						    $storeregion = array();
							$m = app :: get('b2c') -> model('goods_cat');
							foreach($m -> getList('*', array('parent_id' => '0')) as $item) {
								$storeregion[$item['cat_id']] = $item['cat_name'];
							}
							$shopinfo['store_region'] = $storeregion;
                            break;

                        case '2':
                            $shopinfo['store_region'] = str_replace(',', "", $shopinfo['store_region']);
                            break;
                        default:

                            $shopinfo['store_region'] = explode(",", $shopinfo['store_region']);
                            break;
                    } 
              }else{
				 if($stype[0]['issue_type'] == '0') {

						    $storeregion = array();
							$m = app :: get('b2c') -> model('goods_cat');

							foreach($m -> getList('*', array('parent_id' => '0')) as $item) {
								$storeregion[$item['cat_id']] = $item['cat_name'];
							}

							$shopinfo['store_region'] = $storeregion;
                    }
			  } 
        }

	   $this -> pagedata['storeregion'] = $shopinfo['store_region'];

	  if($_GET['id']){
	      $obrand = $obj_brand->getList('*',$_GET);
          
          $this->pagedata['brand_name'] = $obrand[0]['brand_name'];
		  $this->pagedata['brand_desc'] = $obrand[0]['brand_desc'];
		  $this->pagedata['brand_url'] = $obrand[0]['brand_url'];
		  $this->pagedata['brand_logo'] = $obrand[0]['brand_logo'];
          $this->pagedata['edit'] = '1';
          $this->pagedata['id'] = $_GET['id'];
	  }
       
       $this->page('site/brand/brand_apply.html',true,'business');
    }

     /**
     * 删除品牌申请
     * @params string  id
     * @return  成功与否
     */
	 function brand_move(){
      
	   $url = $this->gen_url(array('app'=>'business','ctl'=>'site_brand','act'=>'return_brand'));
       $obj_brand = app::get('business')->model('brand');
       $filter['id'] = $_POST['id'];
       if($obj_brand->delete($filter)){

        $arr=json_encode(array('status'=>'success','message'=>'已删除'));

      }else{

         $arr=json_encode(array('status'=>'success','message'=>'删除失败'));

      }
      echo $arr;
    }
    
    /**
     * 保存品牌申请
     * @params 
     * @return 成功与否
     */
    function save(){
      $image = app::get('image')->model('image');

      $url = $this->gen_url(array('app'=>'business','ctl'=>'site_brand','act'=>'return_brand'));
      $obj_brand = app::get('business')->model('brand');
      //store_id
      $sto= kernel::single("business_memberstore",$this->app->member_id);
      $store_id = $sto->storeinfo['store_id'];
      $_POST['store_id'] = $store_id;
      //
      $shopinfo = $sto->storeinfo;

		 //判断是否是店员（长）
	  $isshoper = $sto->isshoper;
	  $isshopmember = $sto->isshopmember;

      if($isshoper == 'true'){
		    $shopinfo = $shopinfo;
	  }else{
			if($isshopmember == 'true'){
			   $shopinfo = $shopinfo[0];
			}
	  }

	  $obj_storegrade = $this -> app_current -> model('storegrade');
	  $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));
      $issue_type = $stype[0]['issue_type'];
//      if($issue_type == 0 || $issue_type == 2){
          //判断品牌名称存在
          if(!$_POST['id'] && $this->name_exsit($_POST['brand_name']))
            {
                 $this->pagedata['message'] = app::get('business')->_('品牌名称已经存在！');
                 $this->page('site/brand/success.html',true,'business');
            }

          if(!$_POST['id']){
             
             if($obj_brand->save($_POST)){
                 $this->splash('success',$url,app::get('business')->_('请等待管理员审核！'));
              }else{
                  $this->splash('failed',$url,app::get('business')->_('添加申请失败！'));
              }  
                
            }else{
                  $_POST['status'] = '0';
                  $_POST['fail_reason'] = '';
                  if($obj_brand->update($_POST,array('id'=>$_POST['id']))){
                      $this->splash('success',$url,app::get('business')->_('编辑成功，请等待管理员审核！'),'','',false);

                  }else{
                      $this->splash('failed',$url,app::get('business')->_('编辑失败！'),'','',false);
                  }
            }
//       }else{
//            $this->splash('failed',$url,app::get('business')->_('非法操作！'),'','',false);
//       }
    }
    
    /**
     * 使用申请
     * @return boolean 成功与否
     */
    function use_apply(){
     

       $obrand = app::get('b2c')->model('brand');
	   $brand_use = app::get('business')->model('brand');
	   $ogoods_cat = app::get('b2c')->model('goods_cat');
       //store_id
       $sto= kernel::single("business_memberstore",$this->app->member_id);
       $store_id = $sto->storeinfo['store_id'];
       $business_brand = $brand_use->getList('*',array('type'=>'1','store_id'=>$store_id));

	   $sto = kernel::single("business_memberstore",$this->app_b2c->member_id);
	   $shopinfo = $sto->storeinfo;

		 //判断是否是店员（长）
	   $isshoper = $sto->isshoper;
	   $isshopmember = $sto->isshopmember;

	   if($isshoper == 'true'){
		   $shopinfo = $shopinfo;
	   }else{
		   if($isshopmember == 'true'){
		   $shopinfo = $shopinfo[0];
		   }
	   }

	   $obj_storegrade = $this -> app_current -> model('storegrade');
	   $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));

       if($stype[0]['issue_type'] == '2'){

		$store_cat = $brand_use->getStoreregion($store_id);
		$type_id = $ogoods_cat->getList('type_id',$store_cat);
		$region_brand = $brand_use->getBrndsByCtd(array($store_cat),$type_id[0]['type_id']);

	   }

	   if($stype[0]['issue_type'] == '0'){
           $region_brands = $obrand->getList('brand_id');
		   foreach($region_brands as $k=>$v){
		       $region_brand[] = $v['brand_id'];
		   }
	   }
	   
       //过滤使用的品牌
       if($business_brand){

           foreach($region_brand as $k=>$v){ 
             foreach($business_brand as $v1){
                  if($v1['brand_id'] ==  $v){
                      unset($region_brand[$k]);
                  }
             }
            }
       
       }
	   
       foreach($region_brand as $k=>$v){
	        $brand_name = $obrand->getList('brand_name',array('brand_id'=>$v));
            $region[$k]['brand_id'] = $v;
			$region[$k]['brand_name'] = $brand_name[0]['brand_name'];
	   }
	   
       $this->pagedata['data'] = $region;
	   $this->page('site/brand/use_apply.html',true,'business');
       

    }

	function applyStatus(){
       $url = $this->gen_url(array('app'=>'business','ctl'=>'site_brand','act'=>'return_brand'));
	   $obrand = app::get('b2c')->model('brand');
       $brand_use = app::get('business')->model('brand');

       $member_id = $this->app->member_id;

       $sto= kernel::single("business_memberstore",$member_id);
       $store_id = $sto->storeinfo['store_id'];

       $shopinfo = $sto->storeinfo;

		 //判断是否是店员（长）
	  $isshoper = $sto->isshoper;
	  $isshopmember = $sto->isshopmember;

      if($isshoper == 'true'){
		    $shopinfo = $shopinfo;
	  }else{
			if($isshopmember == 'true'){
			   $shopinfo = $shopinfo[0];
			}
	  }

	  $obj_storegrade = $this -> app_current -> model('storegrade');
	  $stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));
      $issue_type = $stype[0]['issue_type'];
 //     if($issue_type == 0 || $issue_type == 2){
           foreach($_POST['linkid'] as $key=>$value){
               $filter['brand_id'] = $value;
               $brand_name = $obrand->getList('brand_id,brand_name,brand_url,brand_desc,brand_logo',$filter);
               $brand_name = $brand_name[0];
               
               $brand_temp = $brand_use->getList('brand_keywords,brand_aptitude',array('brand_id'=>$value));
               $brand_name['brand_keywords'] = $brand_temp[0]['brand_keywords'];
               $brand_name['brand_aptitude'] = $brand_temp[0]['brand_aptitude'];
              
               $brand_name['store_id'] = $store_id;
               $brand_name['status'] = '0';
               $brand_name['type'] = '1';
               $rs = $brand_use->save($brand_name);
           }

           if($rs){
                $this->splash('success',$url,app::get('business')->_('请等待管理员审核！'),'','',true);
           }else{
                $this->splash('failed',$url,app::get('business')->_('使用申请失败！'),'','',true);
           }
//        }else{
//                $this->splash('failed',$url,app::get('business')->_('非法操作！'),'','',true);
//        }
	
	}

     //检查品牌名称是否可用
     function namecheck(){
        $brand_name = trim($_POST['brand_name']);

        if($brand_name == ''){
            echo '<span class="font-red">&nbsp;'.app::get('business')->_('品牌名称不能为空！').'</span>';
            exit;
        }

        if($this->name_exsit($brand_name))
        {
            echo '<span class="font-red">&nbsp;'.app::get('business')->_('该品牌名称已存在！').'</span>';
            exit;
        }else{
            echo '<span class="font-green">&nbsp;'.app::get('business')->_('可以使用').'</span>';
            exit;
        }


        
    }

    //检查品牌名称是否存在
    function name_exsit($brand_name){
       
       $obrand = app::get('b2c')->model('brand');
	    $brand_use = app::get('business')->model('brand');
       
        $b2c_brand = $obrand->getList('brand_name');
        $business_brand = $brand_use->getList('brand_name',array('status'=>1));
        
        $all_brand_name = array_merge($b2c_brand,$business_brand);

        foreach($all_brand_name as $v){
              $name[] = $v['brand_name'];
        }

        if(in_array($brand_name,$name)) {
           return true;
        }else{
           return false;
        }
    }

    function brand_edit($id){
        $obj_brand = app::get('business')->model('brand');
        $obrand = $obj_brand->getList('*',array('id'=>$id));

        $member_id = $this->app->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];
        
        $shopinfo = $sto->storeinfo;
		$isshoper = $sto->isshoper;
		$isshopmember = $sto->isshopmember;

		if($isshoper == 'true'){
		    $shopinfo = $shopinfo;
		}else{
			if($isshopmember == 'true'){
			   $shopinfo = $shopinfo[0];
			}
		}

		$obj_storegrade = $this -> app_current -> model('storegrade');
		$stype = $obj_storegrade -> getList('*', array('grade_id' => $shopinfo['store_grade']));
		if ($stype[0]['issue_type'] >= '0') {
            $shopinfo['issue_type'] =$stype[0]['issue_type'];
            $this -> pagedata['storetype'] = array('parent_id' => '0'); 
            if ($shopinfo['store_region']) {
                    switch ($stype[0]['issue_type']) {
                        case '0':
						    $storeregion = array();
							$m = app :: get('b2c') -> model('goods_cat');
							foreach($m -> getList('*', array('parent_id' => '0')) as $item) {
								$storeregion[$item['cat_id']] = $item['cat_name'];
							}
							$shopinfo['store_region'] = $storeregion;
                            break;

                        case '2':
                            $shopinfo['store_region'] = str_replace(',', "", $shopinfo['store_region']);
                            break;
                        default:

                            $shopinfo['store_region'] = explode(",", $shopinfo['store_region']);
                            break;
                    } 
              }else{
				 if($stype[0]['issue_type'] == '0') {

						    $storeregion = array();
							$m = app :: get('b2c') -> model('goods_cat');

							foreach($m -> getList('*', array('parent_id' => '0')) as $item) {
								$storeregion[$item['cat_id']] = $item['cat_name'];
							}

							$shopinfo['store_region'] = $storeregion;
                    }
			  } 
        }

	   $this -> pagedata['storeregion'] = $shopinfo['store_region'];

        $this->pagedata['brand_name'] = $obrand[0]['brand_name'];
        $this->pagedata['brand_desc'] = $obrand[0]['brand_desc'];
        $this->pagedata['brand_url'] = $obrand[0]['brand_url'];
        $this->pagedata['brand_logo'] = $obrand[0]['brand_logo'];
        $this->pagedata['id'] = $id;
        $this->pagedata['edit'] = '1';
        $this -> pagedata['_PAGE_'] = 'editbrand.html';
        $this->output();
    }

}