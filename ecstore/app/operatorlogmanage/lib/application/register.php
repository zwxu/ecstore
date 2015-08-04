<?php


/**
* 安装资源文件类,查找各个APP下面的operatorlogmanage.xml 遍历
*/
class operatorlogmanage_application_register extends base_application_prototype_xml
{
	/**
	* @var string 文件名称
	*/
    var $xml='operatorlogmanage.xml';
	/**
	* @var string xml文件对应的schema文件
	*/
    var $xsd='operatorlogmanage_content';
	/**
	* @var string 路径
	*/
    var $path = 'register';
    /**
	* 迭代找到当前类实例
	* @return object 返回当前类实例
	*/
    public function current(){
        $this->current = $this->iterator()->current();
        return $this;
    }
    /**
	* 安装资源数据
	* @access final
	*/
    final public function install()
    {
        //$this->target_app->app_id;
        $data = $this->current;
        $data['app'] = $this->target_app->app_id;
        app::get('operatorlogmanage')->model('register')->insert($data);
    }//End Function

    /**
	* 卸载资源数据 register表中对应APP的数据删除
	* @param string $app_id appid
	*/
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('operatorlogmanage')->model('register')->delete(array('app'=>$app_id));
    }

}//End Class