<?php
function theme_widget_gallery_recommend_store(&$setting,&$smarty) {
    $show_nums=intval($setting['show_nums']?$setting['show_nums']:'7');
    $storeids=array_values($setting['store']);
    $search_store_id=$smarty->pagedata['search_store_id'];

    //取得店铺信息
    $mdl_store = app::get('business')->model('storemanger');
    $filter=array('store_id'=>$search_store_id,'disabled'=>'false');
    $filter['str_where']=$mdl_store->_getSearchFilter($filter);
    $where=str_replace("`sdb_business_storemanger`"," s ",$mdl_store->_filter($filter));
    $sql[]="SELECT s.store_id,s.image,s.store_name,s.buy_m_count,IFNULL(p.storePoint,0) as avg_point,IFNULL(p.storePointpercent,100) as avg_percent ";
    
    if(!empty($storeids)){
        $sql[]=', case when s.store_id in ('.implode(',',$storeids).') then 1 else 0 end as gtop ';
    }else{
        $sql[]=', 0 as gtop ';
    }
    $sql[]=" FROM sdb_business_storemanger as s  left join (";
    //店铺评分
    $sql[]="select store_id";
    $sql[]=",sum(if(type_id=1,avg_percent,0)) as storePointpercent";
    $sql[]=",sum(if(type_id=1,avg_point,0)) as storePoint";
    $sql[]="from sdb_business_comment_stores_point ";
    $sql[]="group by store_id";
    $sql[]=") as p  on p.store_id=s.store_id";
    $sql[]=" where ".$where;
    $sql[]=" ORDER BY  gtop desc,s.buy_m_count desc";
    $sql[]=' limit 0,'.$show_nums;
    $ssql=implode(' ',$sql);
    $store=$mdl_store->db->select($ssql);
    
    $result['storelist']=$store;
    return $result;
}
