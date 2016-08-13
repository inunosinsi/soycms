<?php

class IndexPage extends WebPage{
	
	function doPost(){
		
		if(soy2_check_token()&&isset($_POST["Config"])){
			
			$config = $_POST["Config"];
			$config["ownerDisplay"] = (isset($config["ownerDisplay"])) ? 1 : 0;
			$config["count"] = mb_convert_kana($config["count"],"a");
			$config["count"] = (is_numeric($config["count"])) ? $config["count"] : 5;
			$config["archive"] = mb_convert_kana($config["archive"],"a");
			$config["archive"] = (is_numeric($config["archive"])) ? $config["archive"] : 10;
			$config["resize"] = mb_convert_kana($config["resize"],"a");
			$config["resize"] = (is_numeric($config["resize"])) ? $config["resize"] : 300;
			$config["isResize"] = (isset($config["isResize"])) ? 1 : 0;
			$config["label"] = (isset($config["label"])) ? $config["label"] : null;
			$config["isSync"] = (isset($config["isSync"])) ? 1 : 0;
			$config["isPublished"] = (isset($config["isPublished"])) ? 1 : 0;
			
			$dao = SOY2DAOFactory::create("SOYVoice_ConfigDAO");
			try{
				$obj = $dao->getById(1);
				$flag = true;
			}catch(Exception $e){
				$obj = new SOYVoice_Config();
				$flag = false;
			}
			
			$config = SOY2::cast($obj,$config);
			
			
			try{
				if($flag){
					$dao->update($config);
				}else{
					$dao->insert($config);
				}
			}catch(Exception $e){
				var_dump($e);
			}
			
			CMSApplication::jump("Config");
			
		}
		
	}

    function __construct() {
    	WebPage::__construct();
    	
    	$config = $this->getConfig();
    	
    	$this->createAdd("form","HTMLForm");
    	
    	$this->createAdd("owner_name","HTMLInput",array(
    		"name" => "Config[ownerName]",
    		"value" => $config->getOwnerName()
    	));
    	$this->createAdd("owner_display","HTMLCheckBox",array(
    		"name" => "Config[ownerDisplay]",
    		"value" => 1,
    		"selected" => $config->getOwnerDisplay()==1,
    		"elementId" => "owner_display"
    	));
    	$this->createAdd("count","HTMLInput",array(
    		"name" => "Config[count]",
    		"value" => (!is_null($config->getCount())) ? $config->getCount() : 5
    	));
    	$this->createAdd("archive","HTMLInput",array(
    		"name" => "Config[archive]",
    		"value" => (!is_null($config->getArchive())) ? $config->getArchive() : 10
    	));
    	$this->createAdd("is_resize","HTMLCheckBox",array(
    		"name" => "Config[isResize]",
    		"value" => 1,
    		"selected" => $config->getIsResize()==1,
    		"elementId" => "is_resize"
    	));
    	$this->createAdd("resize","HTMLInput",array(
    		"name" => "Config[resize]",
    		"value" => (!is_null($config->getResize())) ? $config->getResize() : 300
    	));
    	
    	$this->buildSyncBox($config);
    }
    
    function buildSyncBox($config){
    	$logic = SOY2Logic::createInstance("logic.SyncLogic");
    	$sites = $logic->getCMSSites();
    	
    	$array = $logic->getCMSSiteArray($sites);

		$this->createAdd("sync_site","HTMLSelect",array(
			"name" => "Config[syncSite]",
			"options" => $logic->getCMSSiteArray($sites),
			"indexOrder" => true,
			"selected" => $config->getSyncSite()
		));
		
		$this->createAdd("is_sync_site","HTMLModel",array(
			"visible" => (!is_null($config->getSyncSite()))
		));
		
		$this->createAdd("label_none","HTMLCheckBox",array(
			"name" => "Config[label]",
			"value" => 0,
			"selected" => (is_null($config->getLabel())||$config->getLabel() == 0),
			"elementId" => "label_none"
		));
		
		if(!is_null($config->getSyncSite())){
			$this->createAdd("label_list","CheckLabelList",array(
				"list" => $logic->getLabels($config->getSyncSite()),
				"label" => $config->getLabel()
			));
		}
		
		$this->createAdd("is_sync","HTMLCheckBox",array(
			"name" => "Config[isSync]",
			"value" => 1,
			"selected" => $config->getIsSync()==1,
			"elementId" => "is_sync"
		));
		
		$this->createAdd("is_published","HTMLCheckBox",array(
			"name" => "Config[isPublished]",
			"value" => 1,
			"selected" => $config->getIsPublished()==1,
			"elementId" => "is_published"
		));
    }
    
    function getConfig(){
    	
    	$dao = SOY2DAOFactory::create("SOYVoice_ConfigDAO");
    	try{
    		$config = $dao->getById(1);
    	}catch(Exception $e){
    		$config = new SOYVoice_Config();
    	}
    	
    	return $config;
    }
}

class CheckLabelList extends HTMLList{
	
	private $label;
	
	protected function populateItem($entity){
		
		$flag = ($entity->getId() == $this->label) ? true : false;
		
		$this->createAdd("check_label","HTMLCheckBox",array(
			"name" => "Config[label]",
			"value" => $entity->getId(),
			"selected" => $flag,
			"elementId" => $entity->getCaption()
		));
		$this->createAdd("label_caption","HTMLModel",array(
			"attr:for" => $entity->getCaption()
		));
		$this->createAdd("caption","HTMLLabel",array(
			"text" => $entity->getCaption()
		));
		
	}
	
	function setLabel($label){
		$this->label = $label;
	}
}
?>