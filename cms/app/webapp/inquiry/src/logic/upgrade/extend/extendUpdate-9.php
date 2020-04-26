<?php
$dao = new SOY2DAO();
$try = 0;
for(;;){
	$res = $dao->executeQuery("SELECT inquiry_id, create_date FROM soyinquiry_comment GROUP BY inquiry_id, create_date HAVING count(*) > 1 LIMIT 1000");
	if(!count($res)) {
		break;
	}

	foreach($res as $v){
		$results = $dao->executeQuery("SELECT id FROM soyinquiry_comment WHERE inquiry_id = " . $v["inquiry_id"] . " AND create_date =" . $v["create_date"]);
		if(!count($results)) break;

		foreach($results as $i => $val){
			if($i === 0) continue;
			$dao->executeUpdateQuery("Update soyinquiry_comment SET create_date = " . ($v["create_date"] + $i) . " WHERE id = " . $val["id"]);
		}
	}
}
