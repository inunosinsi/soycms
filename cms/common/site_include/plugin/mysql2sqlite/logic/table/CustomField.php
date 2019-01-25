<?php

function registerCustomField(PDO $pdo){
	$pdo->query("ALTER TABLE Entry ADD COLUMN custom_field TEXT");

	$dao = new SOY2DAO();
	$res = $dao->executeQuery("SELECT id, custom_field FROM Entry WHERE LENGTH(custom_field) > 0");
	if(!count($res)) return;

	$stmt = $pdo->prepare("UPDATE Entry SET custom_field = :custom WHERE id = :id");
	foreach($res as $v){
		$stmt->execute(array(
			":custom" => $v["custom_field"],
			":id" => $v["id"]
		));
	}
}
