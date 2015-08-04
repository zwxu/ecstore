<?php

 
function theme_widget_mr_pone_articles(&$setting,&$smarty){
    if($setting['articleId']){
        $oMAI = app::get('content')->model('article_indexs');
        $sql = "SELECT i.article_id,i.title,i.node_id,b.content,b.image_id FROM sdb_content_article_indexs i left join sdb_content_article_bodys b on i.article_id=b.article_id where i.article_id= ".$setting['articleId'];
        $setting['content'] = $oMAI->db->selectrow($sql);
    }
    //echo '<pre>';print_r($setting);die;//qianleidebug
    return $setting;
}
?>
