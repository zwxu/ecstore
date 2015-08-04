<?php

 
class queue extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
          $this->model = app::get('base')->model('queue');
    }

    public function testEventregister(){
        $data = array(
            'queue_title'=>app::get('base')->_('测试任务'),
            'start_time'=>time(),
            'params'=>array(
                'filter'=>$filter,
                'watermark'=>$_POST['watermark'],
                'size'=>$_POST['size'],
            ),
            'worker'=>'image_rebuild.run',
        );
        $this->model->insert($data);
    }
   
    public function atestRuntask(){
        $_SERVER['SERVER_ADDR'] = 'localhost';
        $this->model->runtask(1);
    }
    
    public function testRun(){
        $data = array(
            'queue_title'=>app::get('base')->_('测试任务'),
            'status'=>'paused',
            'start_time'=>time(),
            'params'=>array(
                'filter'=>$filter,
                'watermark'=>$_POST['watermark'],
                'size'=>$_POST['size'],
            ),
            'worker'=>'image_rebuild.run',
        );
        $this->model->insert($data);
        unset($data['queue_id']);
        $this->model->insert($data);
    }

}
