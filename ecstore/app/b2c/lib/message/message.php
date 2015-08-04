<?php

 
class b2c_message_message extends b2c_message_comment{

    function __construct(&$app){
         
        $this->app = $app;
        $this->type = 'message';
        parent::__construct($app);

    }
    function send($aData,$member_data=null){
        $data = array(
                        'author_id' => $member_data['member_id'] ? $member_data['member_id']:0,
                        'author' => $member_data['uname']?$member_data['uname']:app::get('b2c')->_('游客'),
                        'title' =>strip_tags($aData['subject']), 
                        'comment' => strip_tags($aData['message']),
                        'time' => time(),
                        'contact'=> $aData['contact'] ? strip_tags($aData['contact']):$member_data['email'],
                        'object_type' => 'message',
                        'ip' => $aData['ip'],
                        'display' => $aData['display'],
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
        if($aData['display'] == "true")
		{
			$sdf['display'] = 'true'; 
        }
		$sdf['reply_name'] = app::get('b2c')->_('管理员');
		$sdf['lastreply'] = time();
		if(!($this->save($sdf))){
			return false; 
		}   
        $sdf['comment_id']= '';
        $sdf['for_comment_id'] = $aData['comment_id'];
        $sdf['object_type'] = "message";
        $sdf['to_id'] = $sdf['author_id'];
        $sdf['author_id'] = null;
        $sdf['author'] = app::get('b2c')->_('管理员');
        $sdf['title'] = strip_tags($aData['title']);
        $sdf['contact'] = '';
        $sdf['display'] = 'true';
        $sdf['lastreply'] = time();
        $sdf['time'] = time();
        $sdf['comment'] = htmlspecialchars($aData['reply_content']);
        if($this->save($sdf)){
            $comments = $this->app->model('member_comments');
            $data['member_id'] = $sdf['to_id'];
            $comments->fireEvent('messagereply',$data,$sdf['to_id']);
            return true;
        }
        else{
            return false;
        }
    }
    
}