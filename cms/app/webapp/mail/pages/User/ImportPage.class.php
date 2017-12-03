<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");

class ImportPage extends CommonPartsPage{
	private $dao;

    function __construct() {
    	parent::__construct();
    	$this->redirectCheck();
    	$this->createTag();
    	$this->buildForm();
    }

    function buildForm(){
    	$this->addForm("import_form", array(
    		 "ENCTYPE" => "multipart/form-data"
    	));
    }
    
    function doPost(){
    	
    	if(soy2_check_token()){
    		set_time_limit(0);
	    	
	    	$format = $_POST["format"];
	    	$item = $_POST["item"];
	    	$file  = $_FILES["import_file"];
	    	
	    	$logic = SOY2Logic::createInstance("logic.user.ExImportLogic");
	
			$logic->setSeparator(@$format["separator"]);
			$logic->setQuote(@$format["quote"]);
			$logic->setCharset(@$format["charset"]);
			$logic->setItems($item);
			
			if(!$logic->checkUploadedFile($file)){
				SOY2PageController::jump("mail.User.Import?failed");
				exit;
			}
			
			if($logic->checkFileContent($file) === false){
				SOY2PageController::jump("mail.User.Import?invalid");
				exit;
			}
			
			//ファイル読み込み・削除
			$fileContent = file_get_contents($file["tmp_name"]);
	    	unlink($file["tmp_name"]);
	
			//データを行単位にばらす
			$lines = $logic->GET_CSV_LINES($fileContent);	//fix multiple lines
			
			//先頭行削除
	    	if(isset($format["label"])) array_shift($lines);
	
	    	//DAO
	    	$this->dao = SOY2DAOFactory::create("SOYMailUserDAO");
	    	
	    	$this->dao->begin();
			//データ更新
	    	foreach($lines as $line){
	    		if(empty($line)) continue;
	    		
	    		$obj = $logic->import($line);
	    		
	    		$deleted = ($obj["id"] == "delete");
	
				$user = $this->import($obj);
				
				if($deleted){
					$this->delete($user);
	
				}else if(strlen($user->getMailAddress()) > 0){
	    			$this->insertOrUpdate($user);
				}
	    	}
	    	
	    	$this->dao->commit();
	
			SOY2PageController::jump("mail.User.Import?success");
    	}
	    	
    }
    
    /**
	 * CSV, TSVの一行からSOYShop_Userを作り、返す
	 * 同じメールアドレスのユーザがすでに登録されている場合は、そのユーザのデータを上書きする形で返す
	 * @return SOYMailUser
	 */
	function import($obj){
		
		//既に登録されているか？をメールアドレスから確認する
		try{
			$oldId = $this->dao->getIdByEmail($obj["mailAddress"]);
		}catch(Exception $e){
			$oldId = null;
		}
		
		$obj["id"] = (isset($oldId)) ? $oldId : null;			
		$user = SOY2::cast("SOYMailUser", (object)$obj);
		
		//birthdayはタイムスタンプにして、放り込む
		$birthday = $user->getBirthday();
		if(isset($birthday) && strpos($birthday, "-") !== false){
			$array = explode("-",$birthday);
			if(isset($array[0]) && isset($array[1]) && isset($array[2])){
				$birthdayText = mktime(0, 0, 0, $array[1], $array[2], $array[0]);
				$user->setBirthday($birthdayText);
			}
		}
		
		return $user;
	}

	/**
	 * ユーザデータの更新または挿入を実行する
	 * 同じメールアドレスのユーザがすでに登録されている場合に更新を行う
	 * @param SOYMailUser
	 */
	function insertOrUpdate(SOYMailUser $user){
		if(strlen($user->getId())){
			$this->update($user);
		}else{
			$this->insert($user);
		}
	}

	/**
	 * ユーザデータの挿入を実行する
	 * @param SOYShop_User
	 */
	function insert(SOYMailUser $user){
		try{
			$this->dao->insert($user);
		}catch(Exception $e){
			//
		}
	}

	/**
	 * ユーザデータの更新を実行する
	 * @param SOYShop_User
	 */
	function update(SOYMailUser $user){

		try{
			$this->dao->update($user);
		}catch(Exception $e){
			//
		}
	}

	/**
	 * ユーザデータの削除を実行する
	 * @param SOYShop_User
	 */
	function delete(SOYMailUser $user){

		try{
			$user = $this->dao->getByMailAddress($user->getMailAddress());
			$this->dao->delete($user);
		}catch(Exception $e){

		}
	}
}
?>