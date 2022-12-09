<?php
SOY2::imports("module.plugins.download_assistant.domain.*");
SOY2::import("module.plugins.bonus_download.util.BonusDownloadConfigUtil");
SOY2::import("module.plugins.bonus_download.util.BonusDownloadConditionUtil");
SOY2::import("module.plugins.bonus_download.logic.BonusDownloadFileLogic");
class BonusDownloadDownloadLogic {
	
	private $dao;
	
	/**
	 * ファイルをダウンロードする
	 * @param string $filename 購入特典ファイル名
	 */
	function downloadFile($filename){
		
		$filePath = BonusDownloadConfigUtil::getUploadDir(). $filename;
		if($filePath){
				
			//ファイルの出力
			try{
				$this->outputFile($filename, $filePath);
			}catch(Exception $e){

			}

		}
		
		return false;
	}
	
	/**
	 * ファイルをダウンロードする
	 * @param object SOYShop_Download file, string filepath
	 */
	function outputFile($filename, $filePath){
		//ダウンロード前にファイル名のチェック
		if(preg_match("/^[0-9A-Za-z%&+\-\^_`{|}~.]+$/", $filename)){
			$logic = new BonusDownloadFileLogic();
			$contentType = $logic->getContentType($filename);
			
			header("Cache-Control: public");
			header("Pragma: public");
			header("Content-Type: " . $contentType . ";");
	    	header("Content-Disposition: attachment; filename=" . basename($filename));
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
	
}
?>