<?php

function registerEntryInfo(PDO $pdo){
	$pdo->query("ALTER TABLE Entry ADD COLUMN keyword VARCHAR");

	$dao = new SOY2DAO();
	$res = $dao->executeQuery("SELECT id, keyword FROM Entry WHERE LENGTH(keyword) > 0");
	if(!count($res)) return;

	$stmt = $pdo->prepare("UPDATE Entry SET keyword = :keyword WHERE id = :id");
	foreach($res as $v){
		$stmt->execute(array(
			":keyword" => $v["keyword"],
			":id" => $v["id"]
		));
	}
}
