<?php
defined('G_IN_SYSTEM')or exit('No permission resources.');
System::load_app_class('base','member','no');
System::load_app_fun('user','go');
class cart extends base {
    private $Cartlist;

    public function __construct() {
        $this->Cartlist = _getcookie('Cartlist');
        $this->db = System::load_sys_class("model");

    }
    //购物车商品列表
    public function cartlist(){
        $webname=$this->_cfg['web_name'];
        $Mcartlist=json_decode(stripslashes($this->Cartlist),true);
        //echo "<pre>";
        //print_r($Mcartlist);
        $shopids='';
        if(is_array($Mcartlist)){
            foreach($Mcartlist as $key => $val){
                $shopids.=intval($key).',';
            }
            $shopids=str_replace(',0','',$shopids);
            $shopids=trim($shopids,',');

        }
        //echo $shopids;
        $shoplist=array();
        if($shopids!=NULL){
            $shoparr=$this->db->GetList("SELECT * FROM `@#_shoplist` where `id` in($shopids)",array("key"=>"id"));
        }
        if(!empty($shoparr)){
            foreach($shoparr as $key=>$val){
                if($val['q_end_time']=='' || $val['q_end_time']==NULL){
                    $shoplist[$key]=$val;
                    $Mcartlist[$val['id']]['num']=$Mcartlist[$val['id']]['num'];
                    $Mcartlist[$val['id']]['shenyu']=$val['shenyurenshu'];
                    $Mcartlist[$val['id']]['money']=$val['yunjiage'] * $Mcartlist[$val['id']]['num'];

                }
            }
            _setcookie('Cartlist',json_encode($Mcartlist),'');
        }

        $MoenyCount=0;
        $Cartshopinfo='{';
        if(count($shoplist)>=1){
            foreach($Mcartlist as $key => $val){
                $key=intval($key);
                if(isset($shoplist[$key])){
                    $shoplist[$key]['cart_gorenci']=$val['num'] ? $val['num'] : 1;
                    $MoenyCount+=$shoplist[$key]['yunjiage']*$shoplist[$key]['cart_gorenci'];
                    $shoplist[$key]['cart_xiaoji']=substr(sprintf("%.3f",$shoplist[$key]['yunjiage']*$val['num']),0,-1);
                    $shoplist[$key]['cart_shenyu']=$shoplist[$key]['zongrenshu']-$shoplist[$key]['canyurenshu'];
                    $Cartshopinfo.="'$key':{'shenyu':".$shoplist[$key]['cart_shenyu'].",'num':".$val['num'].",'money':".$shoplist[$key]['yunjiage']."},";
                }
            }
        }

        $shop=0;

        if(!empty($shoplist)){
            $shop=1;
        }
        //echo "<pre>";
        //print_r($Mcartlist);
        $MoenyCount=substr(sprintf("%.3f",$MoenyCount),0,-1);
        $Cartshopinfo.="'MoenyCount':$MoenyCount}";
        include templates("mobile/cart","cartlist");
    }


    //支付界面
    public function pay(){
        $webname=$this->_cfg['web_name'];
        parent::__construct();
        if(!$member=$this->userinfo){
            header("location: ".WEB_PATH."/mobile/user/login");
        }
        $Mcartlist=json_decode(stripslashes($this->Cartlist),true);
        $shopids='';
        if(is_array($Mcartlist)){
            foreach($Mcartlist as $key => $val){
                $shopids.=intval($key).',';
            }
            $shopids=str_replace(',0','',$shopids);
            $shopids=trim($shopids,',');

        }
        $shoplist=array();
        if($shopids!=NULL){
            $shoplist=$this->db->GetList("SELECT * FROM `@#_shoplist` where `id` in($shopids)",array("key"=>"id"));
        }
        $MoenyCount=0;
        if(count($shoplist)>=1){
            foreach($Mcartlist as $key => $val){
                $key=intval($key);
                if(isset($shoplist[$key])){
                    $shoplist[$key]['cart_gorenci']=$val['num'] ? $val['num'] : 1;
                    $MoenyCount+=$shoplist[$key]['yunjiage']*$shoplist[$key]['cart_gorenci'];
                    $shoplist[$key]['cart_xiaoji']=substr(sprintf("%.3f",$shoplist[$key]['yunjiage']*$val['num']),0,-1);
                    $shoplist[$key]['cart_shenyu']=$shoplist[$key]['zongrenshu']-$shoplist[$key]['canyurenshu'];
                }
            }
            $shopnum=0;  //表示有商品
        }else{
            _setcookie('Cartlist',NULL);
            //_message("购物车没有商品!",WEB_PATH);
            $shopnum=1; //表示没有商品
        }

        //总支付价格
        $MoenyCount=substr(sprintf("%.3f",$MoenyCount),0,-1);
        //会员余额
        $Money=$member['money'];
        //商品数量
        $shoplen=count($shoplist);

        $fufen = System::load_app_config("user_fufen",'','member');
        if($fufen['fufen_yuan']){
            $fufen_dikou = intval($member['score'] / $fufen['fufen_yuan']);
        }else{
            $fufen_dikou = 0;
        }
        $paylist = $this->db->GetList("select * from `@#_pay` where `pay_start` = '1' and `pay_mobile`='1'");

        $_SESSION['submitcode'] = $submitcode = uniqid();
//         require_once G_SYSTEM . "/modules/pay/lib/wxpay_lib/WxPay.Api.php";
//         require_once G_SYSTEM. "/modules/pay/lib/wxpay_lib/WxPay.JsApiPay.php";
//         require_once G_SYSTEM . '/modules/pay/lib/pay.fun.php';
        
//         $dingdancode = pay_get_dingdan_code('C');		//订单号
//         $input = new WxPayUnifiedOrder();
//         $input->SetBody("边吃边抢--精彩无限");
//         $input->SetAttach("夺宝");
//         $input->SetOut_trade_no($dingdancode);
//         $input->SetTotal_fee($MoenyCount * 100);
//         $input->SetTime_start(date("YmdHis"));
//         $input->SetTime_expire(date("YmdHis", time() + 600));
//         $input->SetGoods_tag("夺宝");
//         $input->SetNotify_url("http://duobao.joinear.com/?/mobile/mobile/wx_notify");
//         $input->SetTrade_type("JSAPI");
//         $input->SetOpenid($_SESSION['open_id']);
//         $order = WxPayApi::unifiedOrder($input);
//         $tools = new JsApiPay();
//         $jsApiParameters = $tools->GetJsApiParameters($order);
        $jsApiParameters = '';
        //获取共享收货地址js函数参数
        $editAddress = '';
        include templates("mobile/cart","payment");
    }

