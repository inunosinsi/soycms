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
	public function getFormPage(){
		return SOY2HTMLFactory::createInstance("EntryBlockComponent_FormPage",array(
			"entity" => $this
		));
	}

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 * TODO 公開期間チェック
	 */
	public function getViewPage($page){
		$entryIds = (is_array($this->getEntryId())) ? $this->getEntryId() : array($this->getEntryId());
		return SOY2HTMLFactory::createInstance("EntryBlockComponent_ViewPage",array(
			"list" => self::_getEntries($entryIds),
			"soy2prefix"=>"block"
		));
	}

	private function _getEntries(array $entryIds){
		$return = array();
		foreach($entryIds as $entryId){
			$entry = soycms_get_entry_object((int)$entryId);
			//Check opening status
			if($entry->isActive() == Entry::ENTRY_ACTIVE){
			 	$return[] = $entry;
			}else if(defined("CMS_PREVIEW_ALL") && CMS_PREVIEW_ALL){
				$return[] = $entry;
			}
		}
		return $return;

	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	public function getInfoPage(){
		if(is_null($this->getEntryId()) || count($this->getEntryId()) == 0){
			return CMSMessageManager::get("SOYCMS_NO_SETTING");
		}else{
			return count($this->getEntryId()).CMSMessageManager::get("SOYCMS_NUMBER_OF_SET_ENTRIES");
		}

	}

	/**
	 * @return string コンポーネント名
	 */
	public function getComponentName(){
		return CMSMessageManager::get("SOYCMS_ENTRY_BLOCK");
	}

	public function getComponentDescription(){
		return CMSMessageManager::get("SOYCMS_ENTRY_BLOCK_DESCRIPTION");;
	}


	public function getEntryId() {
		return $this->entryId;
	}
	public function setEntryId($entryId) {
		$this->entryId = $entryId;
	}

	public function getDisplayCountFrom() {
		return $this->displayCountFrom;
	}
	public function setDisplayCountFrom($displayCountFrom) {
		$cnt = (strlen($displayCountFrom) && is_numeric($displayCountFrom)) ? (int)$displayCountFrom : null;
		$this->displayCountFrom = $cnt;
	}

	public function getDisplayCountTo() {
		return $this->displayCountTo;
	}
	public function setDisplayCountTo($displayCountTo) {
		$cnt = (strlen($displayCountTo) && is_numeric($displayCountTo)) ? (int)$displayCountTo : null;
		$this->displayCountTo = $cnt;
	}
}


class EntryBlockComponent_FormPage extends HTMLPage{

	private $entity;

	public function execute(){
		$this->addForm("update_form", array("name" => "update_form"));

		$this->addScript("selector_js",array(
			"script"=>file_get_contents(SOY2::RootDir() . "../soycms/js/entry_selector.js")
		));

		if(is_null($this->entity->getEntryId())){
			$this->entity->setEntryId(array());
		}



		$allEntry = $this->getAllEntry();
		$allEntryIds = array_map(function($v) { return $v["id"]; }, $allEntry);


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

		$this->addScript("entry_list",array(
			"script"=>'var entryList='.json_encode($allEntry).";\n"
					 .'var initEntries = '.json_encode($entryIds).';'
					 .'var outlineLink = "'.SOY2PageController::createLink("Entry.Outline").'";'
		));

		$this->addScript("entry_form",array(
			"script"=>'var entry_form_address="'.SOY2PageController::createLink("Page.Preview.Entry").'?jumpTo='.$this->entity->blockId.'";'
		));

		$this->addSelect("labelList", array(
			"options"=>$this->getLabelCaptions(),
			"indexOrder"=>true,
			"property" => "caption"
		));
	}


	private function getAllEntry(){
		$array = soycms_get_hash_table_dao("entry")->get();
		$ret_val = array();

		foreach($array as $key => $value){
			$ret_val[$key] = array();
			$ret_val[$key]["title"] = htmlspecialchars($value->getTitle());
			$ret_val[$key]["id"] = $value->getId();
			$ret_val[$key]["label"] = $this->getLabelIdsFromEntryId($value->getId());
		}

		return $ret_val;
	}

	private function getLabelIdsByEntryId(int $entryId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		return $dao->getByEntryId($entryId);
	}

	private function getLabelIdsFromEntryId(int $entryId){
		static $labels;
		if(is_null($labels)) $labels = soycms_get_hash_table_dao("label")->get();
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

	private function getLabelCaptions(){
		return soycms_get_hash_table_dao("label")->get();
	}

	public function getTemplateFilePath(){
		//ext-modeでbootstrap対応画面作成中
		if(defined("EXT_MODE_BOOTSTRAP") && file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_sbadmin2.html";
		}


		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja" || !file_exists(CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html")){
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . basename(dirname(__FILE__)). "/form_".SOYCMS_LANGUAGE.".html";
		}

	}

	public function setEntity($entity){
		$this->entity = $entity;
	}

	public function getEntity(){
		return $this->entity;
	}
}


class EntryBlockComponent_ViewPage extends HTMLList{

	protected $_soy2_prefix = "block";
	protected $entry;

	public function getStartTag(){
		if(defined("CMS_PREVIEW_MODE")){
			return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->getId().'["entry_id"]; ?>','<?php echo strip_tags($'.$this->getId().'["title"]); ?>');
		}else{
			return parent::getStartTag();
		}
	}



	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$title = $entity->getTitle();

		$this->createAdd("entry_id","CMSLabel",array(
			"text"=> $id,
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
		$contentLen = (is_string($entity->getContent())) ? strlen($entity->getContent()) : 0;
		$this->addModel("has_content", array(
			"visible" => ($contentLen > 0),
			"soy2prefix"=>"cms"
		));
		$this->addModel("no_content", array(
			"visible" => ($contentLen === 0),
			"soy2prefix"=>"cms"
		));
		$this->createAdd("more","CMSLabel",array(
			"html"=>$entity->getMore(),
			"soy2prefix"=>"cms"
		));
		$moreLen = (is_string($entity->getMore())) ? strlen($entity->getMore()) : 0;
		$this->addModel("has_more", array(
			"visible" => ($moreLen > 0),
			"soy2prefix"=>"cms"
		));
		$this->addModel("no_more", array(
			"visible" => ($moreLen === 0),
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

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId" => $id,"SOY2HTMLObject"=>$this,"entry"=>$entity));
	}

}

class EntryList extends HTMLList{

	private $currentEntry;

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addCheckBox("radio", array(
			"value" => $id,
			"name" => "object[entryId]",
			"selected" => ((int)$this->currentEntry === $id)
		));
		$this->addLabel("title", array(
			"text"=>$entity->getTitle()
		));
		$this->addLabel("contents", array(
			"text"=>substr($entity->getContent(),0,30)
		));
		$this->addLabel("create_time", array(
			"text"=> (is_numeric($entity->getCdate())) ? date('Y-m-d H:i:s',$entity->getCdate()) : "",
		));
	}

	public function setCurrentEntry($currentEntry) {
		$this->currentEntry = $currentEntry;
	}
}
