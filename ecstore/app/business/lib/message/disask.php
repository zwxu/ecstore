<?php
class business_message_disask extends b2c_message_disask{
    function __construct(&$app){
        $this->app_current = $app;
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }
     
    function send($aData,$item){
       
        $sdf['order_id'] = $aData['order_id'];
        $sdf['comments_type'] = ($aData['comments_type'] || $aData['comments_type'] == '0')?$aData['comments_type']:'1';
       
        $sdf['for_comment_id'] = $aData['for_comment_id']?$aData['for_comment_id']:0;
        if($sdf['for_comment_id']){
            $aRes = $this->dump($sdf['for_comment_id']);
            unset($aRes['goods_point']);
            $aRes['lastreply'] = time();
            $aRes['reply_name'] = $aData['author'];
            $this->save($aRes);
        }
        $sdf['type_id'] = $aData['goods_id'];
        $sdf['object_type'] = $item;
        $sdf['author_id'] = $aData['author_id'];
        $sdf['author'] = $aData['author'];
        $sdf['to_id'] = $aData['to_id'];
        $sdf['contact'] = htmlspecialchars($aData['contact']);
        $sdf['title'] = htmlspecialchars($aData['title']);
        $sdf['comment'] = htmlspecialchars($aData['comment']);
        $sdf['time'] = $aData['time'];
        $sdf['lastreply'] = $aData['lastreply'];
        $sdf['ip'] = $aData['ip'];
        $sdf['display'] = $aData['display'];
        if($aData['hidden_name']){
            $addon['hidden_name'] = "YES";
        }
        if($aData['gask_type'] && $item == 'ask'){
            $sdf['gask_type'] = $aData['gask_type'];
        }
        $sdf['addon'] = serialize($addon);

        //咨询添加shop_id -- start
        if($business_shopid=kernel::service("business.addshopid")){
          $business_shopid->setShopId($sdf);
        }//--end

        if($this->save($sdf)){
            if($item == 'discuss' && $aData['goods_point']){
                $goods_point = $this->app_b2c->model('comment_goods_point');
                $_pointsdf['comment_id'] = $sdf['comment_id'];
                foreach($aData['goods_point'] as $key=>$val){
                    if($aData['display'] == 'true')
                        $_pointsdf_addon['display'] = 'true';
                    else
                        $_pointsdf_addon['display'] = 'false';
                    $_pointsdf['addon'] = serialize($_pointsdf_addon);
                    $_pointsdf['goods_id'] = $aData['goods_id'];
                    $_pointsdf['goods_point'] = (float)$val['point'];
                    if($_pointsdf['goods_point']<1) $_pointsdf['goods_point']=5;
                    ($_pointsdf['goods_point']<=5) or $_pointsdf['goods_point']=5;
                    $_pointsdf['member_id'] = $aData['author_id'];
                    $_pointsdf['type_id'] = $key;
                    $goods_point->save($_pointsdf);
                    unset($_pointsdf['point_id']);
                }
            }
            return $sdf['comment_id'];
        }
        else{
            return false;
        }
    }
    
    function get_business_disask($store_id=null,$page=1,$object_type='ask',$filter,$limit=0){
        if(!$store_id) return null;
        if($limit != 0){
            $list_listnum = $limit;
        }else{
            $list_listnum = intval($this->app->getConf('comment.index.listnum')); 
        }
        if($list_listnum == 0 || $list_listnum == '') return ;
        $this->objComment->type = $object_type;
        $filter['store_id'] = $store_id;
        if(isset($filter['comments_type'])){
            $data = array();
            foreach($this->getList('for_comment_id',$filter,0,-1) as $rows){
                if($rows['for_comment_id'])$data[] = $rows['for_comment_id'];
            }
            $filter['comment_id'] = (empty($data))?-1:$data;
        }
        $filter['for_comment_id'] = 0;
        $filter['comments_type'] = '1';
        $params = array();
        $count = $this->count($filter);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $this->getList('*',$filter,$start,$list_listnum);
        foreach($params['data'] as $key=>$v){
            $params['data'][$key]['items'] = $this->get_reply($v['comment_id']);
            $params['data'][$key]['addition'] = $this->get_addition($v['comment_id']);
            foreach($params['data'][$key]['addition'] as $ckey=>$cv){
                $params['data'][$key]['addition'][$ckey]['items'] = $this->get_reply($cv['comment_id']);
            }
        }
        $params['page'] = $maxPage;
        return $params;
    }

