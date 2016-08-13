<?php

class ImportPage extends WebPage{
	private $dao;
	private $attributeDAO;
	private $pointLogic;

    function __construct() {

    	//管理制限の権限を取得
		$session = SOY2ActionSession::getUserSession();
		$appLimit = $session->getAttribute("app_shop_auth_limit");

		//権限がない場合は顧客トップにリダイレクト
		if(!$appLimit){
			SOY2PageController::jump("User");
		}

    	WebPage::__construct();
    	$this->buildForm();

		DisplayPlugin::toggle("fail", (isset($_GET["fail"])));
		DisplayPlugin::toggle("invalid", (isset($_GET["invalid"])));

		DisplayPlugin::toggle("point_checkbox", SOYShopPluginUtil::checkIsActive("common_point_base"));
    	$this->createAdd("customfield_list", "_common.User.CustomFieldListComponent", array(
    		"list" => $this->getCustomFieldList()
    	));

    }

    function buildForm(){
    	$this->addForm("import_form", array(
    		 "ENCTYPE" => "multipart/form-data"
    	));
    }

    function doPost(){

    	//check token
    	if(!soy2_check_token()){
    		SOY2PageController::jump("User.Import?fail");
			exit;
    	}

    	set_time_limit(0);

    	$file  = $_FILES["import_file"];

		$logic = SOY2Logic::createInstance("logic.user.ExImportLogic");
    	$format = $_POST["format"];
    	$item = $_POST["item"];

		$logic->setSeparator(@$format["separator"]);
		$logic->setQuote(@$format["quote"]);
		$logic->setCharset(@$format["charset"]);
		$logic->setItems($item);
		$logic->setCustomFields($this->getCustomFieldList(true));

		if(!$logic->checkUploadedFile($file)){
			SOY2PageController::jump("User.Import?fail");
			exit;
		}
		if(!$logic->checkFileContent($file)){
			SOY2PageController::jump("User.Import?invalid");
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
    	$this->dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
    	$this->attributeDAO = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
    	$config = SOYShop_UserAttributeConfig::load(true);

    	$this->dao->begin();

		//データ更新
    	foreach($lines as $line){
    		if(empty($line)) continue;

    		list($obj, $attributes, $point) = $logic->import($line);

    		$deleted = ($obj["id"] == "delete");

			//メールアドレスが無ければcontinue;
			if(strlen($obj["mailAddress"]) === 0) continue;

			//SOYShop_Userに変換
			$user = $this->import($obj);

			if($deleted){
				//ユーザーデータ、ユーザーカスタムフィールドの削除
				$this->delete($user, $attributes);
			}else{
				//ユーザーデータ、ユーザーカスタムフィールドの更新・挿入
				$this->insertOrUpdate($user, $attributes);

				//ポイントの付与
				if(strlen($user->getId()) && isset($point) && (int)$point > 0){
					if(!$this->pointLogic) $this->pointLogic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
					$oldPoint = $this->pointLogic->getPointByUserId($user->getId())->getPoint();

					if($point != $oldPoint){
						$this->pointLogic->updatePoint($point, $user->getId());
					}
				}
			}

    	}

    	$this->dao->commit();

		SOY2PageController::jump("User.Import?updated");
    }

	/**
	 * CSV, TSVの一行からSOYShop_Userを作り、返す
	 * 同じメールアドレスのユーザがすでに登録されている場合は、そのユーザのデータを上書きする形で返す
	 * @param String $line
	 * @param Array $properties
	 * @return SOYShop_User
	 */
	function import($obj){
		if(isset($obj["id"])) unset($obj["id"]);
		//既に登録されている顧客か調べる
		try{
			$user = $this->dao->getByMailAddress(trim($obj["mailAddress"]));
			if((int)$user->getId() > 0) $obj["id"] = (int)$user->getId();
		}catch(Exception $e){

		}
		return SOY2::cast("SOYShop_User", (object)$obj);
	}

	/**
	 * ユーザデータとユーザーカスタムフィールドの更新または挿入を実行する
	 * 同じメールアドレスのユーザがすでに登録されている場合に更新を行う
	 * @param SOYShop_User
	 * @param Array $attributes
	 */
	function insertOrUpdate(SOYShop_User $user, $attributes){

		$userId = $user->getId();
		if(strlen($userId)){
			$this->update($user);
		}else{
			$userId = $this->insert($user);
			$user->setId($userId);
		}

		//ユーザーデータの更新・挿入が成功したら
		if(strlen($userId)){
			//ユーザーカスタムフィールドも更新する
			foreach($attributes as $key => $value){
				$this->attributeDAO->delete($userId, $key);
				$attr = new SOYShop_UserAttribute();
				$attr->setUserId($userId);
				$attr->setFieldId($key);
				$attr->setValue($value);
				$this->attributeDAO->insert($attr);
			}
		}
	}

	/**
	 * ユーザデータの挿入を実行する
	 * @param SOYShop_User
	 */
	function insert(SOYShop_User $user){
		try{
			$userId = $this->dao->insert($user);
		}catch(Exception $e){
			$userId = null;
		}
		return $userId;
	}

	/**
	 * ユーザデータの更新を実行する
	 * @param SOYShop_User
	 */
	function update(SOYShop_User $user){

		try{
			$this->dao->update($user);
		}catch(Exception $e){

		}
	}

	/**
	 * ユーザデータととユーザーカスタムフィールドの削除を実行する
	 * @param SOYShop_User $user
	 * @param Array $attributes
	 */
	function delete(SOYShop_User $user, $attributes = array()){

		try{
			$user = $this->dao->getByMailAddress($user->getMailAddress());
			$this->dao->delete($user);

			//ユーザーカスタムフィールドも削除する
			$this->attributeDAO->deleteByUserId($user->getId());
		}catch(Exception $e){

		}
	}

    function getCustomFieldList($flag = false){
		$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$config = SOYShop_UserAttributeConfig::load($flag);
		return $config;
    }
}
?>