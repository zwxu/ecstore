<?php


class b2c_banks_info{

	/**
     * 构造方法
     * @param object app
     */
    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('desktop')->router();
    }//End

	public function getBank(){
		//begin 银盛支付银行信息
		include_once(ROOT_DIR.'/config/configBank.php');
		$person_bank = eval(PERSON_BANK);
		$credit_bank = eval(CREDIT_BANK);
		$business_bank = eval(BUSINESS_BANK);
		$bankInfo['person_bank'] = $person_bank;
		$bankInfo['credit_bank'] = $credit_bank;
		$bankInfo['business_bank'] = $business_bank;
		//end
		return $bankInfo;
	}
}