    function get_business_disask_phone($store_id=null,$page=1,$object_type='ask',$filter,$limit=0){
        if(!$store_id) return null;
        if($limit != 0){
            $list_listnum = $limit;
        }else{
            $list_listnum = intval($this->app->getConf('comment.index.listnum')); 
        }
        if($list_listnum == 0 || $list_listnum == '') return ;
        $this->objComment->type = $object_type;
        $filter['store_id'] = $store_id;
        if(isset($filter['comments_type'])){
            $data = array();
            foreach($this->getList('for_comment_id',$filter,0,-1) as $rows){
                if($rows['for_comment_id'])$data[] = $rows['for_comment_id'];
            }
            $filter['comment_id'] = (empty($data))?-1:$data;
        }
        $filter['comments_type|in'] = array('1','3');
        $params = array();
        $data_comment = $this->getList('comment_id',$filter);
        $count = count($data_comment);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $this->getList('*',$filter,$start,$list_listnum);
        foreach($params['data'] as $key=>$v){
            $params['data'][$key]['items'] = $this->get_reply($v['comment_id']);
        }
        $params['page'] = $maxPage;
        return $params;
    }
    
    function get_member_disask($member_id=null,$page=1,$object_type='ask'){
        if(!$member_id) return null;
        $list_listnum = intval($this->app->getConf('comment.index.listnum')); 
        if($list_listnum == 0 || $list_listnum == '') return ;
        $this->objComment->type = $object_type;
        $filter = array('for_comment_id' => 0,'author_id' => $member_id,'display' => 'true');
        $params = array();
        $count = $this->count($filter);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start; 
        $params['data'] = $this->getList('*',$filter,$start,$list_listnum);
        foreach($params['data'] as $key=>$v){
            $params['data'][$key]['items'] = $this->get_reply($v['comment_id']);
            $params['data'][$key]['addition'] = $this->get_addition($v['comment_id'],array('display'=>'true'));
            foreach($params['data'][$key]['addition'] as $ckey=>$cv){
                $params['data'][$key]['addition'][$ckey]['items'] = $this->get_reply($cv['comment_id']);
            }
        }
        $params['page'] = $maxPage;
        return $params;
    }
    
    function get_reply($comment_id){
        $sql = "select *,case comments_type when '0' then case author when '管理员' then author else '掌柜' end else author end as author1 from sdb_b2c_member_comments where for_comment_id='{$comment_id}' and (comments_type='0' or comments_type='2')";
        //$aData = $this->getList('*',array('for_comment_id' => $comment_id,'comments_type'=>'0'));
        $aData = $this->app_b2c->model('member_comments')->db->select($sql);
        foreach((array)$aData as $key => $value){
            $aData[$key]['author'] = $value['author1'];
            unset($aData[$key]['author1']);
        }
        return $aData;
    }
    
    function get_addition($comment_id,$filter=null){
        $filter['for_comment_id'] = $comment_id;
        $filter['comments_type'] = '3';
        $aData = $this->getList('*',$filter);
        return $aData;
    }
    
    function setShowed($comment_id, $value){
        $sql = " update sdb_b2c_member_comments set display='".(($value=='true')?'true':'false')."' WHERE comment_id=".intval($comment_id);
        $this->app_b2c->model('member_comments')->db->exec($sql);
    }
    
    function setReaded($comment_id){
        $sql = " update sdb_b2c_member_comments set mem_read_status='true' WHERE comment_id=".intval($comment_id);
        $this->app_b2c->model('member_comments')->db->exec($sql);
    }
    
