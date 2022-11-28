<?php
class BonusdownloadConfigFormPage extends WebPage{

	//ダウンロード販売補助プラグイン
	const DOWNLOAD_ASSISTANT_PLUGIN_ID = "download_assistant";

	private $config;
	private $files = array();
	private $installed;	//ダウンロード購入補助プラグインがインストールされているか？

	function doPost(){

		if(soy2_check_token()){

			//ファイルアップロード
			if(isset($_POST["submit_upload"])){

				//削除
				if(isset($_POST["delete_filename"]) && strlen($_POST["delete_filename"]) > 0){
					$filename = $_POST["delete_filename"];
					$logic = new BonusDownloadFileLogic();
					$logic->deleteFile($filename);
					$this->config->redirect("notice_bonus_file_deleted");
				}

				//アップロード
				if(isset($_FILES["bonus_file"]) && isset($_FILES["bonus_file"])){
					$file = $_FILES["bonus_file"];
					$logic = new BonusDownloadFileLogic();
					if($logic->checkUpload($file)){
						$res = $logic->uploadFile($file);
					}

					if($res){
						$this->config->redirect("notice_bonus_file_uploaded");
					}else{
						$this->config->redirect("notice_bonus_file_uploaded_fail");
					}

				}

			}


			//購入特典内容
			if(isset($_POST["submit_content"]) && isset($_POST["Type"]) && isset($_POST["Content"]) && is_array($_POST["Content"])){
				$config = BonusDownloadConfigUtil::getConfig();
				$content = $_POST["Content"];

				//購入特典タイプ
				if(strlen($_POST["Type"]) > 0){
					$config["type"] = $_POST["Type"];
				}

				//購入特典名
				if(isset($content["name"])){
					$config["name"] = $content["name"];
				}

				//購入特典HTML
				if(isset($content["html"])){
					$config["html"] = $content["html"];
				}

				//購入特典 ダウンロード期間
				if(isset($content["download_files.time_limit"])){
					$config["download_files.time_limit"] = $content["download_files.time_limit"];
				}

				//購入特典URL
				if(isset($content["download_url"])){
					$config["download_url"] = $content["download_url"];
				}

				//購入特典ファイル 選択
				if(isset($_POST["BonusFileSelected"]) && is_array($_POST["BonusFileSelected"])){
					$files = $_POST["BonusFileSelected"];
					$selected = array();
					foreach($files as $key => $value){
						if($value)$selected[] = $key;
					}
					$config["download_files"] = $selected;
				}

				//公開状態
				if(isset($content["status"])){
					$config["status"] = $content["status"];
				}

				BonusDownloadConfigUtil::setConfig($config);
				$this->config->redirect("notice_updated");
			}

			//購入特典条件
			if(isset($_POST["submit_condition"]) && isset($_POST["Condition"]) && is_array($_POST["Condition"])){
				$condition = BonusDownloadConditionUtil::getCondition();
				$post = $_POST["Condition"];

				//合計金額 オンオフ
				if(isset($post["price_checkbox"]) && is_numeric($post["price_checkbox"])){
					$condition["price_checkbox"] = $post["price_checkbox"];
				}

				//合計金額 金額
				if(isset($post["price_value"]) && is_numeric($post["price_value"])){
					$condition["price_value"] = $post["price_value"];
				}

				//合計商品数 オンオフ
				if(isset($post["amount_checkbox"]) && is_numeric($post["amount_checkbox"])){
					$condition["amount_checkbox"] = $post["amount_checkbox"];
				}

				//合計商品数 金額
				if(isset($post["amount_value"]) && is_numeric($post["amount_value"])){
					$condition["amount_value"] = $post["amount_value"];
				}

				//組み合わせ
				if(isset($post["combination"]) && is_numeric($post["combination"])){
					$condition["combination"] = $post["combination"];
				}

				BonusDownloadConditionUtil::setCondition($condition);
				$this->config->redirect("notice_updated");
			}


		}

	}

	/**
	 * コンストラクタ
	 */
	function __construct(){
		$this->files =  BonusDownloadConfigUtil::getBonusFiles();
	}

