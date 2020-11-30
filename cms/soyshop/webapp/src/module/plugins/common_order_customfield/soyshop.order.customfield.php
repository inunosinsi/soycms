<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class CommonOrderCustomfieldModule extends SOYShopOrderCustomfield{

	private $dao;
	private $list;

	//読み込み準備
	private function prepare(){
		if(!$this->dao){
			$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
			foreach(SOYShop_OrderAttributeConfig::load(true) as $config){
				//管理画面側なら必ずフォームを表示する or 公開側の場合はisAdminOnlyが0であれば表示する
				if(
					(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE) ||
					($config->getIsAdminOnly() != SOYShop_OrderAttribute::DISPLAY_ADMIN_ONLY)
				) {
					$this->list[] = $config;
				}
			}
		}
	}

	function clear(CartLogic $cart){

		self::prepare();

		if(is_array($this->list) && count($this->list)){
			foreach($this->list as $config){
				$cart->removeModule($cart->getAttribute("order_customfield_" . $config->getFieldId()));
				$cart->clearAttribute("order_customfield_" . $config->getFieldId() . ".value");
				$cart->clearOrderAttribute("order_customfield_" . $config->getFieldId());
			}
		}
	}

	function doPost($param){

		$cart = $this->getCart();

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return;

		//ファイル用
		$new = array();

		//paramの再配列
		$array = array();
		foreach($this->list as $obj){
			$isContinue = false;
			switch($obj->getType()){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					$value["value"] = (isset($param[$obj->getFieldId()]) && is_array($param[$obj->getFieldId()])) ? implode(",", $param[$obj->getFieldId()]) : "";
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_FILE:

					$tmp = $_FILES["customfield_module"]["tmp_name"][$obj->getFieldId()];

					//とりあえずキャッシュにアップロードしておく
					if(isset($tmp) && strlen($tmp)){

						//拡張子を調べる。許可していない拡張子の場合は処理を止める
						if(!self::checkUploadFileExtension($obj)) {
							$isContinue = true;
							break;
						}

						$filename = $_FILES["customfield_module"]["name"][$obj->getFieldId()];
						$new[$obj->getFieldId()] = date("YmdHis") . "." . substr($filename, strrpos($filename, ".") + 1);

						if(move_uploaded_file($tmp, self::getCacheDir() . $new[$obj->getFieldId()])){
							$value["value"] = $filename;
						}else{
							$isContinue = true;//ファイルアップロードに関する処理を止める
						}
					//今回はアップロードしないけど、すでにファイルをアップロードしている時
					}else{
						if(!isset($param[$obj->getFieldId()])) $isContinue = true;;
						$value["value"] = $param[$obj->getFieldId()];
					}
					break;
				default:
					$value["value"] = (isset($param[$obj->getFieldId()])) ? $param[$obj->getFieldId()] : "";
			}

			//switch中でのcontinueの処理をswitchの外に出した PHP7.3対策
			if($isContinue) continue;

			$value["label"] = $obj->getLabel();
			$value["type"] = $obj->getType();

			if($obj->getType() == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO){
				$config = $obj->getConfig();
				$value["option"] = $config["attributeOtherText"];
			}else{
				$value["option"] = null;
			}

			$array[$obj->getFieldId()] = $value;
		}
		$param = $array;

		foreach($param as $key => $obj){
			$module = new SOYShop_ItemModule();
			$module->setId("order_customfield_" . $key);
			$module->setName($obj["label"]);
			$module->setType("customfield_module_" . $key);//カスタムフィールドは仮想的にモジュールがたくさん存在することになる
			$module->setIsVisible(false);
			$cart->addModule($module);

			$value = null;
			switch($obj["type"]){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					$other = (isset($_POST["customfield_module"]["custom_radio_other_text"])) ? $_POST["customfield_module"]["custom_radio_other_text"] : null;
					$array = array();
					$array[] = $obj["value"];
					if(isset($obj["option"]) && $obj["value"] == $obj["option"]){
						$array[] = ":" . $other;
						$val = $other;
					}else{
						$val = null;
					}
					$value = implode("", $array);
					/**
					 * radioの場合だけarray("value" => "", "other" => "")の形式にする
					 */
					$obj["value"] = array("value" => $obj["value"], "other" => $val);
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_FILE:
					$value = $obj["value"];
					if(isset($new[$key])){
						$cart->setAttribute("order_customfield_" . $key . ".tmp", $new[$key]);
					}
					break;
				default:
					$value = $obj["value"];
					break;
			}
			unset($obj["option"]);

			//属性の登録
			$cart->setAttribute("order_customfield_" . $key . ".value", $obj["value"]);
			$cart->setOrderAttribute("order_customfield_" . $key, $obj["label"], $value, true, true);
		}
	}

	function complete(CartLogic $cart){

		$orderId = $cart->getAttribute("order_id");
		if(!strlen($orderId)){
			throw new Exception("No order.id designated for this order custom field.");
		}

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return;

		foreach($this->list as $config){
			$value = $cart->getAttribute("order_customfield_" . $config->getFieldId() . ".value");

			$obj = new SOYShop_OrderAttribute();
			$obj->setOrderId($orderId);
			$obj->setFieldId($config->getFieldId());

			switch($config->getType()){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					$obj->setValue1($value["value"]);
					$obj->setValue2($value["other"]);
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_FILE:
					//ファイルを各顧客用のフォルダに移動
					$tmp = $cart->getAttribute("order_customfield_" . $config->getFieldId() . ".tmp");
					$tmpFile = self::getCacheDir() .$tmp;
					if(file_exists($tmpFile) && !is_dir($tmpFile)){
						//value1にアップロード時のファイル名、value2にはリネーム後のファイル名
						$obj->setValue1($value);
						$obj->setValue2($tmp);

						$userId = $cart->getCustomerInformation()->getId();
						$new = self::getDirectoryByUserId($userId)  . $tmp;
						if(copy($tmpFile, $new)){
							//ファイルのアップロードに成功したら、仮のアップロードを削除する
							unlink($tmpFile);

							//ストレージプラグインと併用する
							SOY2::import("util.SOYShopPluginUtil");
							if(SOYShopPluginUtil::checkIsActive("store_user_folder")){
								SOY2::imports("module.plugins.store_user_folder.domain.*");
								$storeDao = SOY2DAOFactory::create("SOYShop_UserStorageDAO");
								$storeObj = new SOYShop_UserStorage();
								$storeObj->setUserId($userId);
								$storeObj->setFileName($tmp);
								$storeObj->setToken(md5(time().$userId.$tmp.rand(0,65535)));

								//他の箇所でトランザクションしてる
								$storeDao->insert($storeObj);
							}
						}
					}
					break;
				default:
					$obj->setValue1($value);
					break;
			}

			$this->dao->insert($obj);

			$cart->clearOrderAttribute("order_customfield_" . $config->getFieldId());
		}
	}

	function hasError($param){
		$cart = $this->getCart();

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return false;	//項目がなければfalseを返す

		//paramの再配列
		$array = array();
		foreach($this->list as $obj){
			$value["value"] = (isset($param[$obj->getFieldId()])) ? $param[$obj->getFieldId()] : "";
			$value["label"] = $obj->getLabel();
			$value["type"] = $obj->getType();
			$value["isRequired"] = (int)$obj->getIsRequired();

			$array[$obj->getFieldId()] = $value;
		}
		$param = $array;

		$res = false;
		foreach($param as $key => $obj){
			$error = "";

			//ファイルの場合は許可していない拡張子の時でも調べる
			if($obj["type"] == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_FILE && !self::checkUploadFileExtension($this->list[$key])){
				$error = "許可されていない拡張子です。";
			}

			//必須項目の時のみ調べる
			if($obj["isRequired"] == SOYShop_OrderAttribute::IS_REQUIRED){
				//エラーメッセージ用
				switch($obj["type"]){
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
						if(!isset($obj["value"]) || !strlen($obj["value"])){
							$error = "値が入力されていません。";
						}
						break;
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
						if(
							!isset($obj["value"]) ||
							(is_array($obj["value"]) && !count($obj["value"])) ||
							(is_string($obj["value"]) && !strlen($obj["value"]))
						){
							$error = "選択されていません。";
						}
						break;
					default:
						break;
				}
			}
			if(strlen($error) > 0){
				$cart->setAttribute("order_customfield_" . $key . ".error", $error);
				$res = true;
			}else{
				$cart->clearAttribute("order_customfield_" . $key . ".error");
			}
		}

		return $res;
	}

	function getForm(CartLogic $cart){

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		//出力する内容を格納する
		$array = array();
		foreach($this->list as $config){
			//公開側でフォームを表示しない。管理画面側のみ表示
			if($config->getIsAdminOnly() == SOYShop_OrderAttribute::DISPLAY_ADMIN_ONLY) continue;

			$value = null;

			$value = $cart->getAttribute("order_customfield_" . $config->getFieldId() . ".value");

			$obj = array();
			$obj["name"] = $config->getLabel();

			$html = array();
			if(!is_null($config->getAttributeDescription())){
				$html[] = "<p>" . $config->getAttributeDescription() . "</p>";
			}
			$html[] = "<p>" . $config->getForm($value) . "</p>";
			$obj["description"] = implode("\n", $html);

			//必須項目であるか？
			$obj["isRequired"] = $config->getIsRequired();

			$error = $cart->getAttribute("order_customfield_" . $config->getFieldId() . ".error");
			if(isset($error) && strlen($error)){
				$obj["error"] = $error;
			}else{
				$obj["error"] = null;
			}

			$array[$config->getFieldId()] = $obj;
		}

		return $array;
	}

	function display($orderId){

		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		//リストの再配列
		$array = array();
		foreach($this->list as $obj){
			$array[$obj->getFieldId()]["label"] = $obj->getLabel();
			$array[$obj->getFieldId()]["type"] = $obj->getType();
		}
		$list = $array;
		if(count($list) == 0) return array();

		try{
			$attributes = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			$attributes = array();
		}
		$array = array();
		foreach($attributes as $obj){
			if(!isset($list[$obj->getFieldId()])) continue;
			$value["name"] = $list[$obj->getFieldId()]["label"];

			switch($list[$obj->getFieldId()]["type"]){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					$msg = $obj->getValue1();
					if(strlen($obj->getValue2()) > 0){
						$msg .= ":" . $obj->getValue2();
					}
					$value["value"] = $msg;
					$value["link"] = null;
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_FILE:
					$value["value"] = $obj->getValue2();
					$value["link"] = self::getFilePathByUserId(self::getUserIdByOrderId($orderId)) . $value["value"];
					break;
				default:
					$value["value"] = $obj->getValue1();
					$value["link"] = null;
					break;
			}

			$array[] = $value;
		}

		return $array;
	}

	/**
	 * @param int $orderID
	 * @return array labelとformの連想配列を格納
	 */
	function edit($orderId){
		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		//扱いやすい形に整形
		$attrList = array();
		foreach($this->list as $obj){
			$attrList[$obj->getFieldId()]["label"] = $obj->getLabel();
			$attrList[$obj->getFieldId()]["type"] = $obj->getType();
			$attrList[$obj->getFieldId()]["config"] = $obj->getConfig();
		}
		if(count($attrList) === 0) return array();

		try{
			$attributes = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}

		//値が登録されていなければフィールドを追加
		foreach($attrList as $fieldId => $attrConf){
			if(!isset($attributes[$fieldId])){
				$attrObj = new SOYShop_OrderAttribute();
				$attrObj->setFieldId($fieldId);
				$attrObj->setOrderId($orderId);
				$attributes[$fieldId] = $attrObj;
			}
		}

		$array = array();
		foreach($attributes as $attribute){
			if(!isset($attrList[$attribute->getFieldId()])) continue;
			$attrObjects = array();
			$htmls = array();
			$attrObjects["label"] = $attrList[$attribute->getFieldId()]["label"];
			$name = "Customfield[" . $attribute->getFieldId() . "]";
			$isContinue = false;
			switch($attrList[$attribute->getFieldId()]["type"]){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
					$htmls[] = "<input type=\"text\" name=\"" . $name . "\" value=\"" .$attribute->getValue1() . "\">";
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
					$htmls[] = "<textarea name=\"" . $name . "\">" . $attribute->getValue1() . "</textarea>";
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					if(isset($attrList[$attribute->getFieldId()]["config"]["option"])){
						$options = explode("\n", $attrList[$attribute->getFieldId()]["config"]["option"]);
						$values = explode(",", $attribute->getValue1());
						foreach($options as $option){
							if(strpos($option, "*") === 0) $option = substr($option, 1);
							$htmls[] = "<label>";
							if(in_array(trim($option), $values)){
								$htmls[] = "<input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . trim($option) . "\" checked=\"checked\">";
							}else{
								$htmls[] = "<input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . trim($option) . "\">";
							}
							$htmls[] = trim($option) . "</label>";
						}
					}
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					if(!isset($attrList[$attribute->getFieldId()]["config"]["option"])) {
						$isContinue = true;
						break;
					}
					$options = explode("\n", $attrList[$attribute->getFieldId()]["config"]["option"]);
					if(count($options) === 0) {
						$isContinue = true;
						break;
					}
					foreach($options as $option){
						if(strpos($option, "*") === 0) $option = substr($option, 1);
						$htmls[] = "<label>";
						if($attribute->getValue1() == trim($option)){
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . trim($option) . "\" checked=\"checked\">";
						}else{
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . trim($option) . "\">";
						}
						$htmls[] = trim($option) . "</label>";
					}
					//その他がある場合
					$config = $attrList[$attribute->getFieldId()]["config"];
					if(isset($config["attributeOther"]) && $config["attributeOther"] == 1){
						$htmls[] = "<label>";
						$otherValue = (isset($config["attributeOtherText"]) && strlen($config["attributeOtherText"])  > 0) ? $config["attributeOtherText"] : "その他";
						if($attribute->getValue1() == $otherValue){
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . $otherValue . "\" checked=\"checked\">";
						}else{
							$htmls[] = "<input type=\"radio\" name=\"" . $name . "\" value=\"" . $otherValue . "\">";
						}
						$htmls[] = $otherValue;
						$htmls[] = "</label>";
						$htmls[] = "<input type=\"text\" name=\"Customfield[" . $attribute->getFieldId() . "_other_text]\" value=\"" . $attribute->getValue2() . "\">";
					}
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
					if(!isset($attrList[$attribute->getFieldId()]["config"]["option"])) {
						$isContinue = true;;
						break;
					}
					$options = explode("\n", $attrList[$attribute->getFieldId()]["config"]["option"]);
					if(count($options) === 0) {
						$isContinue = true;
						break;
					}

					$htmls[] = "<select name=\"" . $name . "\">";
					foreach($options as $option){
						if(strpos($option, "*") === 0) $option = substr($option, 1);
						if($attribute->getValue1() == trim($option)){
							$htmls[] = "<option selected>" . trim($option) . "</option>";
						}else{
							$htmls[] = "<option>" . trim($option) . "</option>";
						}
					}
					$htmls[] = "</select>";
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_FILE:
					//何もしない
					$isContinue = true;
					break;
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RICHTEXT:
				default:
					//未実装
					$isContinue = true;
			}

			//switch中でのcontinueの処理をswitchの外に出した PHP7.3対策
			if($isContinue) continue;
			$attrObjects["form"] = implode("\n", $htmls);
			$array[] = $attrObjects;
		}

		return $array;
	}

	/**
	 * 編集画面で編集するための設定内容を取得する
	 * @param int $orderId
	 * @return array saveするための配列
	 */
	function config($orderId){
		self::prepare();
		if(is_null($this->list) || !count($this->list)) return array();

		//リストの再配列
		$array = array();
		foreach($this->list as $key => $obj){
			$values = array();
			$values["label"] = $obj->getLabel();
			$values["type"] = $obj->getType();
			$array[$obj->getFieldId()] = $values;
		}
		$list = $array;

		try{
			$attributes = $this->dao->getByOrderId($orderId);
		}catch(Exception $e){
			$attributes = array();
		}

		//値が登録されていなければフィールドを追加
		foreach($list as $fieldId => $attrConf){
			if(!isset($attributes[$fieldId])){
				$attrObj = new SOYShop_OrderAttribute();
				$attrObj->setFieldId($fieldId);
				$attrObj->setOrderId($orderId);
				$attributes[$fieldId] = $attrObj;
			}
		}

		$array = array();
		foreach($attributes as $obj){
			if(!isset($list[$obj->getFieldId()])) continue;
			$value["label"] = $list[$obj->getFieldId()]["label"];
			$value["value1"] = $obj->getValue1();
			$value["value2"] = $obj->getValue2();
			$value["type"] = $list[$obj->getFieldId()]["type"];

			$array[$obj->getFieldId()] = $value;
		}

		return $array;
	}

	private function checkUploadFileExtension(SOYShop_OrderAttributeConfig $obj){
		if(!strlen($obj->getFileOption())) return true;

		$fileName = $_FILES["customfield_module"]["name"][$obj->getFieldId()];
		if(!strlen($fileName)) return true;	//ファイル名がない場合は調べない

		$extension = trim(mb_strtolower(substr($fileName, strrpos($fileName, ".") + 1)));
		$res = false;
		foreach(explode("\n", $obj->getFileOption()) as $allow){
			if($extension === trim($allow)) $res = true;
		}

		return $res;
	}

	//最新の注文IDを取得する
	private function getNewOrderId(){
		$dao = new SOY2DAO();

		$sql = "SELECT id "
			  ."FROM soyshop_order "
			  ."ORDER BY id desc "
			  ."LIMIT 1";
		try{
			$result = $dao->executeQuery($sql);
		}catch(Exception $e){
			return 1;
		}

		return (isset($result[0]["id"])) ? (int)$result[0]["id"] + 1 : 1;
	}

	private function getCacheDir(){
		$path = SOY2HTMLConfig::CacheDir() . "tmp/";
		if(!file_exists($path)) mkdir($path);
		return $path;
	}

	private function getDirectoryByUserId($userId){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/user/";
		if(!is_dir($dir)) mkdir($dir);

		$dir .= $userId . "/";
		if(!is_dir($dir)) mkdir($dir);
		return $dir;
	}

	private function getFilePathByUserId($userId){
		return SOYSHOP_SITE_URL . "files/user/" . $userId . "/";
	}

	private function getUserIdByOrderId($orderId){
		static $userId;
		if(is_null($userId)){
			try{
				$userId = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getById($orderId)->getUserId();
			}catch(Exception $e){
				//
			}
		}

		return $userId;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "common_order_customfield", "CommonOrderCustomfieldModule");
