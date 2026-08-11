<?php
include(dirname(dirname(dirname(dirname(__FILE__))))."/module/plugins/item_review/domain/SOYShop_ItemReview.class.php");
include(dirname(dirname(dirname(dirname(__FILE__))))."/module/plugins/item_review/domain/SOYShop_ItemReviewDAO.class.php");
function execute(){

	_echo("<b>SOY Shop 1.6.0 Upgrade</b>");
	_echo("Run : ".date("Y/m/d H:i:s")."<br />");

	_echob("<br />[カート用のテンプレート]");

	//テンプレートの複製
	$tmpDir = SOYSHOP_SITE_DIRECTORY . ".template/";
	$cartDir = $tmpDir . "cart/";
	$mypageDir = $tmpDir . "mypage/";
	if(is_file($cartDir . "main.html")){
		//カート
		file_put_contents($cartDir . "omame.html",file_get_contents($cartDir . "main.html"));
		file_put_contents($cartDir . "omame.ini",str_replace("main","omame",file_get_contents($cartDir . "main.ini")));

		if(SOYShop_DataSets::get("config.cart.cart_id","main")=="main"){
			SOYShop_DataSets::put("config.cart.cart_id","omame");
		}

		//マイページ
		file_put_contents($mypageDir . "omame.html",file_get_contents($mypageDir . "main.html"));
		file_put_contents($mypageDir . "omame.ini",str_replace("main","omame",file_get_contents($mypageDir . "main.ini")));

		if(SOYShop_DataSets::get("config.mypage.id","main")=="main"){
			SOYShop_DataSets::put("config.mypage.id","omame");
		}

	}else{
		$omameTmpDir = SOYSHOP_WEBAPP . "src/logic/init/template/omame/";

		//カート
		file_put_contents($cartDir . "omame.html",str_replace("@@SOYSHOP_URI@@","/".SOYSHOP_ID,file_get_contents($omameTmpDir . "cart/omame.html")));
		file_put_contents($cartDir . "omame.ini",file_get_contents($omameTmpDir . "cart/omame.ini"));

		//マイページ
		file_put_contents($mypageDir . "omame.html",str_replace("@@SOYSHOP_URI@@","/".SOYSHOP_ID,file_get_contents($omameTmpDir . "mypage/omame.html")));
		file_put_contents($mypageDir . "omame.ini",file_get_contents($omameTmpDir . "mypage/omame.ini"));
	}

	$path = SOYSHOP_WEBAPP . "src/logic/init/theme/omame/";
	$to = SOYSHOP_SITE_DIRECTORY . "themes/";
	copyDirectory($path,$to);

	_echo("・CartID:omameを作成しました");

	$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");

	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAdd();
	}else{
		$sqlAdd = sqlSQLiteAdd();
	}


	$sqlAdd = explode(";",$sqlAdd);
	$flg = false;
	foreach($sqlAdd as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$dao->executeUpdateQuery($query);

		}catch(Exception $e){
			$flg = true;
		}
	}

//	if($flg){
//		_echo("・カラム追加は失敗しました。");
//	}else{
//		_echo("・カラム追加を行いました。");
//	}


	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAddOrders();
	}else{
		$sqlAdd = sqlSQLiteAddOrders();
	}


	_echob("<br />[注文商品(カラム追加)]");
	$sqlAdd = explode(";",$sqlAdd);
	$flg = false;
	foreach($sqlAdd as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$dao->executeUpdateQuery($query);

		}catch(Exception $e){
			$flg = true;
		}
	}

	if($flg){
		_echo("・カラム追加は失敗しました。");
	}else{
		_echo("・カラム追加を行いました。");
	}

	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
//	_echo("続いてファイルの上書きを実行してください。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;

}


function sqlSQLiteAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_item_review ADD COLUMN title varchar;

SQL;

	return $sql;
}

function sqlSQLiteAddOrders(){
	$sql = <<<SQL

ALTER TABLE soyshop_orders ADD attributes VARCHAR;

SQL;
	return $sql;
}

function sqlMySQLAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_item_review ADD COLUMN title varchar(255);

SQL;

	return $sql;
}

function sqlMySQLAddOrders(){
	$sql = <<<SQL

ALTER TABLE soyshop_orders ADD attributes TEXT;

SQL;
	return $sql;

}

function _echo($str=""){
	echo $str."<br />";
}

function _echob($str=""){
	_echo("<b>" . $str."</b>");
}
function copyDirectory($from,$to){

	$files = scandir($from);

	if($from[strlen($from)-1] != "/")$from .= "/";
	if($to[strlen($to)-1] != "/")$to .= "/";

	foreach($files as $file){
		if($file[0] == ".")continue;
		if(is_dir($from . $file)){
		if(!file_exists($to.$file))mkdir($to.$file);
			copyDirectory($from . $file, $to . $file);
			continue;
		}else{
			file_put_contents(
				$to . $file
				,file_get_contents($from . $file)
			);
		}

	}
}
?>