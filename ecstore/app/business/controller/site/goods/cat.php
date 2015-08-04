<?php

class business_ctl_site_goods_cat extends business_ctl_site_member
{
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct(&$app)
	{
        parent::__construct($app);
        //设置不读缓存 
        $GLOBALS['runtime']['nocache']=microtime();
    }

	public function return_goodcat($type='',$custom_cat_id='',$app='',$ctl='',$act='')
    {   
        $nCatId = 0;
		$this->path[] = array('title'=>app::get('business')->_('店铺管理'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('宝贝分类管理'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

		$objCat = app::get('business')->model('goods_cat');
        $member_id = $this->app->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];
      
        $catList1 =$objCat->getList('*',array('store_id'=>$store_id,'parent_id'=>'0'),0,-1,'p_order');
        $catList2 =$objCat->getList('*',array('store_id'=>$store_id,'parent_id|noequal'=>'0'),0,-1,'p_order');

        foreach($catList1 as $v){
            $v['cls'] = 1;
            $v['step'] = 1;
            $catList_array[] = $v;
            foreach($catList2 as $v1){
                if($v['custom_cat_id'] == $v1['parent_id']){
                    $v1['step'] = 2;
                    $catList_array[] = $v1;
                }
            }
        }
        //--end
		$this->pagedata['data'] = $catList_array;
		$this->pagedata['tree_number']=count($catList_array);

		$this->_info($nCatId);
        if($type=='edit'){
            $nCatId = $custom_cat_id;
            $this->_info($nCatId,'edit');
            $this->pagedata['edit'] = 1;
        }
        if($type=='apply'){
            $nCatId = $custom_cat_id;
            $this->_info($nCatId);
            $this->pagedata['apply'] = 1;
        }

        $this->output('business');
    }

    function _info($id=0,$type='add'){

		$member_id = $this->app->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];

        $objCat = app::get('business')->model('goods_cat');
        $catList =$objCat->getList('*',array('store_id'=>$store_id,'parent_id'=>'0'));
		
        $aCatNull[] = array('custom_cat_id'=>0,'cat_name'=>app::get('business')->_('----无----'),'step'=>1);
        if(empty($catList)){
            $catList = $aCatNull;
        }else{
            $catList = array_merge($aCatNull, $catList);
        }

        $this->pagedata['catList'] = $catList;

        $aCat = $objCat->dump($id);
        $this->pagedata['cat']['parent_id'] = $aCat['custom_cat_id'];
        $this->pagedata['cat']['type_id'] = $aCat['type_id'];

        if($type == 'edit'){
            $this->pagedata['cat']['custom_cat_id'] = $aCat['custom_cat_id'];
            $this->pagedata['cat']['cat_name'] = $aCat['cat_name'];
            $this->pagedata['cat']['parent_id'] = $aCat['parent_id'];
            $this->pagedata['cat']['p_order'] = $aCat['p_order'];
        }
      
    }

	 function save(){
		$url = $this->gen_url(array('app'=>'business','ctl'=>'site_goods_cat','act'=>'return_goodcat'));
        if( $_POST['p_order'] === '' ){
            
            $_POST['p_order'] = '0';
        }

        $objCat = app::get('business')->model('goods_cat');
		$member_id = $this->app->member_id;
        $objBrd = app::get('business')->model('brand');

        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];

		$_POST['cat']['store_id'] = $store_id;
        if($objCat->save($_POST['cat']))
			$this->splash('success',$url,app::get('business')->_('操作成功'),'','',true);
        else
			$this->splash('failed',$url,app::get('business')->_('操作失败'),'','',true);
    }

	function toRemove(){
        $nCatId=$_POST['custom_cat_id'];
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_goods_cat','act'=>'return_goodcat'));

        $objCat = app::get('business')->model('goods_cat');
        //--start
        $subCats = $objCat->getList('custom_cat_id',array('parent_id'=>$nCatId));
        $CatId[0] = $nCatId;
        if(count($subCats)>0){
            foreach($subCats as $v){
                $CatId[]=$v['custom_cat_id'];
            }
        }
        //--end

        if($objCat->toRemove($CatId,$msg)){
            $arr=json_encode(array('status'=>'success','message'=>'已删除'));

        }else{
           $arr=json_encode(array('status'=>'success','message'=>$msg));
        }
        
         echo $arr;
       
    }

    function removeSelect(){
        $this->begin($this->gen_url(array('app' => 'business', 'ctl' => 'site_goods_cat', 'act' => 'return_goodcat')));
        $objCat = app::get('business')->model('goods_cat');

          if(!$objCat->toRemove($_POST['chkCustom'],$msg)){
             $this->end(false, app::get('business')->_($msg));
          }else{
             $this->end(true, app::get('business')->_("删除成功"));
          }
       
    
    }

} 