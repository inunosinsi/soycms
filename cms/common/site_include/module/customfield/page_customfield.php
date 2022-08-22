<?php
function soycms_page_customfield($html, $htmlObj){
	$obj = $htmlObj->create("page_customfield", "HTMLTemplatePage", array(
		"arguments" => array("page_customfield", $html)
	));
	
	if(CMSPlugin::activeCheck("PageCustomField")){
		SOY2::import("site_include.plugin.PageCustomField.util.PageCustomfieldUtil");
		include_once(SOY2::RootDir() . "site_include/plugin/CustomFieldAdvanced/func/func.php");	//便利な関数
		
		include_once(SOY2::RootDir() . "site_include/plugin/PageCustomField/PageCustomField.php");
		$pluginObj = CMSPlugin::loadPluginConfig("PageCustomFieldPlugin");
		if(is_null($pluginObj)) $pluginObj = new PageCustomFieldPlugin();
		
		$customFields = $pluginObj->customFields;
		$fields = (count($customFields)) ? PageCustomfieldUtil::getCustomFields($_SERVER["SOYCMS_PAGE_ID"], $customFields) : array();
		
		if(count($fields)){
			$isListField = PageCustomfieldUtil::checkIsListField($customFields);	//
			$isDlListField = PageCustomfieldUtil::checkIsDlListField($customFields);	//
			
			foreach($fields as $field){

				//設定を取得
				$master = (isset($customFields[$field->getId()])) ? $customFields[$field->getId()] : null;
				
				$class = "CMSLabel";
				$attr = array(
					"html"	   => $field->getValue(),
					"soy2prefix" => "cms",
				);

				//カスタムフィールドの設定が取れるときの動作（たとえば同じサイト内の場合）
				if($master instanceof PageCustomField){
					//$attr["html"]に改めて値を入れ直す時に使用するフラグ
					$resetFlag = true;

					//値が設定されていないなら初期値を使う
					if(is_null($field->getValue())) $field->setValue($master->getDefaultValue());
					$fieldValue = (is_string($field->getValue())) ? $field->getValue() : "";

					//空の時の動作
					if(!strlen($fieldValue)){
						if($master->getHideIfEmpty()){
							//空の時は表示しない
							$attr["visible"] = false;
						}else{
							//空の時の値
							if(is_string($master->getEmptyValue())) $field->setValue($master->getEmptyValue());
						}
					}

					//タイプがリンクの場合はここで上書き
					if($master->getType() == "link"){
						$class = "HTMLLink";
						$attr["link"] = (strlen($fieldValue)) ? $fieldValue : null;
						unset($attr["html"]);
						$resetFlag = false;

					//画像の場合
					}else if($master->getType() == "image"){
						$class = "HTMLImage";
						$attr["src"] = (strlen($fieldValue)) ? $fieldValue : null;
						unset($attr["html"]);
						$resetFlag = false;
					}

					//リンク、もしくは画像の場合、パスを表示するためのcms:id
					if($master->getType() == "link" || $master->getType() == "image"){
						$obj->addLabel($field->getId() . "_text", array(
							"soy2prefix" => "cms",
							"text" => $fieldValue
						));
					}

					//複数行テキストの場合は\n\rを<br>に変換するタグを追加
					if($master->getType() == "textarea"){
						$obj->addLabel($field->getId() . "_raw", array(
							"soy2prefix" => "cms",
							"html" => $fieldValue
						));
						$attr["html"] = nl2br($fieldValue);
						$obj->addLabel($field->getId() . "_br_mode", array(
							"soy2prefix" => "cms",
							"html" => $attr["html"]
						));
						$resetFlag = false;
					}

					//上で空の時の値が入るかも知れず、下でunsetされる可能性があるのでここで設定し直す。
					if($resetFlag) $attr["html"] = $fieldValue;
					
					//リストフィールド listcomponentはCustomfieldAdvancedのファイルを流用
					if($master->getType() == "list" && is_string($attr["html"])){
						if(!class_exists("ListFieldListComponent")) SOY2::import("site_include.plugin.CustomFieldAdvanced.component.ListFieldListComponent");
						$obj->createAdd($field->getId() . "_list", "ListFieldListComponent", array(
							"soy2prefix" => "cms",
							"list" => soy2_unserialize($attr["html"])
						));
					}

					//定義型リストフィールド listcomponentはCustomfieldAdvancedのファイルを流用
					if($master->getType() == "dllist" && is_string($attr["html"])){
						if(!class_exists("DlListFieldListComponent")) SOY2::import("site_include.plugin.CustomFieldAdvanced.component.DlListFieldListComponent");
						$obj->createAdd($field->getId() . "_dl_list", "DlListFieldListComponent", array(
							"soy2prefix" => "cms",
							"list" => soy2_unserialize($attr["html"])
						));
					}

					//属性に出力
					if(is_string($master->getOutput()) && strlen($master->getOutput()) > 0){

						//リンクタイプ以外でhrefを使う場合
						if($master->getOutput() == "href" && $master->getType() != "link"){
							$class = "HTMLLink";
							$attr["link"] = $fieldValue;

						//下方互換
						}else if($master->getType() == "image" && $master->getOutput() == "src"){
							//上で処理をしているため何もしない

						//その他
						}else{
							$class = "HTMLModel";
							$attr[$master->getOutput()] = $fieldValue;
						}

						/*
						if(strlen($master->getExtraOutputs()) > 0 && is_array($field->getExtraValues())){
							foreach($field->getExtraValues() as $key => $value){
								$attr["attr:" . $key] = $value;
							}
						}
						*/
						unset($attr["html"]);//HTMLModelなのでunsetしなくても出力されないはず
					}

					//追加属性を出力
					if(is_string($master->getExtraOutputs()) && strlen($master->getExtraOutputs()) > 0){
						$extraOutputs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $master->getExtraOutputs()));
						$extraValues = $field->getExtraValues();
						foreach($extraOutputs as $key => $extraOutput){
							$extraOutput = trim($extraOutput);
							$attr[$extraOutput] = (is_array($extraValues) && isset($extraValues[$extraOutput])) ? $extraValues[$extraOutput] : "";
						}

						unset($attr["html"]);//HTMLModelなのでunsetしなくても出力されないはず
					}

					//ペアフィールド
					if($master->getType() == "pair" && is_string($master->getExtraValues())){
						$extraValues = soy2_unserialize($master->getExtraValues());

						//後方互換
						if(isset($extraValues["pair"]) && is_array($extraValues["pair"])) $extraValues = $extraValues["pair"];

						if(count($extraValues)){
							foreach($extraValues as $idx => $pairValues){
								$_hash = (is_string($fieldValue) && strlen($fieldValue)) ? CustomfieldAdvancedUtil::createHash($fieldValue) : null;
								$pairValue = (isset($_hash) && isset($pairValues[$_hash])) ? $pairValues[$_hash] : "";

								$obj->addModel($field->getId() . "_pair_" . ($idx + 1) . "_visible", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$obj->addModel($field->getId() . "_pair_" . ($idx + 1) . "_is_not_empty", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$obj->addModel($field->getId() . "_pair_" . ($idx + 1) . "_is_empty", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) === 0)
								));

								$obj->addLabel($field->getId() . "_pair_" . ($idx + 1), array(
									"soy2prefix" => "cms",
									"html" => $pairValue
								));
							}
						}
					}
				}
				
				$obj->addModel($field->getId() . "_visible", array(
					"soy2prefix" => "cms",
					"visible" => (strlen($fieldValue))
				));

				$obj->addModel($field->getId() . "_is_not_empty", array(
					"soy2prefix" => "cms",
					"visible" => (strlen($fieldValue))
				));

				$obj->addModel($field->getId()."_is_empty", array(
					"soy2prefix" => "cms",
					"visible" => (!strlen($fieldValue))
				));
				
				//SOY2HTMLのデフォルトの _visibleがあるので、$field->getId()."_visible"より後にこれをやらないと表示されなくなる
				$obj->createAdd($field->getId(), $class, $attr);
			}
		}
	}

	$obj->display();
}
