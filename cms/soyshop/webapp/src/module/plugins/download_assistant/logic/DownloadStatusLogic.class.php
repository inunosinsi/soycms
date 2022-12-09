<?php
class DownloadStatusLogic extends SOY2LogicBase{

	private $dao;
	private $logic;
	private $config;
	private $commonLogic;

	function __construct(){
		SOY2::imports("module.plugins.download_assistant.domain.*");
		if(!$this->dao) $this->dao = SOY2DAOFactory::create("SOYShop_DownloadDAO");

		if(!$this->commonLogic) $this->commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");

	}

	function receivedStatus($orderId){
		foreach(self::getDownloadFile($orderId) as $file){
			self::updateDownloadFile($file);
		}
	}

	function cancel($orderId){
		self::cancelStatus($orderId);
	}

	private function cancelStatus($orderId){
		foreach(self::getDownloadFile($orderId) as $file){
			self::cancelDownloadFile($file);
		}
	}

	private function getDownloadFile($orderId){

		try{
			return $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
	}

	private function updateDownloadFile(SOYShop_Download $file){
   		$this->config = $this->commonLogic->getDownloadFieldConfig($file->getItemId());

		$file->setReceivedDate(time());
		$file->setTimeLimit($this->commonLogic->getLimitDate($this->config["timeLimit"]));

		try{
			$this->dao->update($file);
		}catch(Exception $e){
			//
		}
	}

	private function cancelDownloadFile(SOYShop_Download $file){
   		$this->config = $this->commonLogic->getDownloadFieldConfig($file->getItemId());

 		$file->setReceivedDate(null);

 		try{
			$this->dao->update($file);
		}catch(Exception $e){
			//
		}
	}
}
