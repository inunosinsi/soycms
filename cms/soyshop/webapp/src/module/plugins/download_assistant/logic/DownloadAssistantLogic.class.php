<?php

class DownloadAssistantLogic extends SOY2LogicBase{

	private $dao;
	private $itemDao;

	function __construct(){
		SOY2::imports("module.plugins.download_assistant.logic.*");
		SOY2::imports("module.plugins.download_assistant.domain.*");

		if(!$this->dao)	$this->dao = SOY2DAOFactory::create("SOYShop_DownloadDAO");
		if(!$this->itemDao) $this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}

	/**
	 * order_idとitem_idからfileリストを取得する
	 * @param int orderId, int itemId
	 * @return object SOYShop_Download
	 */
	function getDownloadFiles($orderId, $itemId){
		try{
			return $this->dao->getByOrderIdAndItemId($orderId, $itemId);
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * 表示用のダウンロードパスを生成する
	 * @param object SOYShop_Download
	 * @return string url
	 */
	function getDownloadFilePath(SOYShop_Download $file){
		return	self::getMypagePath() . $file->getToken();
	}

	private function getMypagePath(){
		return soyshop_get_mypage_url(true) . "?soyshop_download=download_assistant&token=";
	}

	function getItemIds($orderId){
		try{
			return $this->dao->getItemIdByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * ファイルが配置されているディレクトリのパスを生成する
	 * @param object SOYShop_Download
	 * @return string path
	 */
	private function getFileDirectoryPath($file){
		try{
			$item = $this->itemDao->getById($file->getItemId());
		}catch(Exception $e){
			return null;
		}

		return SOYSHOP_SITE_DIRECTORY . "download/" . $item->getCode() . "/" . $file->getFileName();
	}

	/**
	 * ファイルをダウンロードする
	 * @param object SOYShop_Download
	 */
	function downloadFile(SOYShop_Download $file){

		$filePath = self::getFileDirectoryPath($file);
		if($filePath){
			$rest = $file->getCount();

			//ダウンロード回数の再度チェック
			if(is_null($rest) || $rest > 0){

				//残りダウンロード回数から1引いて、DBに再度インサート
				$rest = (!is_null($rest)) ? $rest - 1 : null;
				$file->setCount($rest);

				//zipファイルをダウンロードする
				try{
					self::outputFile($file, $filePath);
					$this->dao->update($file);
				}catch(Exception $e){
					//
				}
			}
		}

		return false;
	}

	/**
	 * ファイルをダウンロードする
	 * @param object SOYShop_Download file, string filepath
	 */
	private function outputFile(SOYShop_Download $file, $filePath){
		//ダウンロード前にファイル名のチェック
		if(preg_match("/^[0-9A-Za-z%&+\-\^_`{|}~.]+$/", $file->getFileName())){
			$commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");
			$contentType = $commonLogic->getContentType($file->getFileName());

			header("Cache-Control: public");
			header("Pragma: public");
			header("Content-Type: " . $contentType . ";");
	    	header("Content-Disposition: attachment; filename=" . basename($file->getFileName()));
	    	header("Content-Length: " . filesize($filePath));

			flush();
			while(ob_get_level()){
				ob_end_clean();
			}

			$handle = fopen($filePath, 'rb');
			while ( $handle !== false && !feof($handle) && ! connection_aborted() ){
				echo fread($handle, 4096);
				flush();
			}
			fclose($handle);
		}
	}

	//itemIdからitemを取得する
	function getItem($itemId){
		try{
			return $this->itemDao->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	/**
	 * tokenからfileを取得する
	 * @param string token
	 * @return object SOYShop_Download
	 */
	function getFileByToken($token){
		try{
			return $this->dao->getByToken($token);
		}catch(Exception $e){
			return null;
		}
	}
}
