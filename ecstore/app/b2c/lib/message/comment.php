<?php

 
class b2c_message_comment{
    
    var $objComment;
    var $type;
    function __construct(&$app){
        $this->app = &$app;
        $this->objComment = $this->app->model('member_comments');
        $this->objComment->type = $this->type;
    }
     function save(&$aData){
         if($this->objComment->save($aData)){
             return true;
         }
         else{
             return false;
         }
         
     }
     
     function dump($id){
         $aData = $this->getList('*', array('comment_id' => $id,'for_comment_id' => 'all'));
         if($aData[0]['object_type'] == 'discuss'){
             $goods_point = $this->app->model('comment_goods_point');
             $row = $goods_point->getList('*',array('comment_id' => $id));
             $aData[0]['goods_point'] = $row;
         }
         return $aData[0];
     }

     function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        $aData = $this->objComment->getList($cols='*', $filter, $offset, $limit, $orderby);
        $objMember = $this->app->model('members');
        $row = array();
        foreach($aData as $key => $val){
            $val['member_lv_name'] = $objMember->get_lv_name($val['author_id']);
            $val['addon'] = unserialize($val['addon']);
            $row[] = $val;
        }
        return $row;
      }
      function count($filter=array()){
        $aData = $this->objComment->count($filter);
        return $aData;
      }
      function delete($filter=null){
          if($this->objComment->delete($filter)){
              return true;
          }
          else{
              return false;
          }
      }
      
      function get_reply($comment_id){
        $aData = $this->getList('*',array('for_comment_id' => $comment_id));
        return $aData;
  }
  
      function setReaded($comment_id){
        $sdf = $this->dump($comment_id);
        $sdf['mem_read_status'] = 'true';
        $this->save($sdf);
    }
    
}