<?php

class TemplateHistoryDeleteLogic extends SOY2LogicBase {

	function deleteHistory(int $day){
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery("DELETE FROM TemplateHistory WHERE update_date < " . strtotime("-" . $day . "day"));
		}catch(Exception $e){
			//
		}
	}

	function deleteHistoryEachPageIds(int $cnt){
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery("SELECT page_id, COUNT(page_id) AS CNT FROM TemplateHistory GROUP BY page_id HAVING COUNT(page_id) > " . $cnt);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return;

		$try = 0;	//記事の履歴を消すのは5記事毎
		foreach($res as $v){
			$pageId = $v["page_id"];
			try{
				$cdateRes = $dao->executeQuery("SELECT update_date FROM TemplateHistory WHERE page_id = :pageId ORDER BY update_date DESC", array(":pageId" => $pageId));
			}catch(Exception $e){
				continue;
			}

			for($i = 0; $i < $cnt; $i++){
				array_shift($cdateRes);
			}
			$cdateList = array();
			foreach($cdateRes as $v){
				$cdateList[] = $v["update_date"];
			}

			try{
				$dao->executeUpdateQuery("DELETE FROM TemplateHistory WHERE page_id = :pageId AND update_date IN (" . implode(",", $cdateList) . ")", array(":pageId" => $pageId));
			}catch(Exception $e){
				//
			}
			if($try++ > 5) break;
		}
	}
}
