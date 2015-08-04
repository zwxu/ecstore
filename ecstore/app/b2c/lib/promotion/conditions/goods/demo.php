<?php

 
class b2c_promotion_conditions_goods_demo{
    var $tpl_name = "商品促销模板demo";

    // type = html
    var $tpl_type = 'html';
    function getConfig($aData = array()) {
        $___b2c=app::get('b2c')->_('商品名称包含');
        return <<<EOF
$___b2c
<input type="hidden" name="conditions[type]" value="b2c_sales_goods_aggregator_combine" />
<input type="hidden" name="conditions[aggregator]" value="all" />
<input type="hidden" name="conditions[value]" value="1" />

<input type="hidden" name="conditions[conditions][0][type]" value="b2c_sales_goods_item_goods" />
<input type="hidden" name="conditions[conditions][0][attribute]" value="goods_name" />
<input type="hidden" name="conditions[conditions][0][operator]" value="()" />
<input type="text" name="conditions[conditions][0][value]" value="{$aData['conditions'][0]['value']}" />
EOF;
    }

/*
    // type = config
    // 品牌相关
    var $tpl_type = 'config';
    function getConfig($aData = array()) {
        return array('conditions'=>array('brand_id'));
    }
    */

    /*
    // type = auto
    var $tpl_type = 'auto';
    function getConfig($aData = array()) {
        return <<<EOF
<script>
var showConditions = function(o){
   $(o).getNext().setStyles({'display':'block'});$(o).setStyles({'display':'none'});
}
var makeConditions = function(o){
       var position = ($(o).getParent('li').getPrevious() == null)? 0 : (parseInt($(o).getParent('li').getPrevious().get('vposition')) + 1);
       var data = 'condition='+$(o).value+'&path='+$(o).get('vpath')+'&level='+$(o).get('vlevel')+'&position='+position;
       new Request({url:'index.php?ctl=promotion_goods&act=conditions',data:data,onComplete:function(res){
           var obj = new Element('li',{'vposition':position});
           obj.innerHTML = res;
           obj.inject($(o).getParent('li'),'before');

           $(o).getParent('span').setStyles({'display':'none'});
           $(o).getParent('span').getPrevious().setStyles({'display':'block'});
           $(o).selectedIndex = 0;
       }}).send();
}
</script>
EOF;
    }*/
}
?>
