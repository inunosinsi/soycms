<?php

class SavePage extends CMSHTMLPageBase {

	function __construct(){
		parent::__construct();

		if(!isset($_POST["memo"])) self::_printFinished(0);

		//メモの内容を保存
		$dao = SOY2DAOFactory::create("cms.CmsMemoDAO");
		$latest = $dao->getLatestMemo();

		//内容が一緒の場合は更新しない
		$newMemo = trim($_POST["memo"]);
		if(md5($latest->getContent()) == md5($newMemo)) self::_printFinished(0);
		$latest->setContent($newMemo);

		if(is_null($latest->getId())){	//新規登録
			try{
				$dao->insert($latest);
			}catch(Exception $e){
				self::_printFinished(0);
			}
		}else{
			try{
				$dao->update($latest);
			}catch(Exception $e){
				self::_printFinished(0);
			}
		}

		self::_printFinished(1);
	}

	private function _printFinished($flag){
		echo json_encode(array("finished" => $flag));
		exit;
	}
}
