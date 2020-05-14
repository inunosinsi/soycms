<?php

SOY2::import("module.plugins.custom_icon_field.util.CustomIconFieldUtil");
class CustomIconField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "custom_icon_field";

	function doPost(SOYShop_Item $item){

		$path = (isset($_POST[self::PLUGIN_ID])) ? $_POST[self::PLUGIN_ID] : null;

		//アイコンパスをきれいにする。
		$image = array();
		if(!is_null($path)){
			$icons = explode(",", $path);
			foreach($icons as $icon){
				if(!preg_match('/(jpg|jpeg|gif|png)$/', $icon)) continue;
				$image[] = $icon;
			}
			$imagePath = implode(",", $image);
			$iconsPath = "," . $imagePath;
		}else{
			$iconsPath = "";
		}

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$array = $dao->getByItemId($item->getId());

		$value = $iconsPath;

		try{
			if(isset($array[self::PLUGIN_ID])){
				$obj = $array[self::PLUGIN_ID];
				$obj->setValue($value);
				$dao->update($obj);
			}else{
				$obj = new SOYShop_ItemAttribute();
				$obj->setItemId($item->getId());
				$obj->setFieldId(self::PLUGIN_ID);
				$obj->setValue($value);

				$dao->insert($obj);
			}
		}catch(Exception $e){
			//
		}
	}

	function getForm(SOYShop_Item $item){

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$attr = $dao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
		}

		$html = array();
		$html[] = "\n";
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"" . self::PLUGIN_ID . "\">カスタムアイコンフィールド (cms:id=\"" . self::PLUGIN_ID . "\")</label>\n";
		$html[] = "<p class=\"mb\" id=\"" . self::PLUGIN_ID . "_text\">";

		$icons = array();

		if(!is_null($attr->getValue()) && strlen($attr->getValue())){
			$icons = explode(",", $attr->getValue());

			$image = array();
			foreach($icons as $icon){
				if(!preg_match('/(jpg|jpeg|gif|png)$/', $icon)) continue;
				$image[] = "<img src=\"" . CustomIconFieldUtil::getIconPath() . $icon . "\" />";
			}
			$html[] = implode(" ", $image);
		}

		$html[] = "\n";

		if(count($icons)){
			$html[] = "<input name=\"" . self::PLUGIN_ID . "\" id=\"" . self::PLUGIN_ID . "\" type=\"hidden\" value=\"" . implode(",", $icons) . "\" />\n";
		}else{
			$html[] = "<input name=\"" . self::PLUGIN_ID . "\" id=\"" . self::PLUGIN_ID . "\" type=\"hidden\" value=\"\" />\n";
		}

		$html[] = "<a class=\"btn btn-primary btn-sm\" href=\"javascript:void(0);\" onclick=\"$(this).hide();$('#icon_list').show();\">選択する</a>\n";
		$html[] = "<ul id=\"icon_list\" style=\"display:none;\">\n";

		$files = @scandir(CustomIconFieldUtil::getIconDirectory());
		if(!$files) $files = array();

		foreach($files as $file){
			if(!preg_match('/(jpg|jpeg|gif|png)$/', $file)) continue;
			if(array_search($file, $icons)){
				$html[] = "<li><a class=\"selected_category\" href=\"javascript:void(0);\" onclick=\"onClickIconLeaf('" . $file . "',this);\">";
			}else{
				$html[] = "<li><a class=\"\" href=\"javascript:void(0);\" onclick=\"onClickIconLeaf('" . $file . "',this);\">";
			}

			$html[] = "<img src=\"" . CustomIconFieldUtil::getIconPath() . $file . "\" />";
			$html[] = "</a></li>\n";
		}

		$html[] = "</ul>\n";
		$html[] = "</div>\n";

		$html[] = "<script>\n";

		$script = file_get_contents(dirname(__FILE__) . "/soyshop.item.customfield.js");
		$html[] = $script;

		$html[] ="</script>\n";

		return implode("", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$attr = $dao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
		}

		$icons = (!is_null($attr->getValue())) ? explode(",", $attr->getValue()) : array();

		$image = array();
		$html = "";
		if(is_array($icons) && count($icons)){
			foreach($icons as $icon){
				if(preg_match('/(jpg|jpeg|gif|png)$/', $icon, $tmp)){

					//言語設定に対応してファイル名の修正
					if(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE !== "jp"){
						$extension = "." . trim($tmp[0]);
						$langIcon = str_replace($extension, "_" . SOYSHOP_PUBLISH_LANGUAGE . $extension, $icon);
						if(file_exists(CustomIconFieldUtil::getIconDirectory() . $langIcon)) $icon = $langIcon;
					}
					$image[] = "<img src=\"" . CustomIconFieldUtil::getIconPath() . $icon . "\" class=\"" . self::PLUGIN_ID . "\" />";
				}
			}
			$html = implode(" ", $image);
		}

		$htmlObj->addLabel(self::PLUGIN_ID, array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $html
		));
	}

	function onDelete($id){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($id);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "custom_icon_field", "CustomIconField");
