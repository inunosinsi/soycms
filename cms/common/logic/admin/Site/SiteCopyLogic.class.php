<?php

class SiteCopyLogic extends SOY2LogicBase{

	/**
	 * サイトをコピーする
	 * @param from コピー元サイトのID
	 * @param from コピー先サイトのID
	 */

    function copySite($from, $to){
		
    	$dao = SOY2DAOFactory::create("admin.SiteDAO");
    	
		try{
			$fromSite = $dao->getById($from);
			$toSite = $dao->getById($to);
			$fromDir = $fromSite->getPath();
			$toDir = $toSite->getPath();

			//サイトのデータベースをコピー（MySQL版のみ）・・・未対応
			/*
			if(SOYCMS_DB_TYPE == "mysql"){
				$this->deleteDataBase($site->getSiteId());
			}
			*/
			
			$this->copyFiles($fromDir,$toDir);


			return true;
		}catch(Exception $e){
			$dao->rollback();
			return false;
		}
    }


    /**
     * MySQL版のみ
     * データーベースのコピー
     */
    function copyDataBase($siteId){
    	if(SOYCMS_DB_TYPE != "mysql")return;

    	$dao = new PDO(SOY2DAOConfig::Dsn(),SOY2DAOConfig::user(),SOY2DAOConfig::pass());

    	try{
    		$dao->exec(" soycms_".$siteId);
    	}catch(Exception $e){
    		//do nothing
    	}
    }
    /**
     * 再帰的にディレクトリのファイルをコピー
     */
     
    function copyFiles($fromDir,$toDir){

    	if (is_dir($fromDir)) {
			
			$files = scandir($fromDir);
			foreach ($files as $file) {
				if (($file != ".") && ($file != "..")&& ($file != "index.php")&& ($file != ".cache")) {
					$this->copyFiles("$fromDir/$file", "$toDir/$file");
				}
 	        }
	    }else if (file_exists($fromDir)) {
			copy($fromDir, $toDir);
		}
		return true;
	}

    
}
?>