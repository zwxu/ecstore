<?php 
 $db['goods_order']=array ( 
  'columns' => 
  array (
    'id' => 
    array (
      'type' => 'int',
      'required' => true,
      'default' => 0,
      'pkey' => true,
    ),
    'maxBuyMonthCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最大月销量',
    ),
    'minBuyMonthCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最小月销量',
    ),    
    'subBuyMonthCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '月销量百分比单位',
    ),    
    'maxViewCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最大浏览量',
    ),
    'minViewCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最小浏览量',
    ),    
    'subViewCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '浏览量百分比单位',
    ),   
    'maxBuyPercent' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最大购买率',
    ),
    'minBuyPercent' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最小购买率',
    ),    
    'subBuyPercent' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '购买率百分比单位',
    ),
    'maxFavCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高收藏量',
    ),
    'minFavCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低收藏量',
    ),    
    'subFavCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '收藏量百分比单位',
    ), 
    'maxPrice' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高价格',
    ),
    'minPrice' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低价格',
    ),    
    'subPrice' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '价格百分比单位',
    ), 
    'maxLastModify' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最新修改时间',
    ),
    'minLastModify' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最早修改时间',
    ),    
    'subLastModify' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '修改时间百分比单位',
    ),
    'maxStoreLevel' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高店铺等级',
    ),
    'minStoreLevel' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低店铺等级',
    ),    
    'subStoreLevel' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '店铺等级百分比单位',
    ),
    'maxStorePoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高店铺描述评分',
    ),
    'minStorePoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低店铺描述评分',
    ),    
    'subStorePoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '描述评分百分比单位',
    ),
    'maxServicePoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高服务态度评分',
    ),
    'minServicePoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低服务态度评分',
    ),    
    'subServicePoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '服务态度评分百分比单位',
    ),
    'maxDeliveryPoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高发货速度评分',
    ),
    'minDeliveryPoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低发货速度评分',
    ),    
    'subDeliveryPoint' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '发货速度评分百分比单位',
    ),
    'maxRateOfComplaints' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高投诉率',
    ),
    'minRateOfComplaints' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低投诉率',
    ),    
    'subRateOfComplaints' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '投诉率百分比单位',
    ),
    'maxRefundsPercent' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高退款率',
    ),
    'minRefundsPercent' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低退款率',
    ),    
    'subRefundsPercent' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '退款率百分比单位',
    ),
    'maxRefundsSpeed' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高退款速度',
    ),
    'minRefundsSpeed' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低退款速度',
    ),    
    'subRefundsSpeed' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '退款速度百分比单位',
    ),
    'maxPenaltyCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最高处罚数',
    ),
    'minPenaltyCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '最低处罚数',
    ),    
    'subPenaltyCount' => 
    array (
      'type' => 'money',
      'default' => '0',
      'required' => true,
      'label' => '处罚数百分比单位',
    ),
  ),
  'index' => 
  array (
    'ind_id' => 
    array (
      'columns' => 
      array (
        0 => 'id',
      ),
    ),
  ),
  'engine' => 'innodb',
  'version' => '$Rev: 42376 $',
);