<?php

 
class meta extends PHPUnit_Framework_TestCase{
    static $id;
    
    public function setUp()  {
        $this->db = kernel::database();   
        $this->model = app::get('dbeav')->model('meta_register');
        if(!$this->_is_exist_table('sdb_dbeav_meta_register') ){        
            $table = new base_application_dbtable;
            $table->detect('dbeav','meta_register')->install();   
        }
        if(!$this->_is_exist_table('sdb_b2c_members') ){        
            $table = new base_application_dbtable;
            $table->detect('b2c','members')->install();   
        }
        $this->obj_member = app::get('b2c')->model('members');
  
    }
    
    public function _is_exist_table($tbname){
        $tables = $this->db->select('show tables');
        foreach(array_values($tables) as $val){
            $tblist[]  = current($val);
        }
        return in_array($tbname,$tblist);
    }    

    public function tearDown(){
        
    }
 
    public function testRegister(){
        //和dbschema里的*列定义*一样， 但是没有pkey,extra,title定义
        $column = array('qq'=>array (
          'type' => 'number',
          'required' => false,
          'label' => __(' 短值测试列'),
          'width' => 110,
          'editable' => false,
        ),); 

        $this->obj_member->meta_register($column);
    }
    
    public function testInsert(){
        $data['member_lv_id'] = 1;
        $data['uname']= 'oooo'.time();
        $data['passwd'] = 'shopex';
        $data['email'] = 'xxxx@sss.com';
        $data['qq'] = '393161358';
        $this->obj_member->insert($data);   
        die();
        self::$id = $data['member_id'];
    }
    
    /*
    public function testGetList(){
        $ret = $this->obj_member->getList("member_lv_id",array('member_id'=>self::$id));
        var_dump($ret);
        $ret = $this->obj_member->getList("qq",array('member_id'=>self::$id));
        var_dump($ret);
        $ret = $this->obj_member->getList("member_lv_id,qq",array('member_id'=>self::$id));
        var_dump($ret);
        $ret = $this->obj_member->getList("member_id,member_lv_id,qq",array('member_id'=>self::$id));
        var_dump($ret);        
    }
    
    public function testUpdate(){
        $ret = $this->obj_member->getList('qq',array('member_id'=>self::$id));
        var_dump($ret);
        $filter = array('member_id'=>self::$id);
        $data['qq'] = '1000';
        $this->obj_member->update($data,$filter);
        $ret = $this->obj_member->getList('qq',array('member_id'=>self::$id));
        var_dump($ret);
    }
    
    public function testFilter(){
        $filter = array('qq'=>'393161358');
        $ret = $this->obj_member->getList('member_id,qq',$filter);
        var_dump($ret);
    }

    public function testFilterHasMeta(){
        $filter = array('qq'=>'393161358');
        $this->obj_member->delete($filter);
        $ret = $this->obj_member->getList('member_id,qq',$filter);
        var_dump($ret);
    }
    
    public function testDelete(){
        $ret = $this->obj_member->getList('qq',array('member_id'=>self::$id));
        var_dump($ret);
        $filter = array('member_id'=>self::$id);
        $this->obj_member->delete($filter);
        $ret = $this->obj_member->getList('*',array('member_id'=>self::$id));
        var_dump($ret);
    }
    */
    
}
