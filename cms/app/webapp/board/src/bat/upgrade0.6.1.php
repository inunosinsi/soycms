<?php
/*
 * version 0.5.x -> 0.6.1
 */
$dao = new SOY2DAO();

try{
	
	$dao->begin();
	if(SOYCMS_DB_TYPE == "mysql"){
		$dao->executeUpdateQuery("ALTER TABLE soyboard_thread MODIFY id INTEGER AUTO_INCREMENT;",array());
		$dao->executeUpdateQuery("ALTER TABLE soyboard_thread MODIFY lastsubmitdate DATETIME NOT NULL",array());
		$dao->executeUpdateQuery("ALTER TABLE soyboard_response ADD PRIMARY KEY(id)",array());
		$dao->executeUpdateQuery("ALTER TABLE soyboard_response MODIFY id INTEGER AUTO_INCREMENT;",array());
		$dao->executeUpdateQuery("ALTER TABLE soyboard_response ADD response_id INTEGER NOT NULL DEFAULT 0",array());
		$dao->executeUpdateQuery("ALTER TABLE soyboard_response MODIFY submitdate DATETIME NOT NULL",array());
		
	}elseif(SOYCMS_DB_TYPE == "sqlite"){
		
		$sql1 = "ALTER TABLE soyboard_response RENAME TO pre_response";
		$dao->executeUpdateQuery($sql1, array());

		$sql2 = "CREATE TABLE soyboard_response(
		thread_id INTEGER NOT NULL,
		id INTEGER PRIMARY KEY NOT NULL,
		name VARCHAR(64) NOT NULL,
		email VARCHAR(64) NOT NULL,
		submitdate DATE NOT NULL,
		hash VARCHAR(32) NOT NULL,
		body VARCHAR(1024) NOT NULL,
		host VARCHAR(32) NOT NULL,
		response_id INTEGER NOT NULL DEFAULT 0)";
		$dao->executeUpdateQuery($sql2, array());
		
		$result = $dao->executeQuery("SELECT thread_id, name, email, submitdate, hash, body, host FROM pre_response", array());
		$dao2 = SOY2DAOFactory::create("SOYBoard_ResponseDAO");

		foreach($result as $id => $row){
			$sql3 = "INSERT INTO soyboard_response (thread_id, name, email, submitdate, response_id, hash, body, host) ";
			$sql3 .= "VALUES ('".$row["thread_id"]."', '".$row["name"]."', '".$row["email"]."', '".$row["submitdate"]."', ";
			$sql3 .= "'0', '".$row["hash"]."', '".$row["body"]."', '".$row["host"]."')";
			$dao->executeUpdateQuery($sql3,array());

		}
	}
	$dao->commit();
	$dao->executeUpdateQuery("UPDATE soyboard_response SET response_id = 0",array());
	
}catch(Exception $e){
	
}




try{
	$result = $dao->executeQuery("SELECT thread_id FROM soyboard_response GROUP BY thread_id;",array());	
}catch(Exception $e){

}

//スレッドごとの結果を取得
if(count($result) > 0){
	foreach($result as $id => $byThread){
		$counter = 0;
		$dao->begin();
	
		//スレッドIDごとに結果を処理する
		foreach($byThread as $key1 => $threadId){
			$responses = $dao->executeQuery("SELECT id FROM soyboard_response WHERE thread_id = ". $threadId . ";",array());
			$responseId = 0;
			
			//レスポンスごとに個別に処理する
			foreach($responses as $key2 => $res){
				$id = $res["id"];
				$responseId ++;
	
				try{
					$dao->executeUpdateQuery("UPDATE soyboard_response SET response_id = " . $responseId . " WHERE id = " . $res["id"] .";", array());
				
				}catch(Exception $e){
				
				}
			}
		}
	$dao->commit();	
	}
}
?>

<h1>SOY Board バージョンアッププログラム (～0.5 -> 0.6.1)</h1>

<ul>
	<li>MySQLで動作しないバグを修正しました。</li>
	<li>ロジックの変更にともなう修正を行いました。</li>
</ul>

<a href="<?php echo SOY2PageController::createLink("board"); ?>">戻る</a>
<?php exit; ?>