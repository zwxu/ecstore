<?php
/**
 * 商品满数量优惠后台设置
 */
class proqgoods_conditions_goods_goodsofquantity {
    /**
     * @var string $tpl_name
     */
    var $tpl_name = "指定商品购买量满X,自定义优惠";
    /**
     * @var string $tpl_type 类型 分为 html(写死的html) | config(可选项的) | auto(全开放的配置)
     */
    var $tpl_type = 'config';
    #var $type = 'goods';


    /**
     * 获取设置配置信息
     *
     * @param array $aData 暂未使用
     * @return array
     */
    public function getConfig($aData = array()) {
        /////////////////////////////////////// conditions ////////////////////////////////////////////////
        $aConfig['conditions']['type'] = 'html';
        $aConfig['conditions']['info'] = '';/*array(
                                              'conditions'=> array(
                                                   array(
                                                              'type'=> 'b2c_sales_order_aggregator_found',
                                                              'aggregator'=> 'all',
                                                              'conditions'=> array(
                                                                               0 => array(
                                                                                       'type' =>'b2c_sales_order_item_goods',
                                                                                       'attribute' => 'goods_goods_id'
                                                                                     ),
                                                                                1 => array(
                                                                                   'type' =>'b2c_sales_order_item_subgoods',
                                                                                   'attribute' => 'subgoods_quantity'
                                                                                 ),
                                                                    ),
                                                             )
                                                  ),
                                            );*/


        ///////////////////////////////// action_conditions ///////////////////////////////////////////////
        $aConfig['action_conditions']['type'] = 'config';
        $aConfig['action_conditions']['info'] = array(
                                                'aggregator'=>'all',
                                                'value'=>1,
                                                'conditions'=>array(
                                                                array(
                                                                    'type'=>'b2c_sales_order_item_goods',
                                                                    'attribute'=>'goods_goods_id'
                                                                )
                                                              )
                                             );
        #$aConfig['action_conditions']['type'] = 'auto';
        #$aConfig['action_conditions']['info'] = array();
        #$aConfig['action_conditions']['type'] = 'html';
        #$aConfig['action_conditions']['info'] = '';
        return $aConfig;
    }
     /**
      * 获取后台设置中的模板
      *
      * @param array $aData 表单默认数据
      * @param string $type 模板类型：'conditions'|'action_conditions'
      * @return string
      */
     public function getTemplate($aData = array(),$type = 'conditions') {


        switch( $type ) {
            case 'conditions' :
                return $this->_get_conditions_html($aData);
            case 'action_conditions':
            /**/
                return '<input type="hidden" name="action_conditions[type]" value="b2c_sales_order_aggregator_item" />
    <input type="hidden" name="action_conditions[aggregator]" value="all" />
    <input type="hidden" name="action_conditions[value]" value="1" />
    <input type="hidden" name="action_conditions[conditions][0][type]" value="b2c_sales_order_item_goods" />
    <input type="hidden" name="action_conditions[conditions][0][attribute]" value="goods_price" />
    <input type="hidden" name="action_conditions[conditions][0][operator]" value=">=" />
    <input type="hidden" name="action_conditions[conditions][0][value]" value="0" />';
    /**/
        }
    }


    /**
     * 获取条件模板
     *
     * @param array $aData 表单默认数据
     * @return string
     */
    private function _get_conditions_html($aData) {
        #$this->conditions[conditions][0][conditions][0][value]
        $o = kernel::single('b2c_frontpage');
        $o->pagedata['object'] = 'goods';

       

        if((! $aData['storeid_filter']) &&   $aData['store_id']){
             $store_id = explode(',',$aData['store_id']);

             foreach($store_id as $key => $val) {
                 if ($val == '') unset($store_id[$key]);
             }
             sort($store_id);
             $aData['storeid_filter'] =array('store_id'=> $store_id);

        }

        $o->pagedata['storeid_filter'] = $aData['storeid_filter'];
        $o->pagedata['isfront'] = $aData['isfront'];

        $o->pagedata['name']  = 'conditions[conditions][0][conditions][0][value]';
        $o->pagedata['value'] = $aData['conditions'][0]['conditions'][0]['value'];
        $html = $o->fetch('admin/sales/dialog/index_radio.html');

        return <<<EOF
        	<font color="red">商品库存非小数型库存时将对库存向下取整（填写1.5则取1）</font>
            <div>
             <div>
              <input name="conditions[type]" value="b2c_sales_order_aggregator_combine" type="hidden"/>
              <input name="conditions[aggregator]" type="hidden" value="all" />
              <input name="conditions[value]" type="hidden" value="1" />
              <input name="conditions[conditions][0][type]" value="b2c_sales_order_aggregator_subselect" type="hidden"/>
              <input type="hidden" name="conditions[conditions][0][attribute]" value="subgoods_quantity" />
              商品订购数量<input type="hidden" name="conditions[conditions][0][operator]" value="&gt;="/>
              <input name="conditions[conditions][0][value]" value="{$aData['conditions'][0]['value']}" vtype="required&&number" type="text"/>
              <input type="hidden" value="b2c_sales_order_item_goods" name="conditions[conditions][0][conditions][0][type]" />
              <input type="hidden" value="goods_goods_id" name="conditions[conditions][0][conditions][0][attribute]" />
            商品：<input type="hidden"  name="conditions[conditions][0][conditions][0][operator]"  value="()" /><div style="width: 85%; margin-left: 130px; margin-top: -22px;">
        {$html}
        </div>

EOF;


/*
        return <<<EOF
<div><div><input type="hidden" value="b2c_sales_order_aggregator_combine" name="conditions[type]"><input type="hidden"  name="conditions[aggregator]" value="all" /><input type="hidden" name="conditions[value]" value="1" /></div><ul><li vposition="0"><div><input type="hidden" value="proqgoods_sales_order_aggregator_found" name="conditions[conditions][0][type]"><input type="hidden"  name="conditions[conditions][0][aggregator]" value="all" /><input type="hidden" name="conditions[conditions][0][value]" value="1" /></div><ul><li vposition="0"><input type="hidden" value="b2c_sales_order_item_goods" name="conditions[conditions][0][conditions][0][type]"><input type="hidden" value="goods_goods_id" name="conditions[conditions][0][conditions][0][attribute]">商品：<input type="hidden"  name="conditions[conditions][0][conditions][0][operator]"  value="()" /><div style="width: 85%; margin-left: 130px; margin-top: -22px;">
                {$html}
</div>
</li><li vposition="1"><input type="hidden" value="b2c_sales_order_item_subgoods" name="conditions[conditions][0][conditions][1][type]"><input type="hidden" value="subgoods_quantity" name="conditions[conditions][0][conditions][1][attribute]">商品购买数量满<input type="hidden"  name="conditions[conditions][0][conditions][1][operator]" value="&gt;=" /><input type="text" vtype="required" value="{$aData['conditions'][0]['conditions'][1]['value']}" name="conditions[conditions][0][conditions][1][value]"></li></ul></li></ul></div>

EOF;
*/


    }

}
