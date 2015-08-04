<?php

 
class business_ctl_site_msg extends b2c_ctl_site_member
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

	/**
	 * 站内信列表显示
	 */
	public function my_msg($type='',$nPage=1)
	{
		$this->path[] = array('title'=>app::get('business')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('business')->_('站内信'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
		
        $oMsg = kernel::single('b2c_message_msg');
        //全部
        if(!$type){

            $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
            $aData['data'] = $row;
            #print_r($row);
            $aData['total'] = count($row);
            $count = count($row);
            $aPage = $this->get_start($nPage,$count);
            $params['data'] = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true'),$aPage['start'],$this->pagesize);

            $params['page'] = $aPage['maxPage'];
            $this->pagedata['message'] = $params['data'];
            $this->pagedata['total_msg'] = $aData['total'];

            $this->pagedata['pager'] = array(
                    'current'=>$nPage,
                    'total'=>$params['page'],
                    'link' =>$this->gen_url(array('app'=>'business', 'ctl'=>'site_msg','act'=>'my_msg','args'=>array($type,($tmp = time())))),
                    'token'=>$tmp,
                    );
        
        }else if($type == 1){
            //未读
            $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
            $aData['data'] = $row;
            #print_r($row);
            $aData['total'] = count($row);
            $count = count($row);
            $aPage = $this->get_start($nPage,$count);
            $params['data'] = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true','mem_read_status' => 'false'),$aPage['start'],$this->pagesize);

            $params['page'] = $aPage['maxPage'];
            $this->pagedata['message'] = $params['data'];
            $this->pagedata['total_msg'] = $aData['total'];

            $this->pagedata['pager'] = array(
                    'current'=>$nPage,
                    'total'=>$params['page'],
                    'link' =>$this->gen_url(array('app'=>'business', 'ctl'=>'site_msg','act'=>'my_msg','args'=>array($type,($tmp = time())))),
                    'token'=>$tmp,
                    );
        
        }else if($type == 2){
            //已读
            $row = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'true'));
            $aData['data'] = $row;
            #print_r($row);
            $aData['total'] = count($row);
            $count = count($row);
            $aPage = $this->get_start($nPage,$count);
            $params['data'] = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' =>'true','mem_read_status' => 'true'),$aPage['start'],$this->pagesize);

            $params['page'] = $aPage['maxPage'];
            $this->pagedata['message'] = $params['data'];
            $this->pagedata['total_msg'] = $aData['total'];

            $this->pagedata['pager'] = array(
                    'current'=>$nPage,
                    'total'=>$params['page'],
                    'link' =>$this->gen_url(array('app'=>'business', 'ctl'=>'site_msg','act'=>'my_msg','args'=>array($type,($tmp = time())))),
                    'token'=>$tmp,
                    );
        
        }

        //站内信条数显示
        $all = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true'));
        $all = count($all);

        $no_read = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'false'));
        $no_read = count($no_read);

        $had_read = $oMsg->getList('*',array('to_id' => $this->app->member_id,'has_sent' => 'true','for_comment_id' => 'all','inbox' => 'true','mem_read_status' => 'true'));
        $had_read = count($had_read);

        $this->pagedata['type'] = $type;
        $this->pagedata['all'] = $all;
        $this->pagedata['no_read'] = $no_read;
        $this->pagedata['had_read'] = $had_read;

        $this->output('business');
	}
     
     /**
	 * 删除所选站内信
	 */
     function del_in_box_msg(){
        $url = $this->gen_url(array('app'=>'business','ctl'=>'site_msg','act'=>'my_msg'));
        if(!empty($_POST['delete']))
        {
            $objMsg = kernel::single('b2c_message_msg');
            if($objMsg->check_msg($_POST['delete'],$this->member['member_id']))
            {
                if($objMsg->delete_msg($_POST['delete'],'inbox'))
                $this->splash('success',$url,app::get('b2c')->_('删除成功！'),'','',true);
                else $this->splash('failed',$url,app::get('b2c')->_('删除失败！'),'','',true);
            }
            else
            {
                $this->splash('failed',$url,app::get('b2c')->_('删除失败: 参数提交错误！！'),'','',true);
            }

        }
        else
        {
              $this->splash('failed',$url,app::get('b2c')->_('删除失败: 没有选中任何记录！！'),'','',true);
        }
    }


}