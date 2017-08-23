<?php

class SiteDeleteLogic extends SOY2LogicBase{

	/**
	 * サイトを削除する
	 * @return boolean
	 * @param id 削除するサイトのID
	 */
    function deleteSite($id, $dropDB = true, $rmDir = true){

    	$dao = SOY2DAOFactory::create("admin.SiteDAO");
		$siteRoleDAO = SOY2DAOFactory::create("admin.SiteRoleDAO");

		try{
			$dao->begin();
			$site = $dao->getById($id);

			//Adminのデータベースから値を削除
			$siteRoleDAO->deleteBySiteId($id);
			$dao->delete($id);

			$siteDir = $site->getPath();

			//サイトのデータベースを削除（MySQL版のみ）
			if($dropDB && SOYCMS_DB_TYPE == "mysql"){
				if($site->getDataSourceName() != SOY2DAOConfig::Dsn()){
					$this->deleteDataBase($site->getSiteId());
				}else{
					$this->dropAllTable($site->getDataSourceName());
				}
			}

			//サイトディレクトリを削除
			if($rmDir){
				$this->deleteSiteDir($siteDir);
			}

			//サイトの種類ごとの追加処理
			switch($site->getSiteType()){
				case Site::TYPE_SOY_SHOP:
					//SOY Shopの設定ファイルを削除
					$this->deleteSoyshop($site);
					break;
				default:
			}


			$dao->commit();

			//ルート設定のファイルを削除
			//ルート設定時はサイトを削除できないようにしたのでこの処理は使われないが、念のため置いておく
			if($site->getIsDomainRoot()){
				$logic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
				$logic->delete();
			}

			return true;
		}catch(Exception $e){
			$dao->rollback();
			return false;
		}
    }

	/**
	 * 指定されたサイトディレクトリを丸ごと削除する
	 */
	public function deleteSiteDir($siteDir){
		/*
		 * SOYCMS_TARGET_DIRECTORY直下の実在するサブディレクトリのみを削除する
		 */

		if(
			realpath($siteDir) !== false
			AND soy2_realpath(dirname(realpath($siteDir))) === soy2_realpath(SOYCMS_TARGET_DIRECTORY)
		){
	    	$this->deleteFiles($siteDir);
		}
    }

    /**
     * MySQL版のみ
     * データーベースの削除
     */
    function deleteDataBase($siteId){
    	if(SOYCMS_DB_TYPE != "mysql")return;

    	$dao = new PDO(SOY2DAOConfig::Dsn(),SOY2DAOConfig::user(),SOY2DAOConfig::pass());

    	try{
    		$dao->exec("drop database soycms_".$siteId);
    	}catch(Exception $e){
    		//do nothing
    	}
    }

    /**
     * MySQL版のみ
     * 全てのテーブルを削除する
     */
    function dropAllTable($dsn){
    	if(SOYCMS_DB_TYPE != "mysql")return;

		$table_array = array(
			"Block",
			"Entry",
			"EntryComment",
			"EntryLabel",
			"EntryTrackback",
			"Label",
			"Page",
			"SiteConfig",
			"Template",
			"TemplateHistory"
		);

		try{
			$pdo = SOY2DAO::_getDataSource();
			foreach($table_array as $table){
				$pdo->exec('drop table '.$table.';');
			}
		}catch(Exception $e){
			return false;
		}

		return true;
    }

    /**
     * 再帰的にディレクトリのファイルを削除
     */
    function deleteFiles($dir){

		//末尾のスラッシュを除去
		if(strrpos($dir,"/")===strlen($dir)-1){
			$dir = substr($dir,0,strlen($dir)-1);
		}

		//書き込み権限がなければ付与する
		if (!is_writable( $dir )){
			if (!@chmod( $dir, 0777 )){
				return false;
			}
		}

		$d = dir( $dir );
		while(($entry = $d->read())!==false){// != だと 0  というディレクトリが消せない
			if ($entry == '.'| $entry == '..'){continue;}
			$entry = $dir . '/' . $entry;

			if(
				//ディレクトリ
				is_dir($entry) && !$this->deleteFiles($entry)
				||
				//ファイル
				file_exists($entry) && !@unlink($entry)
			){
				error_log("Failed to delete ".$entry);
				$d->close();
				return false;
			}
		}
		$d->close();

		if(is_link($dir)){
			//リンクの場合
			@unlink($dir);
		}elseif(is_dir($dir)){
			//実ディレクトリの場合
			rmdir( $dir );
		}
		return true;
}

    /**
     * SOY Shopの場合の削除処理
     * @param Site site
     */
    function deleteSoyshop($site){
    	// common/db/soyshop.db → do nothing
//    	$path = SOY2::RootDir()."db/soyshop.db";
//    	$array = unserialize(@file_get_contents($path));

    	//SOY Shopの設定ファイルを削除
    	// soyshop/webapp/conf/shop/{siteId}.conf.php
    	$confPath = dirname(SOY2::RootDir()). "/soyshop/webapp/conf/shop/".$site->getSiteId().".conf.php";
    	@unlink($confPath);

    }
}
?>