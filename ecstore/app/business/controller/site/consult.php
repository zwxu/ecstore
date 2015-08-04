<?php

class business_ctl_site_consult extends business_ctl_site_member
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
    
    /**
	 * 店铺咨询管理列表
	 * @param string type 咨询类型
	 */
	public function consult_manage($type='',$app='',$ctl='',$act='',$page=1)
    {   
		$this->path[] = array('title'=>app::get('business')->_('店铺管理'),'link'=>$this->gen_url(array('app'=>'business', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('咨询管理'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

	    $oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();
        $obrand = app::get('business')->model('brand');

        $member_id = $this->app->member_id;
        $sto= kernel::single("business_memberstore",$member_id);
        $store_id = $sto->storeinfo['store_id'];
		$pageLimit = 20;
        if(!$type){

		   $count = $oconsult->count(array('store_id'=>$store_id,'type_id|noequal'=>'','object_type'=>'ask'));

           $consult = $oconsult->getList('*',array('store_id'=>$store_id,'type_id|noequal'=>'','object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
           $this->pagedata['type'] = $type;

        }else{

            if($type ==2){
			  $count = $oconsult->count(array('store_id'=>$store_id,'reply_name|noequal'=>'','type_id|noequal'=>'','object_type'=>'ask'));
              $consult = $oconsult->getList('*',array('store_id'=>$store_id,'reply_name|noequal'=>'','type_id|noequal'=>'','object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
              $this->pagedata['type'] = $type;

            } 
            
            if($type == 1){
			  $count = $oconsult->count(array('store_id'=>$store_id,'filter_sql'=>'reply_name is null','type_id|noequal'=>'','object_type'=>'ask'));
              $consult = $oconsult->getList('*',array('store_id'=>$store_id,'filter_sql'=>'reply_name is null','type_id|noequal'=>'','object_type'=>'ask'),$pageLimit*($page-1),$pageLimit);
              $this->pagedata['type'] = $type;
            }
        
        }
        
        $ogoods = app::get('b2c')->model('goods');
		$this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit),
            'link'=>  $this->gen_url(array('app'=>'business', 'ctl'=>'site_consult','full'=>1,'act'=>'consult_manage','args'=>array($type,$pp,$ctl,$act,($tmp=time())))),
            'token'=>time());
        foreach($consult as $key=>&$value){

            $goods = $ogoods->getList('name',array('goods_id'=>$value['type_id']));
            $value['name'] = $goods[0]['name'];
           
            $value['sub'] = $oconsult->getList('*',array('for_comment_id'=>$value['comment_id']));
        }
        
        $this->pagedata['data'] = $consult;
        $this->output('business');
    }

    /**
	 * 店主的咨询回复
	 */
    function to_reply(){

        $comment_id = $_POST['comment_id'];
        $comment = $_POST['reply_content'];
        if($comment_id&&$comment){
            $member_comments = kernel::single('b2c_message_disask');
            $sdf = $member_comments->dump($comment_id);
            if(app::get('b2c')->getConf('comment.display.ask') == 'reply'){
                $aData = $sdf;
                $aData['display'] = 'true';
                $member_comments->save($aData);
            }
            $sdf['comment_id']= '';
            $sdf['for_comment_id'] = $comment_id;
            $sdf['object_type'] = "ask";
            $sdf['to_id'] = $sdf['author_id'];
            $sdf['author_id'] = $this->app->member_id;
            $sdf['author'] = app::get('b2c')->_('店主');
            $sdf['title'] = '';
            $sdf['contact'] = '';
            $sdf['display'] = 'true';
            $sdf['lastreply'] = time();
            $sdf['time'] = time();
            $sdf['reply_name'] = app::get('b2c')->_('店主');
            $sdf['comment'] = $comment;
            if($member_comments->send($sdf,'ask')){
                 $comments = app::get('b2c')->model('member_comments');
                 $data['member_id'] = $sdf['to_id'];
                 $comments->fireEvent('gaskreply',$data,$sdf['to_id']);

                 $arr=json_encode(array('status'=>'success','message'=>'回复成功'));
                 
            }
            else{
                  
              $arr=json_encode(array('status'=>'fail','message'=>'回复失败'));
            }
        }
        else{
              
              $arr=json_encode(array('status'=>'fail','message'=>'内容不能为空'));
        }

        echo $arr;
    }

	/**
	 * 删除咨询及其回复
	 */
	function removeCR(){
        $comment_id = $_POST['comment_id'];
       
		$oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();

        $consult = $oconsult->getList('*',array('for_comment_id'=>$comment_id));
;
		if($oconsult->delete(array('comment_id'=>$comment_id))){
		       foreach($consult as $v){
			      $oconsult->delete(array('comment_id'=>$v['comment_id']));
			   }
			$arr=json_encode(array('status'=>'success','message'=>'删除成功'));
		}else{
		    $arr=json_encode(array('status'=>'fail','message'=>'删除失败'));
		}
	   echo $arr;
	}

    /**
	 * 删除选中咨询项
	 */
	function removeSelect(){

        $comment_id = $_POST['comment_id'];
        $comment_id = explode(',',$comment_id);

        $oconsult = app::get('b2c')->model('member_comments');
        $oconsult->use_meta();

		if($oconsult->delete(array('comment_id'=>$comment_id))){

            $oconsult->delete(array('for_comment_id'=>$comment_id));
			$arr=json_encode(array('status'=>'success','message'=>'删除成功'));

		}else{

		    $arr=json_encode(array('status'=>'fail','message'=>'删除失败'));

		}

	   echo $arr;
	}

} 