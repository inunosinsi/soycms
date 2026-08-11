<?php

class CustomFieldFormPage extends WebPage {

	private $csfFieldId;
	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		SOY2::imports("module.plugins.custom_search_field.domain.*");
	}

	function doPost(){

		//カテゴリー情報の更新
		if(isset($_POST["update"]) && soy2_check_token()){

			$posts = (isset($_POST["custom_field"])) ? $_POST["custom_field"] : array();
			if(!count($posts)) {
				SOY2PageController::jump("Config.Detail?plugin=custom_search_field&customset=" . $this->csfFieldId . "&updated");
				exit;
			}

			$attrDao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
			$configs = SOYShop_CustomSearchAttributeConfig::load(true);
			if(!count($configs)) {
				SOY2PageController::jump("Config.Detail?plugin=custom_search_field&customset=" . $this->csfFieldId . "&updated");
				exit;
			}

			foreach($configs as $fieldId => $conf){
				try{
					$attr = $attrDao->get($this->csfFieldId, $fieldId);
				}catch(Exception $e){
					$attr = new SOYShop_CustomSearchAttribute();
					$attr->setSearchId($this->csfFieldId);
					$attr->setFieldId($fieldId);
				}

				$v = (isset($posts[$fieldId])) ? $posts[$fieldId] : null;
				$attr->setValue($v);

				$v = (isset($posts[$fieldId . "_option"])) ? $posts[$fieldId . "_option"] : null;
				$attr->setValue2($v);

				try{
					$attrDao->insert($attr);
				}catch(Exception $e){
					try{
						$attrDao->update($attr);
					}catch(Exception $e){
						var_dump($e);
					}
				}
			}

			SOY2PageController::jump("Config.Detail?plugin=custom_search_field&customset=" . $this->csfFieldId . "&updated");
			exit;
		}

		//画像のアップロード
		if(isset($_POST["upload"])){
			$urls = self::uploadImage($_POST["upload"]);

			echo "<html><head>";
			echo "<script type=\"text/javascript\">";
			if($urls !== false){
				foreach($urls as $url){
					echo 'window.parent.ImageSelect.notifyUpload("'.$url.'");';
				}
			}else{
				echo 'alert("failed");';
			}
			echo "</script></head><body></body></html>";
			exit;
		}
	}

	function execute(){
		if(!isset($_GET["customset"])) {
			SOY2SOY2PageController::jump("Config.Detail?plugin=custom_search_field");
			exit;
		}
		$this->csfFieldId = $_GET["customset"];
		$csfConf = CustomSearchFieldUtil::getConfig();
		if(!isset($csfConf[$this->csfFieldId])){
			SOY2SOY2PageController::jump("Config.Detail?plugin=custom_search_field");
			exit;
		}

		$csfField = $csfConf[$this->csfFieldId];

		parent::__construct();

		$this->addForm("form");

		$this->addLabel("label", array(
			"text" => (isset($csfField["label"])) ? $csfField["label"]: ""
		));

		SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
		$configs = SOYShop_CustomSearchAttributeConfig::load();

		$attrDao = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO");
		$attrs = $attrDao->getBySearchId($this->csfFieldId);
		
		//カスタムサーチフィールド用のカスタムフィールド
		$html = array();
		if(is_array($configs) && count($configs)){
			foreach($configs as $field){
				$attr = (isset($attrs[$field->getFieldId()])) ? $attrs[$field->getFieldId()] : new SOYShop_CustomSearchAttribute();
				$html[] = $field->getForm($attr->getValue(), $attr->getValue2());
			}
		}

		$this->addLabel("custom_search_custom_field", array(
			"html" => implode("\n", $html)
		));

		$this->addForm("upload_form", array(
			"enctype" => "multipart/form-data",
			"attr:id" => "upload_form",
			"attr:target" => "upload_target_frame",
		));

		$this->addInput("csf_field_id_upload", array(
			"name" => "upload",
			"value" => $this->csfFieldId
		));

		$this->createAdd("image_list","_common.Category.ImageListComponent", array(
			"list" => self::getAttachments($this->csfFieldId)
		));
	}

	/**
     * 添付ファイルを取得
     */
    function getAttachments($csfFieldId){
        $dir = self::getAttachmentsPath($csfFieldId);
        $url = self::getAttachmentsUrl($csfFieldId);
        $files = scandir($dir);
        $res = array();

        foreach($files as $file){
            if($file[0] == ".") continue;
            $res[] = $url . $file;
        }

        return $res;
    }

	private function getAttachmentsPath($csfFieldId){
        $dir = SOYSHOP_SITE_DIRECTORY . "files/csf/";
        if(!file_exists($dir)){
            mkdir($dir);
        }

		$dir .= $csfFieldId . "/";
		if(!file_exists($dir)){
            mkdir($dir);
        }

        return $dir;
    }

    private function getAttachmentsUrl($csfFieldId){
        return soyshop_get_site_path() . "files/csf/" . $csfFieldId . "/";
    }

	/**
	 * 画像のアップロード
	 *
	 * @return url
	 * 失敗時には false
	 */
	private function uploadImage($csfFieldId){
		$urls = array();

		foreach($_FILES as $upload){
			foreach($upload["name"] as $key => $value){

				//replace invalid filename
				$upload["name"][$key] = strtolower(str_replace("%","",rawurlencode($upload["name"][$key])));

				$pathinfo = pathinfo($upload["name"][$key]);
				if(!isset($pathinfo["filename"]))$pathinfo["filename"] = str_replace("." . $pathinfo["extension"], $pathinfo["basename"]);

				//get unique file name
				$counter = 0;
				$filepath = "";
				$name = "";

				while(true){
					$name = ($counter > 0) ? $pathinfo["filename"] . "_" . $counter . "." . $pathinfo["extension"] : $pathinfo["filename"] . "." . $pathinfo["extension"];
					$filepath = self::getAttachmentsPath($csfFieldId) . $name;


					if(!file_exists($filepath)){
						break;
					}
					$counter++;
				}

				//一回でも失敗した場合はfalseを返して終了（rollbackは無し）
				$result = move_uploaded_file($upload["tmp_name"][$key],$filepath);
				@chmod($filepath,0604);

				if($result){
					$url = self::getAttachmentsUrl($csfFieldId) . $name;
					$urls[] = $url;
				}else{
					return false;
				}
			}
		}

		return $urls;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
