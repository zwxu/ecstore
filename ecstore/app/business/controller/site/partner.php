<?php

class business_ctl_site_partner extends business_ctl_site_member
{
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct(&$app)
  {
      parent::__construct($app);
  }
    
    //合作伙伴列表
	public function partner_manage($page=1,$link_id='',$app='',$ctl='',$act='')
    {   
        $pagelimit =8;

		$this->path[] = array('title'=>app::get('business')->_('店铺管理'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('合作伙伴管理'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $opartner = app::get('business')->model('partner');
        //根据member_id 获得 store_id
        $objBrd = app::get('business')->model('brand');
        $member_id = $this->app->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];

        $partner = $opartner->getList('*',array('store_id'=>$store_id),($page-1)*$pagelimit,$pagelimit);
        $data = $opartner->getList('*',array('store_id'=>$store_id));
        foreach($partner as &$v){

            $v['image_url'] = base_storager::image_path($v['image_url'],'s');
        
        }
        $partnerCount = count($data);

        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($partnerCount/$pagelimit),
            'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_partner', 'act'=>'partner_manage','full'=>1,'args'=>array(($tmp = time())))),
            'token'=>$tmp
            );

        $this->pagedata['data'] = $partner;
        
        if($link_id){
            $this->pagedata['link_id'] = $link_id;
            $partners = $opartner->getList('*',array('link_id'=>$link_id));
            $this->pagedata['link_name'] = $partners[0]['link_name'];
            $this->pagedata['href'] = $partners[0]['href'];
            $this->pagedata['image_url'] = $partners[0]['image_url'];
        }
        

        $this->output('business');
    }

	/*function edit($link_id){
		$opartner = app::get('business')->model('partner');
		$partner = $opartner->getList('*',array('link_id'=>$link_id));
          
		$this->pagedata['link_name'] = $partner[0]['link_name'];
		$this->pagedata['href'] = $partner[0]['href'];
		$this->pagedata['image_url'] = $partner[0]['image_url'];
		$this->pagedata['link_id'] = $link_id;

		$this -> pagedata['_PAGE_'] = 'editpartner.html';

		$this -> output('business');
	}

	function edit1(){
		$url = $this->gen_url(array('app'=>'business','ctl'=>'site_partner','act'=>'partner_manage'));
		$obj_partner = app::get('business')->model('partner');

		if($obj_partner->update($_POST,array('link_name'=>$_POST['link_name']))){
		  $this->splash('success',$url,app::get('business')->_('编辑成功'),'','',true);

		}else{
		  $this->splash('failed',$url,app::get('business')->_('编辑失败'),'','',true);
		}
	}*/

    function save(){
		$url = $this->gen_url(array('app'=>'business','ctl'=>'site_partner','act'=>'partner_manage'));
		$image = app::get('image')->model('image');
		$obj_partner = app::get('business')->model('partner');


		$objBrd = app::get('business')->model('brand');
		$member_id = $this->app->member_id;
		$sto= kernel::single("business_memberstore",$member_id);
		$store_id = $sto->storeinfo['store_id'];

		$_POST['store_id'] = $store_id;


		 if(!$_POST['link_id']){
            if($obj_partner->save($_POST)){
                $this->splash('success',$url,app::get('business')->_('添加成功'),'','',true);
            }else{
                $this->splash('failed',$url,app::get('business')->_('添加失败'),'','',true);
            }
         }else{
            if($obj_partner->update($_POST,array('link_id'=>$_POST['link_id']))){
                $this->splash('success',$url,app::get('business')->_('编辑成功'),'','',true);
            }else{
             $this->splash('failed',$url,app::get('business')->_('编辑失败'),'','',true);
            }
         }

    }

     function move(){
       
	   $url = $this->gen_url(array('app'=>'business','ctl'=>'site_partner','act'=>'partner_manage'));
       $obj_partner = app::get('business')->model('partner');
       $filter['link_id'] = $_POST['link_id'];
       if($obj_partner->delete($filter)){

        $arr=json_encode(array('status'=>'success','message'=>'删除成功！'));

      }else{

         $arr=json_encode(array('status'=>'success','message'=>'删除失败！'));

      }
        echo $arr;
    }

} 