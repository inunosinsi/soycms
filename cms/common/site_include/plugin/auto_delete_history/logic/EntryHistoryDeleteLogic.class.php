<?php

class EntryHistoryDeleteLogic extends SOY2LogicBase{

	function deleteHistory(int $day){
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery("DELETE FROM EntryHistory WHERE cdate < " . strtotime("-" . $day . "day"));
		}catch(Exception $e){
			//
		}
	}

	function deleteHistoryEachEntryIds(int $cnt){
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery("SELECT entry_id, COUNT(entry_id) AS CNT FROM EntryHistory GROUP BY entry_id HAVING COUNT(entry_id) > " . $cnt);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return;

		$try = 0;	//記事の履歴を消すのは5記事毎
		foreach($res as $v){
			$entryId = $v["entry_id"];
			try{
				$cdateRes = $dao->executeQuery("SELECT cdate FROM EntryHistory WHERE entry_id = :entryId ORDER BY cdate DESC", array(":entryId" => $entryId));
			}catch(Exception $e){
				continue;
			}

			for($i = 0; $i < $cnt; $i++){
				array_shift($cdateRes);
			}
			$cdateList = array();
			foreach($cdateRes as $v){
				$cdateList[] = $v["cdate"];
			}

			try{
				$dao->executeUpdateQuery("DELETE FROM EntryHistory WHERE entry_id = :entryId AND cdate IN (" . implode(",", $cdateList) . ")", array(":entryId" => $entryId));
			}catch(Exception $e){
				//
			}
			if($try++ > 5) break;
		}
	}
}
