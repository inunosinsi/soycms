<?php
/**
 * -class CMSVersionUpUtil
 * データベースの変更などを行う 
 * 
 * 1.1以降でバンドル
 */
class CMSVersionUpUtil {

	/**
	 * 全てのサイトに次のSQLを実行
	 */
    public static function executeQueryToAllSites($sql){

    	if(!is_array($sql)){
    		$sql = implode(";");
    	}
    	
    	//ASP版はDBは一つ
    	if(defined("SOYCMS_ASP_MODE")){
			$pdo = new PDO(SOYCMS_ASP_DSN,SOYCMS_ASP_USER,SOYCMS_ASP_PASS,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			
			foreach($sql as $query){
				$pdo->exec($query);
			}
			
    	}else{
    	
	    	//サイトを取得
	    	$siteDAO = SOY2DAOFactory::create("admin.Site");
	    	$sites = $siteDAO->get();
    		
	    	foreach($sites as $site){
	    		
	    		switch(SOYCMS_DB_TYPE){
					
					case "mysql":
						$pdo = new PDO($site->getDataSourceName(),ADMIN_DB_USER,ADMIN_DB_PASS,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
						break;
					case "sqlite":
					default:
						$pdo = new PDO("sqlite:".$site->getPath().".db/sqlite.db","","",array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
						break;
				}
	    		
	    		foreach($sql as $query){
					$pdo->exec($query);
				}
	    	}
    	}
    }
    
}
?>