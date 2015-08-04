<?php



class b2c_mdl_member_comments extends dbeav_model{

 var $defaultOrder = array('time','DESC');
 var $has_one = array(
        'goods_point'=>'comment_goods_point',

    );

     var $has_many = array(
//        'product' => 'products:contrast',
    );
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
    function get_schema()
    {
        $schema = parent::get_schema();
        $params = $_GET;
        if($params['ctl']==='admin_member_discuss' || $params['ctl']==='admin_member_gask')
        {
            unset($schema['in_list'][5]);
            unset($schema['default_in_list'][2]);
            return $schema;
        }
        else
        {
            unset($schema['in_list'][0]);
            return $schema;
        }
    }
    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        if($this->type == 'msgtoadmin' || $this->falg == 'msgtoadmin'){
             $this->type = 'msg';
             $filter['to_id'] = 1;
         }
         if($this->type){
            $filter['object_type'] = $this->type;
        }
        if($filter['for_comment_id'] === 'all'){
             unset($filter['for_comment_id']);
        }
        else{
            if (isset($filter['for_comment_id']))
                $filter['for_comment_id'] = $filter['for_comment_id'] ? $filter['for_comment_id']:0;
        }
         $aData = parent::getList($cols, $filter, $offset, $limit, $orderby);
         return $aData;
  }

  function count($filter=array()){
       if($this->type == 'msgtoadmin'){
             $this->type = 'msg';
             $this->falg = 'msgtoadmin';
             $filter['to_id'] = 1;
         }
         if($this->type){
            $filter['object_type'] = $this->type;
        }
         if($filter['for_comment_id'] === 'all'){
             unset($filter['for_comment_id']);
         }
         else{
             $filter['for_comment_id'] = $filter['for_comment_id'] ? $filter['for_comment_id']:0;
            }
           return parent::count($filter);
  }
  /*设置管理员阅读状态*/
  function set_admin_readed($comment_id){
        $sdf = $this->dump($comment_id);
        $sdf['adm_read_status'] = 'true';
        $this->save($sdf);
  }

    function searchOptions(){
        $arr = parent::searchOptions();
        if($this->type ==='ask' || $this->type ==='discuss')
        {
            unset($arr['title']);
            return array_merge($arr,array(
                'name'=>app::get('b2c')->_('商品名称'),
                'bn'=>app::get('b2c')->_('商品编号'),
            ));
        }
        
        else
        {
            return $arr;
        }
            
    }
    
    public function fireEvent($action , &$object, $member_id=0)
    {
         $trigger = &$this->app->model('trigger');

         return $trigger->object_fire_event($action, $object, $member_id, $this);
    }

    function _filter($filter,$tableAlias=null,$baseWhere=null){
        $objGoods = &$this->app->model('goods');
        if($filter['name']){
            $goods_id = $objGoods->getList('goods_id',array('name|has'=>$filter['name']));
            if(is_array($goods_id)){
                   foreach($goods_id as $gk=>$gv){
                    $filter['type_id'][] = $gv['goods_id'];
                }
                if(!isset($filter['type_id'])){
                    $filter['comment_id'] = 0;
                }
            }
            unset($filter['name']);
        }
        if($filter['bn']){
            $goods_id = $objGoods->getList('goods_id',array('bn'=>$filter['bn']));
            if(is_array($goods_id)){
                   foreach($goods_id as $gk=>$gv){
                    $filter['type_id'][] = $gv['goods_id'];
                }
                if(!isset($filter['type_id'])){
                    $filter['comment_id'] = 0;
                }
            }
            unset($filter['bn']);
        }
        $filter = parent::_filter($filter);
        return $filter;
    }

    function getCommentByName(){

    }

    /**
     * @description 删除评论与咨询后触发短信等事件
     * @access public
     * @param array $data
     * @return boolean
     */
    public function pre_recycle($data) {
        $ret = $this->app->getConf('messenger.actions.comments-delete');
        if(!$ret) return true;
        $action = explode(',',$ret);
        $emailTmpl=''; $msgboxTmpl=''; $smsTmpl='';
        $systmpl = $this->app->model('member_systmpl');
        $queue = app::get('base')->model('queue');
        foreach($data as $key=>$value){
            if(!$value['author_id']) continue;

            $member = $this->app->model('members')->dump(array('member_id'=>$value['author_id']),'mobile,email');

            //发邮件
            if(in_array('b2c_messenger_email',$action) && $member['contact']['email']){
                if(!$emailTmpl){
                    $emailTmpl = $systmpl->fetch('messenger:b2c_messenger_email/comments-delete',array());
                }
                $worker = 'b2c_queue.send_mail';
                $params['acceptor'] = $member['contact']['email'];
                $params['body'] = $emailTmpl;
                $params['title'] = $this->app->_('删除评论与咨询');
            }

            //发站内信
            if(in_array('b2c_messenger_msgbox',$action)){
                if(!$msgboxTmpl){
                    $msgboxTmpl = $systmpl->fetch('messenger:b2c_messenger_msgbox/comments-delete',array());
                }
                $worker = 'b2c_queue.send_msg';
                $params['member_id'] = $value['author_id'];
                $params['data']['content'] = $msgboxTmpl;
                $params['data']['title'] = $this->app->_('删除评论与咨询');
                $params['name'] = $value['author'];
            }

            //发短信
            if(in_array('b2c_messenger_sms',$action) && $member['contact']['phone']['mobile']){
                if(!$smsTmpl) {
                   $smsTmpl = $systmpl->fetch('messenger:b2c_messenger_sms/comments-delete',array());
                }
                $worker = 'b2c_queue.send_sms';
                $params['mobile_number'] = $member['contact']['phone']['mobile'];
                $params['data']['title'] = $this->app->_('删除评论与咨询');
                $params['data']['content'] = $smsTmpl;
            }
            
            if($worker){
                $p['queue_title'] = $this->app->_('删除评论与咨询');
                $p['start_time'] = time();
                $p['params'] = $params;
                $p['worker'] = $worker;
                $queue->insert($p);
                unset($p);
            }
        }
        return true;
    }

     //取得评分特定评论
     function  getcommentsbystoreid($store_id,$score,$start,$end=null) {

        $sql = "SELECT * FROM sdb_b2c_member_comments  LEFT JOIN sdb_b2c_comment_goods_point ON sdb_b2c_member_comments.comment_id = sdb_b2c_comment_goods_point.comment_id
                WHERE  sdb_b2c_member_comments.object_type='discuss'
                 AND  sdb_b2c_comment_goods_point.goods_point < {$score}  AND sdb_b2c_member_comments.store_id = '{$store_id}'
                 AND   sdb_b2c_member_comments.time > {$start} ";
        if($end){
             $sql .="  AND  sdb_b2c_member_comments.time < {$end} ";
        }

       $row = $this->db->select($sql);

       return $row;


    }

    
    function report_comment($store_id){
         //全部评价
         $sql = "SELECT COUNT(sdb_b2c_member_comments.comment_id) as comm_count, CONCAT(YEAR(FROM_UNIXTIME(sdb_b2c_member_comments.time)),'-', LPAD(month(FROM_UNIXTIME(sdb_b2c_member_comments.time)),2,'0') AS total_date 
                        FROM  sdb_b2c_member_comments   WHERE sdb_b2c_member_comments.object_type='discuss'
                        AND sdb_b2c_member_comments.store_id = '{$store_id}'
                        GROUP BY total_date ORDER BY   total_date DESC";
         $row = $this->db->select($sql);

         return $row;

    }

    function report_scorecomment($store_id,$score){
        //中差评价
        $sql = "SELECT COUNT(sdb_b2c_member_comments.comment_id)as score_count, CONCAT(YEAR(FROM_UNIXTIME(sdb_b2c_member_comments.time)),'-',LPAD( month(FROM_UNIXTIME(sdb_b2c_member_comments.time)),2,'0')) AS total_date 
                       FROM sdb_b2c_member_comments 
                       LEFT JOIN sdb_b2c_comment_goods_point ON sdb_b2c_member_comments.comment_id = sdb_b2c_comment_goods_point.comment_id
                       WHERE  sdb_b2c_member_comments.object_type='discuss'
                       AND  sdb_b2c_comment_goods_point.goods_point < {$score}  AND sdb_b2c_member_comments.store_id = '{$store_id}'
                       GROUP BY total_date ORDER BY   total_date DESC  ";  

                      
                       
        $row = $this->db->select($sql);

        return $row;

    }

    function report_delcomment($store_id){
        //删除的评价
        $sid =$store_id;
        $strF="s:".strlen($sid).":\"".$sid."\";%'";
        $store_id="'%\"store_id\";".$strF;

         $sql ="SELECT COUNT(sdb_desktop_recycle.item_id) AS del_count,CONCAT(YEAR(FROM_UNIXTIME(sdb_desktop_recycle.drop_time)),'-', LPAD(month(FROM_UNIXTIME(sdb_desktop_recycle.drop_time)),2,'0')) AS total_date
                       FROM sdb_desktop_recycle  WHERE  item_type='member_comments' AND app_key='b2c' 
                       AND  item_sdf LIKE  {$store_id} 
                       GROUP BY total_date ORDER BY   total_date DESC  ";
          $row = $this->db->select($sql);

        return $row;

    }

}
