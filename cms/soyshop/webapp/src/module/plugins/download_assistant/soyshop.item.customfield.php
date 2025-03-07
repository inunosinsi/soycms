<?php
SOY2::import("module.plugins.download_assistant.common.DownloadAssistantCommon");
class DownloadAssistantCustomField extends SOYShopItemCustomFieldBase{

	private $dao;

	function doPost(SOYShop_Item $item){

		$dir = SOYSHOP_SITE_DIRECTORY . "download/" . $item->getCode();
		if(!file_exists($dir)) mkdir($dir);

		//削除
		if(isset($_POST["download_assistant_delete"])){
			$files = $_POST["download_assistant_delete"];
			foreach($files as $file){
				$deleteFile = $dir."/" . $file;
				unlink($deleteFile);
			}
		}

		$commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");

		//拡張子のチェック。許可してある拡張子はcheckExtension内に記載
		if(isset($_FILES["file"]) && strlen($_FILES["file"]["type"]) > 0 && $commonLogic->checkFileType($_FILES["file"]["name"]) === true){
			$fname = $_FILES["file"]["name"];

			//半角英数字かチェックする
			if (preg_match("/^[0-9A-Za-z%&+\-\^_`{|}~.]+$/", $fname)){
				$dest_name = $dir . "/" . $fname;


				//iconsディレクトリの中にすでにファイルがないかチェックする
				if(!file_exists($dest_name)){
					//ファイルの移動が失敗していないかどうかをチェック
					if(@move_uploaded_file($_FILES["file"]["tmp_name"], $dest_name) === false){
						//
					}
				}
			}
		}

		if(isset($_POST["download_assistant_time"])){

			//ダウンロード期限の値を設定する
			$attr = soyshop_get_item_attribute_object((int)$item->getId(), "download_assistant_time");
			$time = mb_convert_kana($_POST["download_assistant_time"], "a");
			$time = (strlen($time) > 0 && is_numeric($time))? (int)$time : 0;
			$attr->setValue($time);
			soyshop_save_item_attribute_object($attr);

			//ダウンロード回数を設定する
			$attr = soyshop_get_item_attribute_object((int)$item->getId(), "download_assistant_count");
			$count = mb_convert_kana($_POST["download_assistant_count"], "a");
			$count = (strlen($count) > 0 && is_numeric($count)) ? (int)$count : 0;
			$attr->setValue($count);
			soyshop_save_item_attribute_object($attr);
		}
	}

	function getForm(SOYShop_Item $item){

		//商品タイプがダウンロードの時もしくは親商品のタイプがダウンロードの時に表示
		$commonLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadCommonLogic");
		if($commonLogic->checkItemType($item)){

			$_arr = $commonLogic->getDownloadFieldConfig((int)$item->getId(), true);
			$time = $_arr["timeLimit"];
			$count = $_arr["count"];

			$dir = SOYSHOP_SITE_DIRECTORY . "download/" . $item->getCode() . "/";
			if(!file_exists($dir)) mkdir($dir);

			$style = "style=\"text-align:right;width:60px;\"";

			$html = array();

			$html[] = "<div class=\"alert alert-info\">ダウンロード販売用設定</div>";
			$html[] = "<label for=\"download_field\">ダウンロード販売商品登録&nbsp;(半角英数字)</label><br />";
			$html[] = "<span style=\"font-size:0.9em;\">※登録可能なファイルの拡張子：</span>&nbsp;" . $commonLogic->allowExtension() . "<br>";
			$html[] = "<div class=\"form-inline\">";
			$html[] = "<input type=\"file\" name=\"file\" id=\"file\" />";
			$html[] = "<p style=\"font-size:0.9em;padding:5px 0;\">※ファイルを直接サーバに配置することも可能です</p>";
			$html[] = "<div class=\"alert alert-warning\">ファイルの配置ディレクトリ&nbsp;:&nbsp;<strong>" . $dir."</strong></div>";
			$html[] = "<br />";

			//削除ボタン用のフラグ
			$deleteFlag = false;

			//ダウンロード用のファイルがあるか確認する
			$files = opendir($dir);
			while($file = readdir($files)){
				if($commonLogic->checkFileType($file) && preg_match("/^[0-9A-Za-z%&+\-\^_`{|}~.]+$/", $file)){
					if(!$deleteFlag){
						$html[] = "<div class=\"alert alert-success\">登録されているファイル</div>";
					}
					$html[] = "<input type=\"checkbox\" name=\"download_assistant_delete[]\" value=\"" . $file . "\" id=\"download_assistant_" . $file."\" />";
					$html[] = "<label for=\"download_assistant_" . $file . "\">" . $file . "&nbsp;" . $commonLogic->getFileSize(filesize($dir . $file)) . "</label>";
					$html[] = "<br />";
					if(!$deleteFlag) $deleteFlag = true;
				}
			}
			if($deleteFlag){
				$html[] = "<p style=\"font-size:0.9em;padding:5px 0;\">※チェックしたファイルは商品情報更新時に削除されます</p>";
			}

			$html[] = "</div>";

			$html[] = "<label for=\"download_field\">ダウンロード期間日数</label><br>";
			$html[] = "<input type=\"number\" name=\"download_assistant_time\" value=\"" . $time."\" " . $style." />&nbsp;日";
			$html[] = "<p>※値がない場合は無期限</p>";

			$html[] = "<label for=\"download_field\">ダウンロード回数</label><br>";
			$html[] = "<input type=\"number\" name=\"download_assistant_count\" value=\"" . $count."\" " . $style." />&nbsp;回";
			$html[] = "<p>※値がない場合は無制限</p>";

			$html[] = "<div class=\"alert alert-info\">ダウンロード販売用設定ここまで</div>";

			return implode("\n", $html);
		}
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){}

	function onDelete(int $itemId){
		soyshop_get_hash_table_dao("item_attribute")->deleteByItemId($itemId);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "download_assinstant", "DownloadAssistantCustomField");
