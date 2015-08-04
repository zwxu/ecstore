<?php
class b2c_ctl_admin_analysis_member extends desktop_controller{
    var $workground = 'b2c.workground.analysis';

    public function chart_view(){
        $type=$_GET['type'];
        $filter = array(
            'time_from' => $_GET['time_from'],
            'time_to' => $_GET['time_to'],
        );
        $memberObj = $this->app->model('analysis_member');
        $login_name = array();
        $saleTimes = array();
        $salePrice = array();

        if($type=='volume'){
            $data = $memberObj->getlist($cols='*', $filter, 0, 20, 'saleTimes desc');
            foreach($data as $val){
                $login_name[] = '\''.$val['login_name'].'\'';
                $saleTimes[] = $val['saleTimes'];
            }
            $categories = implode(',',$login_name);
            $volume = implode(',',$saleTimes);

            $this->pagedata['categories']='['.$categories.']';

            $this->pagedata['data']='{
                name: \''.app::get('b2c')->_('订单量').'\',
                data: ['.$volume.']}';
        }else{
            $data = $memberObj->getlist($cols='*', $filter, 0, 20, 'salePrice desc');
            foreach($data as $val){
                $login_name[] = '\''.$val['login_name'].'\'';
                $salePrice[] = $val['salePrice'];
            }
            $categories = implode(',',$login_name);
            $turnover = implode(',',$salePrice);

            $this->pagedata['categories']='['.$categories.']';

            $this->pagedata['data']='{
                name: \''.app::get('b2c')->_('订单额').'\',
                data: ['.$turnover.']}';
        }

        $this->display("admin/analysis/chart_type_column.html");
    }
}
