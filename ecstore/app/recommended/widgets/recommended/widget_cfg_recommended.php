<?php
function widget_cfg_recommended() {
    $data = array();
    // 商品排序规则
    $data['orderby'] = array(
        'last_buy desc'       => '上次购买时间 新->旧',
        'last_modify desc'    => '发布时间 新->旧',
        'buy_w_count desc'    => '周购买次数 高->低',
        'buy_count desc'      => '总购买次数 高->低',
        'comments_count desc' => '评论次数 高低',
    );
    return $data;
}
