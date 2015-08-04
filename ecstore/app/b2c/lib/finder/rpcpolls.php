<?php

 

class b2c_finder_rpcpolls {
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    var $column_editbutton = '操作';
    public $column_editbutton_order = COLUMN_IN_HEAD;
    public function column_editbutton($row)
    {
        if ($row['status'] != 'succ')
            $str_operation = '<a icon="sss.ccc" target="{onComplete:function(){if (finderGroup&&finderGroup[\'' . $_GET['_finder']['finder_id'] . '\']) finderGroup[\'' . $_GET['_finder']['finder_id'] . '\'].refresh();}}" href="index.php?app=b2c&ctl=admin_datarelation&act=re_request&p[0]=' . $row['id'] . '&p[1]=' . $row['calltime'] . '" label="添加订单">
                <span><!--todo ICON-->' . app::get('b2c')->_('重新请求') . '</span></a>';
        else
            $str_operation = "";

        return $str_operation;
    }
}