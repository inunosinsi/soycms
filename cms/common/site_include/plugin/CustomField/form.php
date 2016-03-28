<?php
class CustomFieldPluginFormPage extends WebPage{

	private $pluginObj;

	const SHOW_INPUT_YES   = 1;
	const SHOW_INPUT_NO    = 0;
	const SHOW_INPUT_LABEL = 2;


	function CustomFieldPluginFormPage(){

	}

	function doPost(){
		
		//CSVエクスポート
		if(isset($_POST["csv"])){
			$this->pluginObj->exportFields();
		}
		
		//CSVインポート
		if(isset($_POST["upload"])){
			$this->pluginObj->importFields();
		}

		if(isset($_POST["display_config"])){
			$this->pluginObj->updateDisplayConfig($_POST["display_config"]);
		}elseif(isset($_POST["delete_submit"])){
			$this->pluginObj->deleteField($_POST["delete_submit"]);
		}else if(isset($_POST["update_submit"]) && isset($_POST["label"]) && isset($_POST["type"])){
			$this->pluginObj->update($_POST["update_submit"],$_POST["label"],$_POST["type"]);
		}else if(isset($_POST["move_up"]) && isset($_POST["field_id"])){
			$this->pluginObj->moveField($_POST["field_id"],-1);
		}else if(isset($_POST["move_down"]) && isset($_POST["field_id"])){
			$this->pluginObj->moveField($_POST["field_id"],1);
		}else if(isset($_POST["update_advance"]) && isset($_POST["config"])){

			//入力欄の表示・非表示設定
			if(isset($_POST["config"]["showInput"])){
				if($_POST["config"]["showInput"] == self::SHOW_INPUT_YES){
					$_POST["config"]["showInput"] = true;
					//常に表示を選んだときはラベル設定をクリアする
					$_POST["config"]["labelId"] = "";
				}elseif($_POST["config"]["showInput"] == self::SHOW_INPUT_LABEL){
					$_POST["config"]["showInput"] = true;
				}elseif($_POST["config"]["showInput"] == self::SHOW_INPUT_NO){
					$_POST["config"]["showInput"] = false;
				}
			}

			$this->pluginObj->updateAdvance($_POST["update_advance"],(object)$_POST["config"]);

		}else{
			$data = new CustomField($_POST);
			if(strlen($data->getId())>0){
				$this->pluginObj->insertField($data);
			}
		}

		CMSUtil::notifyUpdate();
		CMSPlugin::redirectConfigPage();

	}

	function execute(){
		WebPage::WebPage();

		//$this->pluginObj->importFields();
		//$this->pluginObj->deleteAllFields();

		$this->createAdd("field_table","HTMLModel",array(
			"visible"=> count($this->pluginObj->customFields)
		));

		$this->createAdd("add_field","HTMLModel",array(
			"visible"=> count($this->pluginObj->customFields)<1
		));

		//ラベルの取得
		$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();

		$this->createAdd("field_list","FieldList",array(
			"list"=>$this->pluginObj->customFields,
			"labels" => $labels
		));

		/* カスタムフィールド全体の設定変更用 */
		$this->createAdd("config_display_title","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config[display_title]",
			"value"    => 1,
			"selected" => $this->pluginObj->displayTitle,
			"isBoolean"=> true,
			"elementId"=> "config_display_title",
			"label"    => "「カスタムフィールド」を表示する",
			"onclick"  => "update_display_sample()"
		));
		$this->createAdd("config_display_id","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config[display_id]",
			"value"    => 1,
			"selected" => $this->pluginObj->displayID,
			"isBoolean" => true,
			"elementId"=> "config_display_id",
			"label"    => "IDを表示する",
			"onclick"  => "update_display_sample()"
		));

	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/form.html";
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}



class FieldList extends HTMLList{

	private $labels = array();

