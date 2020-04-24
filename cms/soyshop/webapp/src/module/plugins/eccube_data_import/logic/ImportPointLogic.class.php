<?php

class ImportPointLogic extends ExImportLogicBase{
	
	private $labels = array("E-MAIL","ポイント残高");
	private $factors = array();
	
	private $type;
	private $dao;
	private $pointLogic;
	
	const POINT_PLUGIN_ID = "common_point_base";
	
	const EMAIL = 0;
	const POINT = 1;
	
	function __construct(){
		$this->setCharset("Shift_JIS");
		$this->dao = new SOY2DAO();
				
		//ポイントプラグインのインストール
		if(!SOYShopPluginUtil::checkIsActive(self::POINT_PLUGIN_ID)){
			$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
		    $logic->prepare();
		    $logic->installModule(self::POINT_PLUGIN_ID);
		    unset($logic);
		}
		
		$this->pointLogic = SOY2Logic::createInstance("module.plugins.". self::POINT_PLUGIN_ID . ".logic.PointBaseLogic");
	}
	
	function execute(){
		set_time_limit(0);

		//ファイル読み込み・削除
		$fileContent = file_get_contents($_FILES["CSV"]["tmp_name"][$this->type]);
		unlink($_FILES["CSV"]["tmp_name"][$this->type]);

		//データを行単位にばらす
		$lines = self::GET_CSV_LINES($fileContent);	//fix multiple lines
		self::setFactors(self::encodeFrom($lines[0]));
		
		//ファイルを間違えてアップロードした場合は処理を止める
		if(count($this->factors) === 0) return;
		
		unset($lines[0]);
		
		$this->dao->begin();
		foreach($lines as $line){
			//,の場合も省くように2文字未満でスルーにする
			if(strlen($line) < 2) continue;
			$values = self::explodeLine(self::encodeFrom($line));
			
			//ポイントがあるか調べる
			if((int)$values[$this->factors[self::POINT]] < 1) continue;
			
			//メールアドレスから顧客IDを取得
			$userId = self::getUserId($values[$this->factors[self::EMAIL]]);
			if($userId < 1) continue;
			
			//すでにポイントを入れたことがあるか調べてから、ポイントを挿入する
			if(self::checkInsertedPoint($userId)){
				$this->pointLogic->insert($values[$this->factors[self::POINT]], "EC CUBEからの移行分", $userId);
			}
		}
		
		$this->dao->commit();
	}
	
	private function getUserId($email){
		if(!isset($email) || strlen($email) === 0) return 0;
		
		try{
			$res = $this->dao->executeQuery("SELECT id FROM soyshop_user WHERE mail_address = :mail_address", array(":mail_address" => $email));
		}catch(Exception $e){
			return 0;
		}
		
		return (int)$res[0]["id"];
	}
	
	/**
	 * EC CUBEからダウンロードしてきたCSVにある表示されている項目の状況を調べる
	 * @param String カンマ区切りの文字列
	 */
	private function setFactors($line){
		foreach(explode(",", $line) as $n => $t){
			$i = array_search($t, $this->labels);
			if($i === false) continue;
			$this->factors[$i] = $n;
			unset($this->labels[$i]);
		}
	}
	
	/**
	 * 顧客IDのポイント履歴があるか調べる。なければtrue
	 * @param int userId
	 * @return boolean
	 */
	private function checkInsertedPoint($userId){
		try{
			$res = $this->dao->executeQuery("SELECT * FROM soyshop_point_history WHERE user_id = :user_id LIMIT 1", array(":user_id" => $userId));
		}catch(Exception $e){
			return true;
		}
		
		return (count($res) < 1);
	}
	
	function setType($type){
		$this->type = $type;
	}
}
?>