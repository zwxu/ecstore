<?php


function theme_widget_goodscat_show(&$setting,&$render){

      $data = b2c_widgets::load('GoodsCat')->getGoodsCatMap('',true); //新数据接口
      return $data;
                                        
    }
?>
