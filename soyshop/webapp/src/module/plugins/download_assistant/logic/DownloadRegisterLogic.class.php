<?php

class DownloadRegisterLogic extends SOY2LogicBase{

	private $status;
	private $config;
	private $dao;

	private $commonLogic;

	function __construct(){
		SOY2::imports("module.plugins.download_assistant.logic.*");
		SOY2::imports("module.plugins.download_assistant.domain.*");

		if(!$this->dao) $this->dao = SOY2DAOFactory::create("SOYShop_DownloadDAO");
		if(!$this->commonLogic) $this->commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");
	}

	/**
	 * ダウンロードの購入登録前にすでに登録されていないか？をチェック
	 * @param int orderId
	 * @return boolean
	 */
	function checkRegister($orderId){

		try{
			$files = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			$files = array();
		}

		//$filesで配列が0で有れば処理を続ける
		return (count($files) === 0);
	}

	/**
	 * ダウンロードの購入を登録
	 * @return int orderId, object SOYShop_Item, int userId, string status
	 */
	function register($orderId, SOYShop_Item $item, $userId, $status){

		$this->status = $status;
		$this->config = $this->commonLogic->getDownloadFieldConfig($item->getId());

		$files = self::getZipFile($item->getCode());

		//ファイルの数だけ登録を開始する
		foreach($files as $file){
			//登録前にファイル名のチェック
			if(preg_match("/^[0-9A-Za-z%&+\-\^_`{|}~.]+$/", $file)){
				//登録する値を入れるための配列
				$values = self::getDownloadArray($orderId, $item->getId(), $userId, $file);
				$download = SOY2::cast("SOYShop_Download",(object)$values);
				try{
					$this->dao->insert($download);
				}catch(Exception $e){
				}
			}
		}
	}

	/**
	 * @return array
	 */
	private function getZipFile($code){

		$array = array();

		$dir = SOYSHOP_SITE_DIRECTORY . "download/" . $code;
		$files = opendir($dir);
		while($file = readdir($files)){
			if($this->commonLogic->checkFileType($file) === true){
				$array[] = $file;
			}
		}
		return $array;
	}

	/**
	 * @param int orderId, int itemId, int userId, string file
	 * @return array
	 */
	private function getDownloadArray($orderId, $itemId, $userId, $file){

		return array(
				"orderId" => $orderId,
				"itemId" => $itemId,
				"userId" => $userId,
				"fileName" => $file,
				"token" => md5(time().$userId.rand(0,65535)),
				"orderDate" => time(),
				"receivedDate" => self::getReceivedDate(),
				"timeLimit" => $this->commonLogic->getLimitDate($this->config["timeLimit"]),
				"count" => (isset($this->config["count"]) && is_numeric($this->config["count"])) ? (int)$this->config["count"] : null
		);
	}

	private function getReceivedDate(){
		return ($this->status == SOYShop_Order::PAYMENT_STATUS_CONFIRMED) ? time() : null;
	}
}