	function populateItem($entity, $i){
		static $i = 0;
		$i++;

		/* 情報表示用 */
		$this->createAdd("label","HTMLLabel",array(
			"text"=>$entity->getLabel(),
			"id" => "label_text_" . $i,
		));

		$this->createAdd("id","HTMLLabel",array(
			"text"=> $entity->getId(),
		));

		$this->createAdd("type","HTMLLabel",array(
			"text"=> (isset(CustomField::$TYPES[$entity->getType()])) ? CustomField::$TYPES[$entity->getType()] : "",
			"id" => "type_text_" . $i,
		));

		$this->createAdd("display_form","HTMLLabel",array(
			"text"=>'cms:id="'.$entity->getId().'"'
		));


		/* カスタムフィールド設定変更用 */
		$this->createAdd("toggle_update","HTMLLink",array(
			"link" => "javascript:void(0)",
			"onclick" => '$(\'#label_input_'.$i.'\').show();' .
						'$(\'#label_text_'.$i.'\').hide();' .
						'$(\'#type_select_'.$i.'\').show();' .
						'$(\'#type_text_'.$i.'\').hide();' .
						'$(\'#update_link_'.$i.'\').show();' .
						'$(this).hide();'
		));

		$this->createAdd("update_link","HTMLLink",array(
			"link" => "javascript:void(0)",
			"id" => "update_link_" . $i,
			"onclick" => '$(\'#update_submit_'.$i.'\').click();' .
						'return false;'
		));

		$this->createAdd("update_submit","HTMLInput",array(
			"name" => "update_submit",
			"value" => $entity->getId(),
			"id" => "update_submit_".$i
		));

		$this->createAdd("label_input","HTMLInput",array(
			"name" => "label",
			"id" => "label_input_" . $i,
			"value" => $entity->getLabel(),
		));

		$this->createAdd("type_select","HTMLSelect",array(
			"name" => "type",
			"options" => CustomField::$TYPES,
			"id" => "type_select_" . $i,
			"selected" => $entity->getType(),
		));

		/* 順番変更用 */
		$this->createAdd("field_id","HTMLInput",array(
			"name" => "field_id",
			"value" => $entity->getId(),
		));


		/* 削除用 */
		$this->createAdd("delete_submit","HTMLInput",array(
			"name" => "delete_submit",
			"value" => $entity->getId(),
			"id" => "delete_submit_".$i
		));

		$this->createAdd("delete","HTMLLink",array(
			"text"=>"削除",
			"link"=>"javascript:void(0);",
			"onclick"=>'if(confirm("delete \"'.$entity->getLabel().'\"?")){$(\'#delete_submit_'.$i.'\').click();}return false;'
		));

		/* 高度な設定 */
		$this->createAdd("toggle_config","HTMLLink",array(
			"link" => "javascript:void(0)",
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_'.$i.'\').toggle();',
			"style" => (!$entity->getShowInput() OR $entity->getLabelId() OR $entity->getDefaultValue() OR $entity->getEmptyValue() OR $entity->getDescription()) ? "background-color:yellow;" : ""
		));

		$this->createAdd("field_config","HTMLModel",array(
			"id" => "field_config_" . $i
		));

		//表示の切り替え：表示/非表示/ラベルと連動
		$this->createAdd("editer_show","HTMLCheckBox",array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_YES,
			"selected" => $entity->getShowInput() && strlen($entity->getLabelId())==0,
			"label" => "常に表示",
		));
		$this->createAdd("editer_hide","HTMLCheckBox",array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_NO,
			"selected" => !$entity->getShowInput(),
			"label" => "常に隠す",
		));
		$this->createAdd("editer_label","HTMLCheckBox",array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_LABEL,
			"selected" => strlen($entity->getLabelId()),
			"label" => "ラベルと連動",
		));
		$this->createAdd("labels","HTMLSelect",array(
			"options" => $this->labels,
			"property" => "caption",
			"name" => "config[labelId]",
			"selected" => $entity->getLabelId(),
		));

		$this->createAdd("default_value","HTMLInput",array(
			"name" => "config[defaultValue]",
			"value" => $entity->getDefaultValue()
		));

		$this->createAdd("empty_hide","HTMLCheckBox",array(
			"name" => "config[hideIfEmpty]",
			"value" => 1,
			"selected" => $entity->getHideIfEmpty(),
			"label" => "表示しない",
		));
		$this->createAdd("empty_show","HTMLCheckBox",array(
			"name" => "config[hideIfEmpty]",
			"value" => 0,
			"selected" => !$entity->getHideIfEmpty(),
			"label" => "指定の値を出力",
		));
		$this->createAdd("empty_value","HTMLInput",array(
			"name" => "config[emptyValue]",
			"value" => $entity->getEmptyValue()
		));

		$this->createAdd("output","HTMLInput",array(
			"name" => "config[output]",
			"value" => $entity->getOutput()
		));
		
		$this->createAdd("description","HTMLInput",array(
			"name" => "config[description]",
			"value" => $entity->getDescription()
		));

		$this->createAdd("use_extra", "HTMLModel", array(
			"visible" => $entity->hasExtra(),
		));
		
		$this->createAdd("extra_outputs", "HTMLTextArea", array(
			"name" => "config[extraOutputs]",
			"value" => $entity->getExtraOutputs(),
		));

		$this->createAdd("option","HTMLTextArea",array(
			"name" => "config[option]",
			"value" => $entity->getOption()
		));

		$this->createAdd("with_options","HTMLModel",array(
			"visible" => $entity->hasOption()
		));

		$this->createAdd("update_advance","HTMLInput",array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_'.$i.'\').click();return false;'
		));

		$this->createAdd("update_advance_submit","HTMLInput",array(
			"name" => "update_advance",
			"value" => $entity->getId(),
			"id" => "update_advance_submit_".$i
		));

	}

	function getLabels() {
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}
}

?>
