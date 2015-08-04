<?php

 

class b2c_mdl_member_msg extends dbeav_model{

 var $type;
 
   function __construct(&$app){
        $this->app = $app;

        parent::__construct($app);
        
    }
     
   function get_type(){
       
          return $type;
    
   }

   function set_type($type){
      $this->type = $type;
   }

  function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
      
         $aData = parent::getList('*',array('from_type' => $this->type)); 
         return $aData;
  }
  
    function get_msg_by_Id($nMemId){
        if($rows = parent::getList('*',array('to_id'=>$nMemId,'has_sent'=>'true' ),0,20,'msg_id DESC')){
            $ret['data'] = $rows;
            $ret['total'] = count($rows);
            return $ret;
        }
        return false;         
    }
    
    function get_unsent_msg($nMemId){
        if($rows = parent::getList('*',array('from_id'=>$nMemId,'has_sent'=>'false','from_type'=>0 ),0,20,'msg_id DESC')){
            $ret['data'] = $rows;
            $ret['total'] = count($rows);
            return $ret;
        }
        return false;     
    }
    
    function get_from_msg($nMemId){
        if($rows = parent::getList('*',array('from_id'=>$nMemId,'has_sent'=>'true','from_type'=>0 ),0,20,'msg_id DESC')){
            $ret['data'] = $rows;
            $ret['total'] = count($rows);
            return $ret;
        }
        return false;     
    }
    
    ####获取商店留言
    
    function get_shop_msg(){
        $data = parent::getList('*',array('from_type' => 2));
        return $data;
    }
    
    function get_msg($msg_id){
        $data = parent::getList('*',array('msg_id' => $msg_id));
        return $data[0];
    }
    
    function get_reply_msg($msg_id){
        $data = parent::getList('*',array('for_id' => $msg_id));
        return $data;
    }
    ###回复留言
    
    function reply_msg($Data){
        $data = parent::getList('*',array('msg_id' => $Data['for_id']));
        $sdf['msg_id'] = '';
        $sdf['for_id'] = $Data['for_id'];
        $sdf['from_id'] = 1;
        $sdf['from_uname'] = app::get('b2c')->_('管理员');
        $sdf['to_uname'] = $data[0]['from_uname'];
        $sdf['to_id'] = $data[0]['from_id'];
        $sdf['subject'] = $Data['subject'];
        $sdf['content'] = $Data['message'];
        $sdf['create_time'] = time();
        if($this->save($sdf)){
            return true;
        }
        else{
            return fasle;
        }
    }
    
    ##删除回复
    
    function delete_reply_msg($msg_id){
        $aSql = 'delete from sdb_b2c_member_msg where msg_id='.$msg_id;
        if($this->db->exec($aSql)){
            return true;
        }
        else{
            return fasle;
        }
    }
    function setReaded($nMsgId){
      $data['msg_id'] = $nMsgId;
      $data['has_read'] = true;
      return $this->save($data);
    }
    
    function calc_unread_msg($nMemId){
        $filter = array('to_id'=>$nMemId,'has_sent'=>'true','has_read'=>'false');
        return $this->count($filter);
    }
    

    
    function del_inbox_msg($aMsgId){
      $filter = array('msg_id'=>$aMsgId,'from_type'=>0);
      return $this->delete($filter);
    }
    
    function del_track_msg($aMsgId){
      $filter = array('msg_id'=>$aMsgId,'from_type'=>0);
      return $this->delete($filter);
    }
    
    function del_outbox_msg($aMsgId){
      $filter = array('msg_id'=>$aMsgId,'from_type'=>0);
      return $this->delete($filter);
    }
    
    
    //管理员发站内信
    
    function send_msg($member_id,$data){
        $obj_member = $this->app->model('members');
        $aData = $obj_member->dump($member_id);
        $sdf['msg_id'] = '';
        $sdf['for_id'] = 0;
        $sdf['from_id'] = 1;
        $sdf['from_uname'] = app::get('b2c')->_('管理员');
        $sdf['from_type'] = $data['from_type'];
        $sdf['to_uname'] = $aData['contact']['name'];
        $sdf['to_id'] = $member_id;
        $sdf['subject'] = $data['title'];
        $sdf['content'] = $data['content'];
        $sdf['create_time'] = time();
        if($this->save($sdf)){
            return true;
        }
        else{
            return fasle;
        }
    }
    /**
     * 实质上是重写了getlist方法
     * @params string - 特殊的列名
     * @params array - 限制条件
     * @params 偏移量起始值
     * @params 偏移位移值
     * @params 排序条件
     */
    public function get_message_list($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null)
    {
        return parent::getList($cols, $filter, $offset, $limit, $orderby);
    }

}  