    function getGoodsIndexComments($gid,$item,$page=1,$type_id=null){
        if($item == 'ask') return kernel::single('b2c_message_disask')->getGoodsIndexComments($gid,$item,$page,$type_id);
        $list_listnum = intval($this->app->getConf('comment.index.listnum'));
        $this->objComment->type = $item;
        if(is_array($gid)){
            $data = array();
            foreach($this->getList('for_comment_id,comment_id',$gid,0,-1) as $rows){
                if(isset($gid['comments_type'])){
                    if($rows['for_comment_id'])$data[] = $rows['for_comment_id'];
                }elseif($rows['comment_id']){
                    $data[] = $rows['comment_id'];
                }
            }
            $filter['comment_id'] = (isset($gid['comments_type']) && empty($data))?-1:$data;
            $filter['type_id'] = $gid['type_id'];
            switch($filter['orderb']){
                case '1':
                $orderby = ' time desc ';
                break;
                default:
                $orderby = ' time desc ';
                break;
            }
            unset($filter['orderb']);
        }else{
            $filter['type_id'] = $gid;
        }
        $filter['for_comment_id'] = 0;
        $filter['display'] = 'true';
        $filter['comments_type'] = '1';
        if($type_id) $filter['gask_type'] = $type_id;
        //$aData = $this->getList('*',$filter);
        $count = $this->count($filter);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $objOrderItem = &$this->app->model('order_items');
        $sql = "  select c.*,group_concat(convert(p.spec_info,char) separator  '、') as spec_info,m.name as author_alias ".
            " from sdb_b2c_member_comments as c ".
            " left join sdb_b2c_members as m on c.author_id=m.member_id".
            " left join ".$objOrderItem->table_name(1)." as i on c.order_id=i.order_id and c.type_id=i.goods_id ".
            " left join sdb_b2c_products as p on i.goods_id=p.goods_id and i.product_id=p.product_id ".
            " where c.object_type='{$item}' and c.for_comment_id=0 and c.type_id=".intval($filter['type_id'])." and c.display='true' and c.comments_type='1' ".(isset($filter['gask_type'])?" and c.gask_type='".$type_id."' ":" ").
            ($filter['comment_id']?" and c.comment_id in ('".implode("','", $filter['comment_id'])."') ":" ");
        if(is_array($gid)){
            $sql .= (isset($gid['comment'])?" and c.comment is null ":" ").
            (isset($gid['comment|noequal'])?" and c.comment is not null ":" ");
        }else{
            $sql .= " and c.comment is not null ";
        }
        $sql .= " group by  comment_id,i.goods_id ".
            ($orderby?" order by ".$orderby:" ").
            " limit ".$start.",".$list_listnum;

        //$aData = $this->getList('*',$filter,$start,$list_listnum);
        $aData = $objOrderItem->db->select($sql);
        $data = array();
        $goods_point = $this->app_current->model('comment_goods_point');
        foreach((array)$aData as $key=>$val){
            if($val['object_type'] == 'discuss'){
                $row = $goods_point->get_comment_point($val['comment_id']);
                $val['goods_point'] = $row;
            }
            $spec = array();
            if($val['spec_info']){
                foreach(explode('、', $val['spec_info']) as $rows){
                    $temp = explode('：', $rows);
                    $spec[$temp[0]][$temp[1]] = $temp[1];
                }
                $spec_info = array();
                foreach((array)$spec as $ck => $cv){
                    $spec_info[$ck] = implode('、', (array)$cv);
                }
                $spec = array_filter($spec_info);
            }
            $val['spec_info'] = $spec;
            $val['addon'] = unserialize($val['addon']);
            if(!empty($val['author_alias'])) $val['author'] = $val['author_alias'];
            unset($val['author_alias']);
            if(isset($val['addon']['hidden_name']) && $val['addon']['hidden_name'] == 'YES' && ($val['author_id'] !=0 || $val['author_id'] !=1)){
                //$val['author'] = $this->replaceStartFilter($val['author'],1);
                $val['author'] = mb_substr($val['author'], 0, 1, 'UTF-8').'****'.mb_substr($val['author'], mb_strlen($string, 'UTF-8')-1, 1, 'UTF-8');
            }
            $data[] = $val;
        }
        $result['start'] = $start+1;
        $result['end'] = $start+$list_listnum;
        $result['total'] = $count;
        $result['data'] = $data;
        $result['page'] = $maxPage;
        $result['current_page'] = $page;
        return $result;        
    }
    
    private function replaceStartFilter($string, $start = 0, $end = 0) {
        $count = mb_strlen($string, 'UTF-8'); //此处传入编码，建议使用utf-8。此处编码要与下面mb_substr()所使用的一致
        if (!$count) {
            return $string;
        }
        if ($end == 0) {
            $end = $count-1;
        }

        $i = 0;
        $returnString = '';
        while ($i < $count) {
            $tmpString = mb_substr($string, $i, 1, 'UTF-8'); // 与mb_strlen编码一致
            if ($start <= $i && $i < $end) {
                $returnString .= '*';
            } else {
                $returnString .= $tmpString;
            }
            $i++;
        }
        return $returnString;
    }
    
    function getCommentsReply($aId, $display=false){
        if($display)
        {
            $aId = (array)$aId;
            $sql = " select c.*,m.name as author_alias from sdb_b2c_member_comments as c left join sdb_b2c_members as m on c.author_id=m.member_id ";
            $sql .= " where c.object_type='discuss' and c.for_comment_id in (".implode(',',$aId).") and c.display='true' ";
            $aData = array();
            if(!$aId) return $aData;
            foreach((array)$this->app->model('member_comments')->db->select($sql) as $val){
                $val['addon'] = unserialize($val['addon']);
                if(!empty($val['author_alias'])) $val['author'] = $val['author_alias'];
                unset($val['author_alias']);
                if($val['comments_type']!=0 && isset($val['addon']['hidden_name']) && $val['addon']['hidden_name'] == 'YES' && ($val['author_id'] !=0 || $val['author_id'] !=1)){
                    //$val['author'] = $this->replaceStartFilter($val['author'],1);
                    $val['author'] = mb_substr($val['author'], 0, 1, 'UTF-8').'****'.mb_substr($val['author'], mb_strlen($string, 'UTF-8')-1, 1, 'UTF-8');
                }
                $aData[] = $val;
            }
            //$aData = $this->getList('*',array('for_comment_id' => $aId,'display' => 'true'));
        }
        return $aData;
    }
}