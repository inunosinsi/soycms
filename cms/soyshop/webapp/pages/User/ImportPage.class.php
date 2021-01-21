<?php

class ImportPage extends WebPage{
	private $dao;
	private $attributeDAO;
	private $pointLogic;

    function __construct() {

		SOY2::import("domain.config.SOYShop_ShopConfig");

		//権限がない場合は顧客トップにリダイレクト
		if(!AUTH_OPERATE) SOY2PageController::jump("User");

    	parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

    	self::buildForm();

		DisplayPlugin::toggle("fail", (isset($_GET["fail"])));
		DisplayPlugin::toggle("invalid", (isset($_GET["invalid"])));

		DisplayPlugin::toggle("user_custom_search_field", SOYShopPluginUtil::checkIsActive("user_custom_search_field"));
		DisplayPlugin::toggle("point", SOYShopPluginUtil::checkIsActive("common_point_base"));
    	$this->createAdd("customfield_list", "_common.User.CustomFieldListComponent", array(
    		"list" => $this->getCustomFieldList()
    	));

		//商品オプションリストを表示する
		$this->createAdd("custom_search_field_list", "_common.User.UserCustomSearchFieldImExportListComponent", array(
			"list" => $this->getCustomSearchFieldList()
		));

		//前にチェックした項目 jqueryで制御
		$this->addLabel("check_js", array(
			"html" => SOY2Logic::createInstance("logic.csv.ItemCheckListLogic")->buildJSCode("user")
		));
    }

    private function buildForm(){
    	$this->addForm("import_form", array(
    		 "ENCTYPE" => "multipart/form-data"
    	));

		$config = SOYShop_ShopConfig::load();

		//ログインIDの名称変更
		$this->addLabel("account_id_item_name", array(
			"text" => $config->getAccountIdItemName()
		));

		//項目の非表示用タグ
		foreach($config->getCustomerAdminConfig() as $key => $bool){
			DisplayPlugin::toggle($key, $bool);
		}

		DisplayPlugin::toggle("office_items", $config->getDisplayUserOfficeItems());
		DisplayPlugin::toggle("userCode", $config->getUseUserCode());
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

		$item = (isset($_POST["item"])) ? $_POST["item"] : array();

		//今回チェックした内容を保持する
		SOY2Logic::createInstance("logic.csv.ItemCheckListLogic")->save($item, "user");

		$logic->setSeparator(@$format["separator"]);
		$logic->setQuote(@$format["quote"]);
		$logic->setCharset(@$format["charset"]);
		$logic->setItems($item);
		$logic->setCustomFields($this->getCustomFieldList(true));
		$logic->setCustomSearchFields($this->getCustomSearchFieldList());

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

    	//カスタムサーチフィールド
		$customSearchFieldDBLogic = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.UserDataBaseLogic");

    	$this->dao->begin();

		//データ更新
    	foreach($lines as $line){
			if(empty($line)) continue;

    		list($obj, $attributes, $point, $customSearchFields) = $logic->import($line);

			//すでに削除された顧客の場合 //メールアドレスに_delete_数字がある
			if(preg_match('/_delete_[0-9]*$/', trim($obj["mailAddress"]), $tmp)) continue;

			//ダミーのメールアドレスで登録するか？
			if(!strlen($obj["mailAddress"]) && isset($format["dummy"]) && $format["dummy"] == 1){
				$obj["mailAddress"] = soyshop_dummy_mail_address();
			}

    		$deleted = (isset($obj["id"]) && $obj["id"] == "delete");

			//メールアドレスが無ければcontinue;
			if(strlen($obj["mailAddress"]) === 0) continue;

			//SOYShop_Userに変換
			$user = $this->import($obj);

			//フリガナで半角カナの場合は全角カナにする
			if(strlen($user->getReading()) && preg_match('/^[ｦ-ﾟｰ ]+$/u', $user->getReading())){
				$kana = mb_convert_kana($user->getReading(), "aK");
				$user->setReading($kana);
			}
			$user->setReading(soyshop_convert_kana_sonant($user->getReading()));

			//新規登録の場合は必ず本登録にしておく
			if(is_null($user->getUserType())) $user->setUserType(SOYShop_User::USERTYPE_REGISTER);

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

				//カスタムサーチフィールド
				if(count($customSearchFields)){
					$customSearchFieldDBLogic->save($user->getId(), $customSearchFields);
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

    function getCustomSearchFieldList(){
		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		return UserCustomSearchFieldUtil::getConfig();
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build(SHOP_USER_LABEL . "情報CSVインポート", array("User" => SHOP_USER_LABEL . "管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("User.FooterMenu.UserFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
