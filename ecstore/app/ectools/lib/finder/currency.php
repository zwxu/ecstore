<?php

 
/**
 * 货币finder下拉的操作列
 * 
 * @version 0.1
 * @package ectools.lib.finder
 */
class ectools_finder_currency extends desktop_controller{
	/**
	 * @var 货币详细列说明
	 */
    var $detail_currency = '货币信息';
	
    /**
     * @param object 当前app对象
     * @return null
     */
    public function __construct($app){
        $this->app = $app;
    }
    
    /**
     * 详细页面的载入与提交修改
     * @params int - currency id
     * @return null but echo html
     */
    public function detail_currency($id)
    {
        $currency = $this->app->model('currency');
        
        if ($_POST)
        {
            $this->begin("index.php?app=ectools&ctl=currency&act=index");
            $_POST['cur_id'] = $id;
            if (isset($_POST['cur_default']) && $_POST['cur_default'] == 'true')
                $currency->set_currency_default();
            $result = $currency->save($_POST);
            $this->end($result, app::get('ectools')->_('货币修改成功！'));
        }
        else
        {
            /*$this->ui = new base_component_ui($this);
            $cur = $currency->dump($id);
            $html = $this->ui->form_start();

            $html .= $this->ui->form_input(array('title'=>'货币:','type'=>'select','vtype'=>'required'
                        ,'onchange'=>'var str=controller.options[controller.selectedIndex].innerHTML;$(\'cur_sign\').value=str.substring(0,str.indexOf(\' \'))'
                        ,'required'=>true,'name'=>'cur_code','options'=>$currency->getSysCur(false,$cur['cur_code']),'value'=>$cur['cur_code']
                        , 'onbeforeactivate'=>' return false ','onfocus'=>'this.blur();','onmouseover'=>'this.setCapture();this.releaseCapture();'
                        ));
                        
            $html .= $this->ui->form_input(array('title'=>'货币名称:','vtype'=>'required','required'=>true,'name'=>'cur_name','value'=>$cur['cur_name']));

            $html .= $this->ui->form_input(array('title'=>'货币符号:',
                'style'=>'font-size:18px;width:50px;text-align:center;padding:0'
            ,'size'=>3,'required'=>true,'id'=>'cur_sign','name'=>'cur_sign','value'=>$cur['cur_sign']));

            $html .= $this->ui->form_input(array('title'=>'汇率:','vtype'=>'required&&number','required'=>true,'name'=>'cur_rate','value'=>$cur['cur_rate']));
            $html .= $this->ui->form_input(array('title'=>'默认货币:', 'type'=>'radio', 'options' => array('false'=>'不是默认', 'true'=>'默认'), 'value'=>$cur['cur_default'], 'required'=>true,'name'=>'cur_default'));
            $html .= $this->ui->form_end();
            echo $html;*/            
            $render = $this->app->render();
            $cur = $currency->dump($id);
            $render->pagedata['curs_name'] = $cur['cur_sign'] . ' ' . $cur['cur_name'];;
            $render->pagedata['cur_id'] = $id;    
            $render->pagedata['cur_code'] = $cur['cur_code'];
            $render->pagedata['cur_name'] = $cur['cur_name'];
            $render->pagedata['cur_sign'] = $cur['cur_sign'];
            $render->pagedata['cur_rate'] = $cur['cur_rate'];
            $render->pagedata['cur_default'] = $cur['cur_default'];
            
            return $render->fetch('currency/view_cur.html');
        }
    }
	
    /**
     * @var finder编辑列说明
     */
	public $column_editbutton = '编辑';
	/**
	 * 编辑列的具体实现
	 * @param array 每一行数据
	 * @return string finder修改列的html
	 */
	public function column_editbutton($row)
    {
        return '<a href="index.php?app=ectools&ctl=currency&act=showEdit&cur_id='.$row['cur_id'].'&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&finder_id='.$_GET['_finder']['finder_id'].'" target="dialog::{title:\''.app::get('ectools')->_('编辑货币信息').'\',width:700,height:300}"><span>'.app::get('ectools')->_('编辑').'</span></a>';
    }
}
