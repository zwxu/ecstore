<?php

 
class b2c_message_msg extends b2c_message_comment{
    
    function __construct(&$app){
         
        $this->app = $app;
        $this->type = 'msg';
        parent::__construct($app);

    }
    
      function send($aData){
          if($aData['to_type'] == 'admin'){
              $to_id = 1;
              $to_uname = app::get('b2c')->_('管理员');
          }
          else{
              $to_id = $aData['to_id'];
              $to_uname = $aData['msg_to'];
          }
          $data = array(
                    'comment_id' => $aData['comment_id']?$aData['comment_id']:'',
                    'author_id' => $aData['member_id'],
                    'author' => $aData['uname'],
                    'contact'=>htmlspecialchars($aData['contact']),
                    'object_type' => 'msg',
                    'to_id' =>  $to_id,
                    'to_uname' => $to_uname,
                    'title' =>$aData['subject'],
                    'comment' => $aData['comment'],
                    'time' => time(),
                    'ip'=>$aData['ip'],
                    'has_sent'=>$aData['has_sent'],
                        );
        if($this->save($data)){
            return true;
        }
        else{
            return false;
        }
  }
      
      function to_reply($aData){
        $sdf = $this->dump($aData['comment_id']);
        $sdf['reply_name'] = app::get('b2c')->_('管理员');
        $sdf['lastreply'] = time();
        if(!($this->save($sdf))){
            return false; 
        }   
        $sdf['comment_id']= '';
        $sdf['for_comment_id'] = $aData['comment_id'];
        $sdf['object_type'] = "msg";
        $sdf['to_id'] = $sdf['author_id'];
        $sdf['to_uname'] = $sdf['author'];
        $sdf['author_id'] = 1;
        $sdf['author'] = app::get('b2c')->_('管理员');
        $sdf['title'] = htmlspecialchars($aData['title']);
        $sdf['contact'] = '';
        $sdf['display'] = 'true';
        $sdf['lastreply'] = time();
        $sdf['time'] = time();
        $sdf['comment'] = htmlspecialchars($aData['reply_content']);
        if($this->save($sdf)){
            //回复站内信息，触发短信等事件
            $comments = $this->app->model('member_comments');
            $comments->fireEvent('membermsg',$sdf,$sdf['to_id']);
            return true;
        }
        else{
            return false;
        }
    }
    
    public function check_msg($aComment=array(),$member_id=null)
    {
        if(!$aComment && $member_id)
        {   
            $msg = app::get('b2c')->_('删除失败: 参数提交错误！！');
            return false;
        }
        foreach($aComment as $k=> $v)
        {
            $row = $this->getList('comment_id',array('comment_id' => $v, 'for_comment_id' => 'all' ,'member_id' => $member_id));
            if(!$row)
            {
                $msg = app::get('b2c')->_('对不起，您提交删除的信息不存在或您没有权限查看这条信息！');
                return false;
            }
        }
        return true;
    }
    
    public function delete_msg($comment_id=array(),$box_type='inbox')
    {
        if(!$comment_id) return false;
            foreach($comment_id as $k => $v)
            {
                if($sdf = $this->dump($v))
                {
                    if($sdf['track'] === 'false' || $sdf['inbox'] === 'false')
                    {
                        $this->delete(array('object_type' => 'msg','comment_id' => $v));
                    }
                    else
                    {
                        if($box_type == 'inbox') $sdf['inbox'] = 'false';
                        if($box_type == 'track') $sdf['track'] = 'false';
                        $this->save($sdf);
                    }
                }
                else
                {
                    return false;
                }
            }
        return true;
        
    }
   
}