<?php

 
class business_ctl_site_favorite extends b2c_ctl_site_member
{
	/**
	 * 构造方法
	 * @param object application
	 */
	public function __construct(&$app)
	{
		$this->app_current = $app;
		$this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }
	
	public function my_favorite($nPage=1)
	{
		$this->path[] = array('title'=>app::get('business')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('我的收藏'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
		
        $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$nPage);
        $imageDefault = app::get('image')->getConf('image.set');
        $aProduct = $aData['data'];

        $oGoods = app::get('b2c')->model('goods');
        $oMGoods = app::get('b2c')->model('member_goods');

        foreach($aProduct as &$value){

            $goods = $oGoods->getList('bn',array('goods_id'=>$value['goods_id']));
            $value['bn'] = $goods[0]['bn'];

            $mgoods = $oMGoods->getList('create_time',array('goods_id'=>$value['goods_id'],'member_id'=>$this->app->member_id,'type'=>'fav'));
            $value['create_time'] = $mgoods[0]['create_time'];
        }
        
        
        $oImage = app::get('image')->model('image');
        foreach($aProduct as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }

            if(!$oImage->getList("image_id",array('image_id'=>$v['thumbnail_pic']))) {
                $aProduct[$k]['thumbnail_pic'] = $imageDefault['S']['default_image'];
            }
        }
        $this->pagedata['favorite'] = $aProduct;
        $this->pagination($nPage,$aData['page'],'favorite');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        /** 接触收藏的页面地址 **/
        $this->pagedata['fav_ajax_del_goods_url'] = $this->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'ajax_del_fav','args'=>array('goods')));

        $this->output('business');
	}

    function ajax_del_fav($object_type='goods'){
        if(!$this->app->member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        }

        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$_POST['gid'],$maxPage)){
            header('Content-Type:text/jcmd; charset=utf-8');
            echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

            $current_page = $_POST['current'];
            header('Content-Type:text/jcmd; charset=utf-8');

            if ($current_page > $maxPage){
                $current_page = $maxPage;
                $reload_url = $this->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'my_favorite','args'=>array($current_page)));
                echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,data:"",reload:"'.$reload_url.'"}';exit;
        }

            $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$current_page);
            $aProduct = $aData['data'];

            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach($aProduct as $k=>$v) {
                if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
    }
        }
            $this->pagedata['favorite'] = $aProduct;
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $str_html = $this->fetch('site/member/favorite_items.html','business');
            echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,data:"'.addslashes(str_replace("\n","",str_replace("\r\n","",$str_html))).'",reload:null}';
        }
    }


    function delSel($object_type='goods'){

    if(!$this->app->member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'index'));
        }

        if (!kernel::single('b2c_member_fav')->del_fav($this->app->member_id,$object_type,$_POST['chk'],$maxPage)){
            $this->splash('failed','back',app::get('b2c')->_('删除失败！'));
        }else{
            $this->set_cookie('S[GFAV]'.'['.$this->app->member_id.']',$this->get_member_fav($this->app->member_id),false);

            $current_page = $_POST['current'];
            header('Content-Type:text/jcmd; charset=utf-8');

            if ($current_page > $maxPage){
                $current_page = $maxPage;
                $reload_url = $this->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'my_favorite','args'=>array($current_page)));
                $this->splash('success',$reload_url,app::get('b2c')->_('删除成功！'));
        }

            $aData = kernel::single('b2c_member_fav')->get_favorite($this->app->member_id,$this->member['member_lv'],$current_page);
            $aProduct = $aData['data'];

            $oImage = app::get('image')->model('image');
            $imageDefault = app::get('image')->getConf('image.set');
            foreach($aProduct as $k=>$v) {
                if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                    $aProduct[$k]['image_default_id'] = $imageDefault['S']['default_image'];
    }
        }
            $this->pagedata['favorite'] = $aProduct;
            $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
            $str_html = $this->fetch('site/member/favorite_items.html','business');
            $this->splash('success',$this->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'my_favorite')),app::get('b2c')->_('删除成功！'));
        }
    
    }

   
    function ajax_fav_store()
    {
        $object_type = $_POST['type'];
        $object_type = 'stores';
        $nSid = $_POST['gid'];
        $act_type = $_POST['act_type'];
        if($act_type == 'del'){
            if (!kernel::single('business_member_storefav')->del_fav($this->app->member_id,$object_type,$nSid)){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('删除失败！').'",_:null}';
            }else{
                $this->set_cookie('S[SFAV]'.'['.$this->app->member_id.']',$this->get_member_fav_store($this->app->member_id),false);

                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_('删除成功！').'",_:null,reload:"'.$this->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'sfavorite')).'"}';
            }
        }
        else{
            if (!kernel::single('business_member_storefav')->add_fav($this->app->member_id,$object_type,$nSid)){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.app::get('b2c')->_('添加失败！').'",_:null}';
            }else{
                $this->set_cookie('S[SFAV]'.'['.$this->app->member_id.']',$this->get_member_fav_store($this->app->member_id),false);

                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.app::get('b2c')->_('添加成功！').'",_:null,reload:"'.$this->gen_url(array('app'=>'business','ctl'=>'site_favorite','act'=>'sfavorite')).'"}';
            }
        }
    }
    
    function get_member_fav_store($member_id=null){
        $obj_member_goods = $this->app_current->model('member_stores');
        return $obj_member_goods->get_member_fav($member_id);
    }
    
    public function sfavorite($nPage=1){
        $this->path[] = array('title'=>app::get('business')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('店铺收藏'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->pagedata['controller'] = 'my_favorite';
        $aData = kernel::single('business_member_storefav')->get_favorite($this->app->member_id,$this->member['member_lv'],$nPage);
        $imageDefault = app::get('image')->getConf('image.set');
        $aStore = $aData['data'];
        $oImage = app::get('image')->model('image');
        foreach($aStore as $k=>$v) {
            if(!$oImage->getList("image_id",array('image_id'=>$v['image_default_id']))){
                $aStore[$k]['image_default_id'] = $imageDefault['S']['default_image'];
            }
        }
        $this->pagedata['favorite'] = $aStore;
        $this->pagination($nPage,$aData['page'],'sfavorite',null,'business','site_favorite');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $setting['buytarget'] = $this->app->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        $this->pagedata['current_page'] = $nPage;
        $this->output('business');
	}
    
}