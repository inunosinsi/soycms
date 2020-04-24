<?php
/*
 * Created on 2010/07/18
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class EntryListComponent extends HTMLList{

	private $blogUrl;
	private $customFields;
	private $thisIsNewDate = 7;
	private $thumbnailConfig;

	protected function populateItem($entity){

		$link = $this->blogUrl . rawurlencode($entity->getAlias());

		$this->addLabel("entry_id", array(
			"text" => $entity->getId(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("title", array(
			"html" => "<a href=\"" . $link . "\">".htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8")."</a>",
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("title_plain", array(
			"text" => $entity->getTitle(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("content", array(
			"html" => $entity->getContent(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//作成日付 Y-m-d H:i:s
		$this->createAdd("create_date","DateLabel", array(
			"text" => $entity->getCdate(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//作成日付 Y-m-d
		$this->createAdd("create_ymd","DateLabel", array(
			"text"=>$entity->getCdate(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"defaultFormat"=>"Y-m-d"
		));

        $this->addLabel("create_date_ymd", array(
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
            "text" => date("Y-m-d", $entity->getCdate())
        ));

		$this->addLabel("create_date_ymd_slash", array(
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
            "text" => date("Y/m/d", $entity->getCdate())
        ));

		$more = $entity->getMore();
		$this->addLabel("more", array(
			"html"=> '<a name="more"></a>'.$more,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
		));

		//作成時刻 H:i
		$this->createAdd("create_time", "DateLabel", array(
			"text"=>$entity->getCdate(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"defaultFormat"=>"H:i"
		));

		$this->addLink("entry_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => $link
		));

		$this->addLink("more_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => $link ."#more",
			"visible"=>(strlen($entity->getMore()) != 0)
		));

		//カスタムフィールドを呼び出す
		$array = array();
		if(count($this->customFields)){
			try{
				$array = self::_attrDao()->getByEntryId($entity->getId());
			}catch(Exception $e){
				//
			}

			foreach($this->customFields as $fieldId => $obj){
				$value = (isset($array[$fieldId])) ? $array[$fieldId]->getValue() : "";

				$this->addModel($fieldId . "_visible", array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"visible" => (strlen($value) > 0)
				));

				$attr["soy2prefix"] = SOYSHOP_SITE_PREFIX;
				switch($obj->getType()){
					case "image":
						$class = "HTMLImage";
						$attr["src"] = $value;
						break;
					case "link":
						$class = "HTMLLink";
						$attr["link"] = $value;
					default:
					case "input":
						$class = "HTMLLabel";
						$attr["html"] = $value;
						break;
				}


				$this->addLabel($fieldId, array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"html" => $value
				));

				$this->createAdd($fieldId, $class, $attr);
			}
		}

		//サムネイルプラグイン
		if(!is_null($this->thumbnailConfig)){
			$objects = (is_numeric($entity->getId())) ? self::_getThumbnailValues($entity->getId()) : array();
			foreach(array("upload", "trimming", "resize") as $label){
				$key = "soycms_thumbnail_plugin_" . $label;
				$obj = (isset($objects[$key])) ? $objects[$key] : new EntryAttribute();

				if($label == "resize") $label = "thumbnail";

				$imagePath = trim($obj->getValue());
				if($label == "thumbnail" && !strlen($imagePath)) $imagePath = $this->thumbnailConfig;

				$this->addModel("is_" . $label, array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"visible" => (strlen($imagePath) > 0)
				));

				$this->addModel("no_" . $label, array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"visible" => (strlen($imagePath) === 0)
				));

				$this->addImage($label, array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"src" => $imagePath,
					"alt" => (isset($objects["soycms_thumbnail_plugin_alt"])) ? $objects["soycms_thumbnail_plugin_alt"]->getValue() : ""
				));

				$this->addLabel($label . "_text", array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"text" => $imagePath
				));

				$this->addLabel($label . "_path_text", array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"text" => $imagePath
				));
			}
		}


		//this is new
		if(is_numeric($this->thisIsNewDate) && $this->thisIsNewDate > 0){
			$this->addModel("this_is_new", array(
				"visible" => (isset($entity) && self::_compareTime($entity) > time()),
				"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
		}
	}

	private function _getThumbnailValues($entryId){
		if(!is_numeric($entryId)) return array();
		$dao = self::_attrDao();
		try{
			$res = $dao->executeQuery("SELECT entry_field_id, entry_value FROM EntryAttribute WHERE entry_id = :entryId AND entry_field_id LIKE 'soycms_thumbnail_plugin_%' AND entry_field_id NOT LIKE '%config'", array(":entryId" => $entryId));
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[$v["entry_field_id"]] = $dao->getObject($v);
		}
		return $list;
	}

	private function _attrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}

	private function _compareTime($entry){
		return $entry->getCdate() + $this->thisIsNewDate * 60*60*24;
	}

	function setBlogUrl($blogUrl){
		$this->blogUrl = $blogUrl;
	}

	function setCustomFields($customFields){
		$this->customFields = $customFields;
	}

	function setThisIsNewDate($thisIsNewDate){
		$this->thisIsNewDate = $thisIsNewDate;
	}

	function setThumbnailConfig($thumbnailConfig){
		$this->thumbnailConfig = $thumbnailConfig;
	}
}
