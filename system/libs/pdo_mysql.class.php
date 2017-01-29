<?php 


/**
 *  pdo_mysql.class.php ���ݿ�ʵ����
 *
 * @copyright			(C) 2005-2010 BUSY
 * @license				
 * @lastmodify			2012-6-1
 */
 
 
final class pdo_mysql {

	/**
	  * ���ݿ�������Ϣ
	  */
	 private $config;
	 
	 /**
	  * ���ݿ�������Դ���
	  */
	 private $link;
	 
	 /**
	  *	���һ�β�ѯ����Դ���
	  */
	 public $lastresult;
	 
	/**
	 *  ͳ�����ݿ��ѯ����
	 */
	public $querycount = 0;
	
	
	/**
	 * ������ʵ��
	 */
	static private $mysqlobj=array();
	
	
	private function __construct($configs){
		
		$this->config=$configs;	
		$this->connect();	
	}
	
	public function __destruct(){	
		$this->close();
	}
	
	public function close() {
		if (is_resource($this->link)) {
			$this->link = null;
		}
	}
	
	
	private function connect(){

		if(empty($this->config['hostname']) || empty($this->config['username'])){
			$this->DisplayError('Can not Connect to MySQL server',"hook_mysql_install");
		}		
		try {
			$db  =  new PDO('mysql:host=localhost;port=3306;dbname=yougoucms','root', '');		
		}catch (PDOException $e) {
			echo  "Error!: " . $e->getMessage() . "<br/>";			
			exit;
		}
		
		$db->exec('set names utf8');
		$this->link = $db;
	}	
	
	public function DisplayError($message='',$hook=''){
	
		if(!empty($hook)){$hook($message);}
		if($this->config['debug']){
			$html ='<b>MySQL Error: </b>'.$this->GetError().'<br/>';
			$html.='<b>MySQL Errno: </b>'.$this->GetErrno().'<br/>';
			$html.='<b>MySQL Message: </b>'.$message;
			echo "<div style='border:1px dotted #ccc; padding:5px; font-size:12px; clear:both;width:100%;height:auto;'>".$html."</div>";
			exit;
		}else{
			return false;
		}	
	}
	
	final static public function GetObject($configs=array('hostname'=>'','database'=>'')){
			$db=$configs['hostname'].$configs['database'];			
			if(!isset(self::$mysqlobj[$db])){				
				if(!is_array($configs)){
					$this->DisplayError("The configuration file is not an array");
				}	
				$C=__CLASS__;	
					
				self::$mysqlobj[$db]=new $C($configs);				
			}
			return self::$mysqlobj[$db];
	}
	
	public function execute($sql) {
	
		$stmt = $this->link->prepare($sql);
		$stmt->execute();
		
		if($this->link->errorCode() != '00000'){
			print_r($this->link->errorInfo()); 
		}

		$this->querycount++;
		$this->lastresult = $this->link->lastinsertid();
		return $this->lastresult;
		//$this->displayerror($sql);
		
	}	
	
		


}