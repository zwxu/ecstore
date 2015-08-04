<?php

 
class goods_type extends PHPUnit_Framework_TestCase
{
    /*
     * author guzhengxiao
     */
    public function setUp()
    {
        $this->model = app::get('b2c')->model('goods_type');
    }

    public function testInsert(){

        $saveData = array(
            'is_physical' => 1,
            'setting' => array(
                'use_brand' => 1,
                'use_props' => 1,
                'use_params' => 1,
                'use_minfo' => 1
            ),
            'name' => 'test测试类型',
            'alias' => 'test测试类型别名1|test测试类型别名2',
            'brand' => array(
                array( 'brand_id' => 4 ),
                array( 'brand_id' => 5 ),
                array( 'brand_id' => 6 ),
                array( 'brand_id' => 8 ),
                array( 'brand_id' => 14 )
            ),
            'floatstore' => 0,
            'props' => array(
                1 => array(
                    'name' => '选择属性',
                    'alias' => '选择属性别名1|选择属性别名2',
                    'type' => 'select',
                    'show' => 'on',
                    'ordernum' => '',
                    'search' => 'nav',
                    'goods_p' => 1
                ),
                21 => array(
                    'name' => '输入属性',
                    'alias' => '输入属性别名1|输入属性别名2',
                    'type' => 'input',
                    'show' => 'on',
                    'ordernum' => '',
                    'search' => 'input',
                    'goods_p' => 21,
                ),
            ),
            'spec' => array(
                array(
                    'spec_id' => 1,
                    'spec_style' => 'flat'
                ),
                array(
                    'spec_id' => 2,
                    'spec_style' => 'select'
                )
            ),
            'params' => array(
                '参数组1' => array(
                    '参数11' => '别名111|别名112',
                    '参数12' => '别名121|别名122'
                ),
                '参数组2' => array(
                    '参数21' => '别名211|别名212',
                    '参数22' => '别名221|别名222'
                ),
            ),
            'minfo' => array(
                array(
                    'label' => '必填1',
                    'name' => 'M09e43c03f7339dea927f3a8c325c85f7',
                    'type' => 'select',
                    'options' => array(
                        '必1',
                        '必2',
                        '必3'
                    )
                ),
                array(
                    'label' => '必填2',
                    'name' => 'Mf15e2c018bf67c25d15f2c3e5e20138d',
                    'type' => 'input'
                ),
                array(
                    'label' => '必填3',
                    'name' => 'M0afcc24fe7429f175f7d013722207eb7',
                    'type' => 'text'
                )
            )
        );
        $rs = $this->model->save( $saveData );
        $this->assertEquals($rs,true); //添加成功
        $typeId = $saveData['type_id'];
        if( $rs ){
            //品牌绑定关系
            $brandtype = $this->model->db->select( 'SELECT brand_id,type_id FROM sdb_b2c_type_brand WHERE type_id = '.intval($saveData['type_id']).' ORDER BY brand_id ASC ' );
            $this->assertEquals( $brandtype,$saveData['brand'] );
//
            
//            $dumpData = $this->model( $typeId,'*','default' );
//            $dumpData['brand'] = array_values( $dumpData['brand'] );
//            $dumpData['spec'] = array_values( $dumpData['spec'] );
            
        }
    }

}
