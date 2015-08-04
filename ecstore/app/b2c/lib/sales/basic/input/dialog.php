<?php

 
class b2c_sales_basic_input_dialog
{
    public $type = 'dialog';
    public function create($aData, $object='') {
        // todo 2010-04-13 14:35 wubin
        // 通过$aStandard['default'] $aStandard['attribute'] 取出存在值
        // 要和dialog返回来的值做好对接 返回的数据加在原有数据后面
        // 过滤掉已存在的数....

        //$url = app::get('desktop')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'admin_sales_goods', 'act'=>'goods_dialog', 'arg0'=>urlencode(serialize($table_info))));

/**
        ob_start();
        app::get('b2c')->controller('admin_sales_goods')->get_defaine_dialog($aData, $table_info);
        $html = ob_get_contents();
        ob_end_clean();
//*/

        /**/
        ob_start();
        if(strpos($object,'@')>0){
        }else{
            $object .='@b2c';
         }
        $ctlbase= kernel::single('base_controller');
        $ctlbase->pagedata['object'] = $object;
		$ctlbase->pagedata['mdl_object'] = 'object='.$object;
        $ctlbase->pagedata['value'] = $aData['default'];
        $ctlbase->pagedata['name'] = $aData['name'];
        $ctlbase->pagedata['isfront'] = $aData['isfront']; 

        $ctlbase->display("admin/sales/dialog/index.html",'b2c');

                  
        $html = ob_get_clean();  
        return $html;
        //*/
        
        return <<<EOF
            <a href="{$url}" onclick="return doTemplate(this, '{$aData['name']}[]');" >筛选</a>
            <div class="gridlist rows-body" id="div-dialog-data" >{$html}</div>
EOF;
    }
    
}
