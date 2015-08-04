<?php



class b2c_mdl_comment_goods_point extends dbeav_model{
   

      function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null){
        $row = parent::getList($cols, $filter, $offset, $limit, $orderType);
        foreach($row as $key => $val){
            $val['type_name'] = $this->get_type_name($val['type_id']);
            $aData[] = $val;
        }
        
        return $aData;
    }
    
    function get_type_name($type_id){
        $comment_goods_type = $this->app->model('comment_goods_type');
        $sdf = $comment_goods_type->dump($type_id);
        return $sdf['name'];
    }
    /*
        设置评分状态 addon['display']
    */
    function set_status($comment_id=0,$status='false'){
        if(!$comment_id) return ;
        $row = $this->getList('addon',array('comment_id' => $comment_id));
        if($row){
            foreach((array)$row as $key =>$val){
                $addon = unserialize($val['addon']);
                $addon['display'] = $status;
                $sdf['addon'] = serialize($addon);
                $this->update($sdf,array('comment_id' => $comment_id));
            }
        }
        
    }
    /*
    商品各类总分,平均分
    return array
    params goods_id
    */
    function get_goods_point($goods_id=null){
        if(!$goods_id) return null;
        $objType = $this->app->model('comment_goods_type');
        $row = $objType->getList('*');
        foreach((array)$row as $val){
            $data = $this->get_type_point($val['type_id'],$goods_id);
            $data['type_name'] = $val['name'];
            $aData[] = $data;
        }
        return $aData;    
        
    }
    
    function get_type_point($type_id,$goods_id){
        $row = $this->getList('*',array('goods_id' => $goods_id,'type_id' => $type_id));
        $num = 0;
        $total = 0;
        foreach((array)$row as $val){
            $addon = unserialize($val['addon']);
            if($addon['display'] == 'true')
            {
                $num = $num+$val['goods_point'];
                $total = $total+1;    
            }
            
        }
        if($num == 0 || $total==0) $data['avg'] = 0;
        else $data['avg'] =  number_format((float)$num/$total,1);
        $data['total'] = $num;
        return $data;
        
    }
    
    /*
    作为商品总分的类型ID
    return array
    params goods_id
    */
    function totalType(){
        $objType = $this->app->model('comment_goods_type');
        $row = $objType->getList('*');
        $type_id = 1;
        foreach((array)$row as $val){
           $addon = unserialize($val['addon']);
           if($addon['is_total_point'] == 'on') $type_id = $val['type_id'];
        }
        return $type_id;
    }
    /*
    单条评论商品单一类型评分
    return array
    params goods_id
    */
    
    function get_comment_point($comment_id=null){
        if(!$comment_id) return null;
        $type_id = $this->totalType();
        $row = $this->getList('goods_point',array('comment_id' => $comment_id,'type_id' => $type_id));
        if($row) return $this->star_class($row[0]['goods_point']);
        return null;
    }
     /*
    商品单一类型评分
    return array
    params goods_id
    */
    function get_single_point($goods_id=null){
        if(!$goods_id) return null;
        $type_id = $this->totalType();
        $_singlepoint = $this->get_type_point($type_id,$goods_id);
        $_singlepoint['avg_num'] = $_singlepoint['avg'];
        if(!$_singlepoint) return null;
        else{
            $_singlepoint['avg'] = $this->star_class($_singlepoint['avg']);
            return $_singlepoint;
        }
    }
    
    function star_class($avg){
        $a = $avg;
        $t = round($avg);
        if( $a==$t ) {
            $r = floor($a);
        }else {
            switch( $t>$a ) {
                case true:
                if( $t-$a!=0.5 ) {
                    $r = $t;break;
                }
                case false:
                $r = floor($a).'_';
            }
        }
        return $r;
    }
    
    function get_point_nums($gid=null)
    {
        if(!$gid) return 0;
        $i = 0;
        $data = $this->getList('*',array('goods_id' => $gid));
        $comm = array();
        foreach($data as $k => $v)
        {
            if(!in_array($v['comment_id'],$comm))
            {
                if($v['addon'])
                {
                    $addon = unserialize($v['addon']);
                    if($addon['display'] == 'true')
                    $i++;
                }
                
            }
            $comm[] = $v['comment_id'];
        }
        return $i;
    }
    
}
