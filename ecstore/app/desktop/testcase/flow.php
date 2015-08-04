<?php

 
class flow extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->model = app::get('desktop')->model('flow');
    }

    public function testInsert(){
        $data = array(
                'flow_from'=>'system',
                'subject'=>'信息123',
                'flow_desc'=>'霜之哀伤',
                'body'=>'山东蓝翔高级技工学校（原山东蓝翔技校）,蓝翔技校，
            以办学早、规模大、专业多、设备全、质量高、
            管理严而驰名中华职业教育。设有数控、厨师、汽修、挖掘机、美容',
                'flow_type'=>'txtplain',
            );
        $this->model->write_op(1,$data);
    }

    public function testBordcast(){
        $data = array(
                'flow_from'=>'system',
                'subject'=>'信息123',
                'flow_desc'=>'广播信息',
                'body'=>'山东蓝翔高级技工学校（原山东蓝翔技校）,蓝翔技校，
            以办学早、规模大、专业多、设备全、质量高、
            管理严而驰名中华职业教育。设有数控、厨师、汽修、挖掘机、美容',
                'flow_type'=>'txtplain',
            );
        $this->model->write_role(0,$data);
    }
    
    public function testMarkStar(){        
        $this->model->system->op_id = 1;
        $this->model->mark_star(9,'true');
        $ret = $this->model->list_flow('starred');
        var_dump($ret);
        $this->model->mark_star(9,'false');
        $ret = $this->model->list_flow('starred');
        var_dump($ret);
    }

   /* public function test_operator(){
        $data = $this->model->mark_read(range(1,30),1);
        var_dump($data);
    }*/

    /*public function test_operator(){
        $data = $this->model->get_for_operator(1);
        var_dump($data);
    }*/

}