	function execute(){
		parent::__construct();
		$config = BonusDownloadConfigUtil::getConfig();

		$this->addLabel("bonus_file_type_list_text", array(
			"text" => $this->getAllowExtensions()
		));

		$this->addLabel("bonus_file_dir", array(
			"text" => BonusDownloadConfigUtil::getUploadDir()
		));

		//アップロードファイル存在時 表示
		$this->addModel("display_bonus_files", array(
			"visible" => count($this->files)
		));

		//アップロードファイルがなかった時 表示
		$this->addModel("display_no_bonus_files", array(
			"visible" => !count($this->files)
		));

		$this->buildUploadForm();//ファイルアップロード
		$this->buildContentForm($config);//内容
		$this->buildConditionForm();//条件


		/* ダウンロード販売補助プラグインの初回インストールチェック */
		$this->installed = $this->checkDownloadAssistantPlugin();

		//未インストール時
		$this->addModel("display_download_assistant_plugin_install", array(
			"visible" => !$this->installed
		));

		$this->addLink("download_assitant_plugin_link", array(
			"link" => $this->buildDownloadAssistantPluginLink()
		));

		//インストール済み、あるいはsoyshop_downloadテーブルが存在する
		$this->addModel("display_download_assistant_plugin_not_install", array(
			"visible" => $this->installed
		));


		/* notice */

		//変更しました
		$this->addModel("notice_updated", array(
			"visible" => isset($_GET["notice_updated"])
		));

		//ファイルをアップロードしました
		$this->addModel("notice_bonus_file_uploaded", array(
			"visible" => isset($_GET["notice_bonus_file_uploaded"])
		));

		//ファイルをアップロードに失敗しました
		$this->addModel("notice_bonus_file_uploaded_fail", array(
			"visible" => isset($_GET["notice_bonus_file_uploaded_fail"])
		));

		//ファイルを削除しました
		$this->addModel("notice_bonus_file_deleted", array(
			"visible" => isset($_GET["notice_bonus_file_deleted"])
		));



	}

	/**
	 * ファイルアップロードフォーム
	 */
	function buildUploadForm(){
		$this->addForm("upload_form");

		//アップロードファイル input
		$this->addInput("bonus_file_upload", array(
			"name" => "bonus_file"
		));

		//アップロードファイル 一覧 削除リンク
		$this->createAdd("bonus_file_list", "BonusFileListComponent", array(
			"list" => $this->files,
		));

	}

	/**
	 * 購入特典内容のフォーム
	 * @param array $config 購入特典の設定
	 */
	function buildContentForm($config){
		$this->addForm("content_form");

		//購入特典名
		$this->addInput("bonus_name", array(
			"name" => "Content[name]",
			"value" => $config["name"]
		));

		//購入特典内容のHTML
		$this->addTextarea("bonus_html", array(
			"text" => $config["html"],
			"name" => "Content[html]",

		));

		//購入特典タイプ アップロードしたファイルをURLに変換
		$this->addCheckbox("bonus_type_file", array(
			"name" => "Type",
			"value" => BonusDownloadConfigUtil::TYPE_FILE,
			"selected" => ($config["type"] == BonusDownloadConfigUtil::TYPE_FILE),
			"elementId" => "bonus_type_file",
		));

		//購入特典タイプ URLを出力
		$this->addCheckbox("bonus_type_text", array(
			"name" => "Type",
			"value" => BonusDownloadConfigUtil::TYPE_TEXT,
			"selected" => ($config["type"] == BonusDownloadConfigUtil::TYPE_TEXT),
			"elementId" => "bonus_type_text",
		));

		//購入特典ファイル ダウンロード期間
		$this->addInput("bonus_download_time_limit", array(
			"name" => "Content[download_files.time_limit]",
			"value" => $config["download_files.time_limit"]
		));


		//購入特典URL
		$this->addInput("bonus_download_url", array(
			"name" => "Content[download_url]",
			"value" => $config["download_url"]
		));

		//アップロードファイル 一覧　購入特典選択
		$selected = $config["download_files"];
		$this->createAdd("bonus_file_selected_list", "BonusFileListComponent", array(
			"list" => $this->files,
			"selected" => $selected
		));

		//公開状態 非公開
		$this->addCheckbox("status_type_inactive", array(
			"name" => "Content[status]",
			"value" => BonusDownloadConfigUtil::STATUS_INACTIVE,
			"selected" => ($config["status"] == BonusDownloadConfigUtil::STATUS_INACTIVE),
			"elementId" => "status_type_inactive",
		));

		//公開状態 公開
		$this->addCheckbox("status_type_active", array(
			"name" => "Content[status]",
			"value" => BonusDownloadConfigUtil::STATUS_ACTIVE,
			"selected" => ($config["status"] == BonusDownloadConfigUtil::STATUS_ACTIVE),
			"elementId" => "status_type_active",
		));

	}

