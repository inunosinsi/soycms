<?php
/**
 * エントリー表示用のブロックコンポーネント
 */
class EntryBlockComponent implements BlockComponent{
	
	private $entryId = array();
	
	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	function getFormPage(){
		return SOY2HTMLFactory::createInstance("EntryBlockComponent_FormPage",array(
			"entity" => $this
		));		
	}
	
	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 * TODO 公開期間チェック
	 */
	function getViewPage($page){
		return SOY2HTMLFactory::createInstance("EntryBlockComponent_ViewPage",array(
			"list" => $this->getEntries($this->getEntryId()),
			"soy2prefix"=>"block"
		));
	}
	
	function getEntries($entries){
		$dao = SOY2DAOFactory::create("cms.EntryDAO");
		$return = array();
		foreach($entries as $entryId){
			try{
				$entry = $dao->getById($entryId);
					
				//Check opening status
				if($entry->isActive() == Entry::ENTRY_ACTIVE){
				 	$return[] = $entry;
				}else if(defined("CMS_PREVIEW_ALL") && CMS_PREVIEW_ALL){
					$return[] = $entry;
				}
				 
				
			}catch(Exception $e){
				//do nothing
				//No entry is searched.
			}
		}
		return $return;
		
	}
	
	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	function getInfoPage(){
		
		if(is_null($this->getEntryId()) || count($this->getEntryId()) == 0){
			return CMSMessageManager::get("SOYCMS_NO_SETTING");
		}else{
			return count($this->getEntryId()).CMSMessageManager::get("SOYCMS_NUMBER_OF_SET_ENTRIES");
		}
		
	}
	
	/**
	 * @return string コンポーネント名
	 */
	function getComponentName(){
		return CMSMessageManager::get("SOYCMS_ENTRY_BLOCK");
	}
	
	function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_ENTRY_BLOCK_DESCRIPTION");;
	}
	

	function getEntryId() {
		return $this->entryId;
	}
	function setEntryId($entryId) {
		$this->entryId = $entryId;
	}

}


class EntryBlockComponent_FormPage extends HTMLPage{
	
	private $entity;
	
	function EntryBlockComponent_FormPage(){
		HTMLPage::HTMLPage();
		
	}
	
	function execute(){
		$this->createAdd("update_form", "HTMLForm", array("name" => "update_form"));
		
		$this->createAdd("selector_js","HTMLScript",array(
			"lang"=>"text/javascript",
			"script"=>file_get_contents(SOY2::RootDir() . "../soycms/js/entry_selector.js")
		));
		
		if(is_null($this->entity->getEntryId())){
			$this->entity->setEntryId(array());
		}
		
		
		
		$allEntry = $this->getAllEntry();
		$allEntryIds = array_map(create_function('$v','return $v["id"];'),$allEntry);
		
		
		$entryIds = array();//array_map(create_function('$v','return (int)$v;'),$this->entity->getEntryId());
				
		foreach($this->entity->getEntryId() as $key => $entry){
			if(!in_array($entry,$allEntryIds)){
				continue;
			}else{
				$entryIds[] = array(
					"order"=>(int)$key,
					"id"=>(int)$entry
				);
			}
		}
		
		if(isset($_GET["createdId"])){
			$entryIds[] = array(
				"order"=>10000,
				"id"=>(int)$_GET["createdId"]
			);	
		}
		
		$this->createAdd("entry_list","HTMLScript",array(
			"lang"=>"text/javascript",
			"script"=>'var entryList='.json_encode($allEntry).";\n"
					 .'var initEntries = '.json_encode($entryIds).';'
					 .'var outlineLink = "'.SOY2PageController::createLink("Entry.Outline").'";'
		));
		
		$this->createAdd("entry_form","HTMLScript",array(
			"lang"=>"text/javascript",
			"script"=>'var entry_form_address="'.SOY2PageController::createLink("Page.Preview.Entry").'?jumpTo='.$this->entity->blockId.'";'
		));
		
		$this->createAdd("labelList","HTMLSelect",array(
			"options"=>$this->getLabelCaptions(),
			"indexOrder"=>true,
			"property" => "caption"
		));
	}
	
	
	function getAllEntry(){
		$dao = SOY2DAOFactory::create("cms.EntryDAO");
    	$array = $dao->get();
    	$ret_val = array();
    	
    	
    	foreach($array as $key => $value){
    		$ret_val[$key] = array();
    		$ret_val[$key]["title"] = htmlspecialchars($value->getTitle());
    		$ret_val[$key]["id"] = $value->getId();
    		$ret_val[$key]["label"] = $this->getLabelIdsFromEntryId($value->getId());
    	}
    	
    	return $ret_val;
	}
	
