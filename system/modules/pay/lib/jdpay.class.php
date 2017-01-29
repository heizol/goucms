<?php
include dirname(__FILE__).DIRECTORY_SEPARATOR."jdpay".DIRECTORY_SEPARATOR."common".DIRECTORY_SEPARATOR."SignUtil.php";					
			
include dirname(__FILE__).DIRECTORY_SEPARATOR."jdpay".DIRECTORY_SEPARATOR."common".DIRECTORY_SEPARATOR."ConfigUtil.php";						
include dirname(__FILE__).DIRECTORY_SEPARATOR."jdpay".DIRECTORY_SEPARATOR."api".DIRECTORY_SEPARATOR."jdpay_submit.class.php";				
System::load_sys_fun('global');

class Jdpay{
	private $config;
	private $urlencode;
	
	//主入口
	public function config($config=null){
		
		$this->config = $config;
		$this->config_jsdz();
	}

	//及时到账
	private function config_jsdz(){

		$this->db=System::load_sys_class('model');
		
		$param = array(
					//"serverUrl" 		 => ConfigUtil::get_val_by_key('serverPayUrl'),
					"version" 			 => "1.1.5",
					"token" 			 => "", 		
					"merchantNum" 		 => ConfigUtil::get_val_by_key('merchantNum'),
					"merchantRemark" 	 => $this->config['shouname'],
					"tradeNum" 			 => $this->config['code'],
					"tradeName" 		 => $this->config['title'],
					"tradeDescription" 	 => $this->config['title'],
					"tradeTime" 		 => date('Y-m-d H:i:s', time()),
					"tradeAmount" 		 => $this->config['money'] * 100,
					//"tradeAmount" 		 => "1",
					"currency" 			 => "CNY",
					"successCallbackUrl" => $this->config['ReturnUrl'],
					
					"notifyUrl" 		 => $this->config['NotifyUrl'],
					"ip" 	             => $_SERVER["REMOTE_ADDR"]
			);

		$sign = SignUtil::signWithoutToHex($param);

		$param["merchantSign"] = $sign;
		
		
		
	
		$jdpaySubmit = new Jdpaysubmit($param);
		$this->url = $jdpaySubmit->buildRequestForm($param,'POST','submit');
	}

	//发送
	public function send_pay(){

		 echo  $this->url;
		 exit;
	}
}