    //开始支付
    public function paysubmit(){
        $webname=$this->_cfg['web_name'];

        header("Cache-control: private");
        parent::__construct();
        if(!$this->userinfo){
            header("location: ".WEB_PATH."/mobile/user/login");
            exit;
        }

        session_start();

        $checkpay=$this->segment(4);//获取支付方式  fufen   money  bank
        $banktype=$this->segment(5);//获取选择的银行 CMBCHINA  ICBC CCB
        $money=$this->segment(6);   //获取需支付金额
        $fufen=$this->segment(7);   //获取学分
        $submitcode1=$this->segment(8);   //获取SESSION
        $uid = $this->userinfo['uid'];
        if(!empty($submitcode1)) {
            if(isset($_SESSION['submitcode'])){
                $submitcode2 = $_SESSION['submitcode'];
            }else{
                $submitcode2 = null;
            }
            if($submitcode1 == $submitcode2){
                unset($_SESSION["submitcode"]);
            }else{
                $WEB_PATH = WEB_PATH;
                _messagemobile("请不要重复提交...<a href='{$WEB_PATH}/mobile/cart/cartlist' style='color:#22AAFF'>返回购物车</a>查看");
                exit;
            }
        }else{
            $WEB_PATH = WEB_PATH;
            _messagemobile("正在返回购物车...<a href='{$WEB_PATH}/mobile/cart/cartlist' style='color:#22AAFF'>返回购物车</a>查看");
        }


        $zhifutype = $this->db->GetOne("select * from `@#_pay` where `pay_class` = '$checkpay' and `pay_start`='1' and `pay_mobile`='1'");
        $pay_checkbox=false;
        $pay_type_bank=false;
        $pay_type_id=false;

        if($checkpay == 'money'){
            $pay_checkbox=true;
        }

        if($banktype != 'nobank'){
            $pay_type_id=$banktype;
        }
        if(!empty($zhifutype)){
            $pay_type_bank = $zhifutype['pay_class'];
        }

        if(is_array($zhifutype)){
            $pay_type_id = $zhifutype['pay_id'];
        }

        /*************
        start
         *************/
        $pay=System::load_app_class('pay','pay');
        $pay->fufen = $fufen;
        $pay->pay_type_bank = $pay_type_bank;
        $ok = $pay->init($uid,$pay_type_id,'go_record');	//云购商品
        if($ok != 'ok'){
            _setcookie('Cartlist',NULL);
            _messagemobile("购物车没有商品请<a href='".WEB_PATH."/mobile/cart/cartlist' style='color:#22AAFF'>返回购物车</a>查看");
        }
        $check = $pay->go_pay($pay_checkbox);

        if(!$check){
            _messagemobile("订单添加失败,请<a href='".WEB_PATH."/mobile/cart/cartlist' style='color:#22AAFF'>返回购物车</a>查看");
        }
        if($check){
            //成功
            header("location: ".WEB_PATH."/mobile/cart/paysuccess");
        }else{
            //失败
            _setcookie('Cartlist',NULL);
            header("location: ".WEB_PATH."/mobile/mobile");
        }
        exit;
    }
    //成功页面
    public function paysuccess(){
        $webname=$this->_cfg['web_name'];
        _setcookie('Cartlist',NULL);
        include templates("mobile/cart","paysuccess");
    }
    public function addmoney(){
        parent::__construct();
        $webname=$this->_cfg['web_name'];
        $money=intval($this->segment(4));//获取充值金额
        $pay_class = safe_replace($this->segment(5)); //网络支付方式
        $banktype= safe_replace($this->segment(6));   //获取选择的银行 CMBCHINA  ICBC CCB
        $checkpay= safe_replace($this->segment(7));   //获取选择的支付方式

        if(!$this->userinfo){
            header("location: ".WEB_PATH."/mobile/user/login");
            exit;
        }
        $pay=System::load_app_class('pay','pay');
        if($pay_class != "money" && $pay_class != "fufen" && !empty($pay_class)){
            $checkpay = safe_replace($pay_class);
            $zhifutype = $this->db->GetOne("select * from `@#_pay` where `pay_class` = '$checkpay' and `pay_start`='1' and `pay_mobile`='1'");
            if(!$zhifutype){
                _messagemobile("支付方式选择不正确!");
            }
            $pay_type_id=$zhifutype['pay_id'];
            $pay->pay_type_bank = $zhifutype['pay_class'];
        }else{
            $pay_type_id=$banktype;
            $pay->pay_type_bank = $banktype;
        }
        $uid = $this->userinfo['uid'];

        $pay->init($uid,$pay_type_id,'addmoney_record',$money);
    }
}
?>