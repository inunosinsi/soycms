<?php
/*
 */
include(dirname(__FILE__) . "/common/common.php");
class CommonAdditionOptionCustomField extends SOYShopItemCustomFieldBase{

	private $dao;
	private $item;

	function doPost(SOYShop_Item $item){

		$this->item = $item;

		$this->dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$array = $this->dao->getByItemId($item->getId());

		if(isset($_POST["addition_option_price"])){

			//表示設定を行う
			$key = "addition_option_flag";
			$publishFlag = (isset($_POST[$key])) ? 1 : 0;

			try{
				if(isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue($publishFlag);
					$this->dao->update($obj);
				}else{
					$obj = new SOYShop_ItemAttribute();
					$obj->setItemId($item->getId());
					$obj->setFieldId($key);
					$obj->setValue($publishFlag);

					$this->dao->insert($obj);
				}
			}catch(Exception $e){
				//
			}

			//加算額の設定を行う
			$key = "addition_option_price";
			$price = soyshop_convert_number($_POST[$key], 0);

			try{
				if(isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue($price);
					$this->dao->update($obj);
				}else{
					$obj = new SOYShop_ItemAttribute();
					$obj->setItemId($item->getId());
					$obj->setFieldId($key);
					$obj->setValue($price);
					$this->dao->insert($obj);
				}
			}catch(Exception $e){
				//
			}

			//加算項目の設定
			$key = "addition_option_name";
			$name = $_POST[$key];

			try{
				if(isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue($name);
					$this->dao->update($obj);
				}else{
					$obj = new SOYShop_ItemAttribute();
					$obj->setItemId($item->getId());
					$obj->setFieldId($key);
					$obj->setValue($name);
					$this->dao->insert($obj);
				}
			}catch(Exception $e){
				//
			}

			//加算時の文言設定を行う
			$key = "addition_option_text";
			$text = $_POST[$key];

			try{
				if(isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue($text);
					$this->dao->update($obj);
				}else{
					$obj = new SOYShop_ItemAttribute();
					$obj->setItemId($item->getId());
					$obj->setFieldId($key);
					$obj->setValue($text);
					$this->dao->insert($obj);
				}
			}catch(Exception $e){
				//
			}
		}
	}

	function getForm(SOYShop_Item $item){

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$array = $dao->getByItemId($item->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		if(isset($array["addition_option_price"])){
			$value = $array["addition_option_flag"]->getValue();
			$flag = ($value) ? true : false;
			$name = (isset($array["addition_option_name"])) ? $array["addition_option_name"]->getValue() : "";
			$price = (isset($array["addition_option_price"])) ? $array["addition_option_price"]->getValue() : "";
			$text = (isset($array["addition_option_text"]))?  $array["addition_option_text"]->getValue() : "";
		}else{
			$config = CommonAdditionCommon::getConfig();
			$flag = false;
			$name = $config["name"];
			$price = $config["price"];
			$text = $config["text"];
		}

		$style = "style=\"text-align:right;ime-mode:inactive;\"";

		$html = array();

		$html[] = "<br>";
		$html[] = "<div class=\"alert alert-info\">加算オプションの設定</div>";

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>公開側の表示設定</label><br>";
		$html[] = "<label>";
		$html[] = "<input type=\"checkbox\" name=\"addition_option_flag\" value=\"1\" ";
		if($flag){
			$html[] = "checked=\"checked\"";
		}
		$html[] = " />";
		$html[] = "公開側に加算オプションを表示する</label>";
		$html[] = "</div>";

		$html[] = "<div class=\"form-group\">";
		$html[] = "	<label>加算項目(カートに入れた時に表示されます)</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<input type=\"text\" name=\"addition_option_name\" class=\"form-control\" value=\"" . $name."\" />";
		$html[] = "	</div>";
		$html[] = "	<br>";
		$html[] = "	<label>加算額の設定</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<input type=\"text\" name=\"addition_option_price\" class=\"form-control\" value=\"" . $price."\" " . $style." size=\"5\" />&nbsp;円";
		$html[] = "	</div>";
		$html[] = "	<br>";
		$html[] = "	<label>加算時の文言</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<textarea name=\"addition_option_text\" class=\"form-control\">".htmlspecialchars($text, ENT_QUOTES, "UTF-8")."</textarea>";
		$html[] = "		<div class=\"alert alert-warning\">※##PRICE##は公開側で加算額で設定した値に置換されます</div>";
		$html[] = "	</div>";
		$html[] = "</div>";
		$html[] = "<div class=\"alert alert-info\">加算オプションの設定ここまで</div>";

		return implode("\n", $html);
	}

	function onOutput($htmlObj, SOYShop_Item $item){

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$array = $dao->getByItemId($item->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		if(isset($array["addition_option_price"])){
			$value = $array["addition_option_flag"]->getValue();
			$visible = ($value) ? true : false;
			$price = $array["addition_option_price"]->getValue();
			$text = $array["addition_option_text"]->getValue();
			$text = str_replace("##PRICE##", $price, $text);
		}else{
			$visible = false;
			$price = "";
			$text = "";
		}

		$html = array();

		if($visible){
			//valueには商品IDを入れておく
			$html[] = "<input type=\"hidden\" name=\"item_option[addition_option]\" value=\"0\" />";
			$html[] = "<input type=\"checkbox\" name=\"item_option[addition_option]\" value=\"" . $item->getId() . "\" id=\"addition_option\">";
			$html[] = "<label for=\"addition_option\">" . nl2br(htmlspecialchars($text, ENT_QUOTES, "UTF-8")) . "</label>";
		}

		$htmlObj->addModel("addition_option_visible", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => $visible
		));

		$htmlObj->addLabel("addition_option", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => implode("\n", $html)
		));
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_addition_option", "CommonAdditionOptionCustomField");
