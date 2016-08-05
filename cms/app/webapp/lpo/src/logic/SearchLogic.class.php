<?php

SOY2::import("domain.SOYLpo_List");
class SearchLogic extends SOY2LogicBase{

	/**
	 * 検索のワードから対応する記事のIDを取得する
	 * @param q mode
	 * @return id
	 */
    function search($q,$mode,$keyword){
    	$dao = new SOY2DAO();
    	
    	$sql =  "SELECT id " .
    			"FROM soylpo_list ".
    			"WHERE keyword LIKE :q AND mode = :mode AND is_public = 1 ".
    			"ORDER BY update_date DESC ".
    			"LIMIT 1";
    			
    	try{
    		$result = $dao->executeQuery($sql,array(
				":q" => "%" . $q . "%",
				":mode" => $mode
			));
    	}catch(Exception $e){
    		$result = array();
    	}
    	
    	//URLモードで結果がない場合、ドメインモードに切り替えて再度チェックする
    	if(count($result)==0&&$mode=SOYLpo_List::MODE_URL){
    		$mode = SOYLpo_List::MODE_DOMAIN;
    		preg_match('/^http:\/\/(.*?)\//',$q,$value);
			$domain = $value[1];
			$keyword = "http://".$domain."/";
			
			$q = $domain;
			
			try{
				$result = $dao->executeQuery($sql,array(
					":q" => "%" . $q . "%",
					":mode" => $mode
				));
			}catch(Exception $e){
				$result = array();
			}			
    	}
    	
    	$id = (count($result)>0) ? $result[0]["id"] : 1;
    	
    	//結果が取得できなかった場合はディフォルトを返す
    	return array($id,$keyword);
    }
    
    //表示回数のログをとる
    function addLog($id,$keyword){
    	
    	//ディフォルトの場合はログをとらない
    	if($id!=1){
    		$dao = SOY2DAOFactory::create("SOYLpo_LogDAO");
    		
    		$log = new SOYLpo_Log();
    		$log->setLpoId($id);
    		$log->setReferer($keyword);
    		$log->setEntryDate(date("Ymd",time()));
    		$log->setCreateDate(time());
    		
    		try{
    			$dao->insert($log);
    		}catch(Exception $e){
    			var_dump($e);
    		}
    	}
    }
}
?>