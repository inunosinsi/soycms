<?php
/*
 * version 0.9.x -> 1.0.0
 */

$dao = new SOY2DAO();
$flag = false;

if(SOYCMS_DB_TYPE == "mysql"){
	try{
		$dao->executeUpdateQuery("ALTER TABLE soyinquiry_inquiry MODIFY content TEXT",array());
		$dao->executeUpdateQuery("ALTER TABLE soyinquiry_inquiry MODIFY data TEXT",array());
		$flag = true;
	}catch(Exception $e){
		
	}
}

?>

<h1>SOY Inquiry バージョンアッププログラム(0.9 -> 1.0.0)</h1>

<ul>
<?php if($flag===true){
	echo "<li>soyinquiry_inqiuryのcontentとdataカラムの型をvarcharからtextに変更しました。</li>";
}else{
	echo "<li>変更はありません。</li>";
}?>
</ul>

<a href="<?php echo SOY2PageController::createLink("inquiry"); ?>">戻る</a>