	/**
	 * 条件のフォーム
	 */
	function buildConditionForm(){
		$this->addForm("condition_form");
		$condition = BonusDownloadConditionUtil::getCondition();

		//合計金額 チェックボックス
		$this->addCheckbox("condition_price_checkbox", array(
			"name" => "Condition[price_checkbox]",
			"value" => 1,
			"selected" => $condition["price_checkbox"],
			"isBoolean" => true,
			"elementId" => "condition_price_checkbox",
		));

		//合計金額 入力
		$this->addInput("condition_price_value", array(
			"name" => "Condition[price_value]",
			"value" => $condition["price_value"]
		));

		//合計商品数 チェックボックス
		$this->addCheckbox("condition_amount_checkbox", array(
			"name" => "Condition[amount_checkbox]",
			"value" => 1,
			"selected" => $condition["amount_checkbox"],
			"isBoolean" => true,
			"elementId" => "condition_amount_checkbox"
		));

		//合計商品数
		$this->addInput("condition_amount_value", array(
			"name" => "Condition[amount_value]",
			"value" => $condition["amount_value"]
		));


		/* 条件の適用 */

		//両方の条件に一致
		$this->addCheckbox("condition_combination_all", array(
			"name" => "Condition[combination]",
			"value" => BonusDownloadConditionUtil::COMBINATION_ALL,
			"selected" => ($condition["combination"] == BonusDownloadConditionUtil::COMBINATION_ALL),
			"elementId" => "condition_combination_all"
		));

		//片方の条件に一致
		$this->addCheckbox("condition_combination_any", array(
			"name" => "Condition[combination]",
			"value" => BonusDownloadConditionUtil::COMBINATION_ANY,
			"selected" => ($condition["combination"] == BonusDownloadConditionUtil::COMBINATION_ANY),
			"elementId" => "condition_combination_any"
		));


	}

	function getAllowExtensions(){
		$extensions = BonusDownloadConfigUtil::getAllowExtension();
		$allowExtensionText = "";
		foreach($extensions as $key => $extension){
			if(strlen($allowExtensionText) > 0) $allowExtensionText .= ", ";
			$allowExtensionText .= $key;
		}
		return $allowExtensionText;
	}

	/**
	 * soyshop_downloadテーブルが存在するかどうか
	 * @return boolean
	 */
	function checkDownloadAssistantPlugin(){
		$dao = new SOY2DAO();
		$sql = "SELECT id FROM soyshop_download";

		try{
			$dao->executeQuery($sql);
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	function buildDownloadAssistantPluginLink(){
		return (!$this->installed) ? SOY2PageController::createLink("Plugin.Detail." . soyshop_get_plugin_object(self::DOWNLOAD_ASSISTANT_PLUGIN_ID)->getId()) : null;
	}

//	function getTemplateFilePath(){
//		return dirname(__FILE__) . "/BonusdownloadConfigFormPage.html";
//	}

	function getConfigObj() {
		return $this->config;
	}

	function setConfigObj($config) {
		$this->config = $config;
	}

}

class BonusFileListComponent extends HTMLList{
	private $selected = array();

	function populateItem($entity, $key, $index){

		//チェックボックス
		$this->addCheckbox("bonus_file_checkbox", array(
			"name" => "BonusFileSelected[". $entity["name"]. "]",
			"value" => true,
			"isBoolean" => true,
			"selected" => in_array($entity["name"], $this->selected),
			"elementId" => "bonus_file_". $entity["name"]
		));

		//label
		$this->addModel("bonus_file_label", array(
			"for" => "bonus_file_". $entity["name"]
		));

		//ファイル名
		$this->addLabel("bonus_file_name", array(
			"text" => $entity["name"]
		));

		//ファイルサイズ
		$this->addLabel("bonus_file_size", array(
			"text" => $entity["filesize"]
		));

		//削除リンク
		$this->addLink("bonus_file_delete_link", array(
			"link" => "javascript:void(0);",
			"onClick" => "if(confirm('このファイルを削除しますか？')){post_delete_bonus_file('". $entity["name"] ."');}"
		));

	}


	public function getSelected() {
		return $this->selected;
	}
	public function setSelected($selected) {
		$this->selected = $selected;
	}
}
