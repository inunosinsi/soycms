<?php
/*
 * Created on 2010/07/18
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class EntryListComponent extends HTMLList{
	
	private $blogUrl;
	private $customField;
	private $entryAttributeDao;
	
	protected function populateItem($entity){
		$link = $this->blogUrl . rawurlencode($entity->getAlias());
		
		$this->addLabel("entry_id", array(
			"text" => $entity->getId(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
		
		$this->addLabel("title", array(
			"html" => "<a href=\"$link\">".htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8")."</a>",
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
		$array = (count($this->customField)) ? $this->entryAttributeDao->getByEntryId($entity->getId()) : array();
		
		foreach($this->customField as $fieldId => $obj){
			if(isset($array[$fieldId])){
				$value = $array[$fieldId]->getValue();
			}else{
				$value = "";
			}
			
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
	
	function setBlogUrl($blogUrl){
		$this->blogUrl = $blogUrl;
	}
	
	function setCustomField($customField){
		$this->customField = $customField;
	}
	
	function setEntryAttributeDao($entryAttributeDao){
		$this->entryAttributeDao = $entryAttributeDao;
	}
}
?>