	function getLabelIdsByEntryId($entryId){
		static $dao = null;
		if($dao == null){
			$dao = SOY2DAOFactory::create("cms.EntryLabelDAO");	
		}
		return $dao->getByEntryId($entryId);
	}
	
	function getLabelIdsFromEntryId($entryId){
		static $labels = null;
		if($labels == null){
			$dao = SOY2DAOFactory::create("cms.LabelDAO");
			$labels = $dao->get();
		}
		$labelIds = $this->getLabelIdsByEntryid($entryId);
		$return = array();		
		foreach($labelIds as $key => $value){
			if(isset($labels[$key])){
				$tmp = $labels[$key];
				$return[] = $tmp->getId();
			}
		}
		
		return $return;
	}
	
	function getLabelCaptions(){
		$dao = SOY2DAOFactory::create("cms.LabelDAO");
		$array = $dao->get();
		return $array;
	}
	
	function getTemplateFilePath(){
	  
		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja" || !file_exists(CMS_BLOCK_DIRECTORY . "EntryBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html")){
		   return CMS_BLOCK_DIRECTORY . "EntryBlockComponent" . "/form.html";
		}else{
		   return CMS_BLOCK_DIRECTORY . "EntryBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html";			
		}
	
	}
	
	function setEntity($entity){
		$this->entity = $entity;
	}
	
	function getEntity(){
		return $this->entity;
	}
	
		
}


class EntryBlockComponent_ViewPage extends HTMLList{
	
	protected $_soy2_prefix = "block";
	protected $entry;
	
	function getStartTag(){
		if(defined("CMS_PREVIEW_MODE")){
			return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->getId().'["entry_id"]; ?>','<?php echo strip_tags($'.$this->getId().'["title"]); ?>');
		}else{
			return parent::getStartTag();
		}
	}
	

	
	function populateItem($entity){
		$title = $entity->getTitle();
		
		$this->createAdd("entry_id","CMSLabel",array(
			"text"=> $entity->getId(),
			"soy2prefix"=>"cms"
		));
		
		$this->createAdd("title","CMSLabel",array(
			"text"=> $title,
			"soy2prefix"=>"cms"
		));
		
		$this->createAdd("title_plain","CMSLabel",array(
			"text" => $title,
			"soy2prefix" => "cms"
		));
		$this->createAdd("content","CMSLabel",array(
			"html"=>$entity->getContent(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("more","CMSLabel",array(
			"html"=>$entity->getMore(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("create_date","DateLabel",array(
			"text"=>$entity->getCdate(),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("create_time","DateLabel",array(
			"text"=>$entity->getCdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		//1.7.5~
		$this->createAdd("update_date","DateLabel",array(
			"text"=>$entity->getUdate(),
			"soy2prefix"=>"cms",
		));

		$this->createAdd("update_time","DateLabel",array(
			"text"=>$entity->getUdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entity->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entity));
	}
		
}

class EntryList extends HTMLList{
	
	private $currentEntry;
	
	function populateItem($entity){
		$this->createAdd("radio","HTMLCheckBox",array(
			"value"=>$entity->getId(),
			"name"=>"object[entryId]",
			"selected"=>((string)$this->currentEntry == (string)$entity->getId())
		));
		$this->createAdd("title","HTMLLabel",array(
			"text"=>$entity->getTitle()
		));
		$this->createAdd("contents","HTMLLabel",array(
			"text"=>substr($entity->getContent(),0,30)
		));
		$this->createAdd("create_time","HTMLLabel",array(
			"text"=>date('Y-m-d H:i:s',$entity->getCdate()),
		));
	}

	function setCurrentEntry($currentEntry) {
		$this->currentEntry = $currentEntry;
	}
}

?>
