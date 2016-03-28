<?php
/**
 * エントリー表示用のブロックコンポーネント
 */
class LabeledBlockComponent implements BlockComponent{

	private $labelId;
	private $displayCountFrom;
	private $displayCountTo;
	private $isStickUrl = false;
	private $blogPageId;
	private $order = self::ORDER_DESC;//記事の並び順

	/**
	 * @return SOY2HTML
	 * 設定画面用のHTMLPageComponent
	 */
	function getFormPage(){

		$logic = SOY2Logic::createInstance("logic.site.Page.BlogPageLogic");
		$blogPages = $logic->getBlogPageList();

		return SOY2HTMLFactory::createInstance("LabeledBlockComponent_FormPage",array(
			"entity" => $this,
			"blogPages" => $blogPages
		));
	}

	/**
	 * @return SOY2HTML
	 * 表示用コンポーネント
	 */
	function getViewPage($page){
		$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

		$this->displayCountFrom = max($this->displayCountFrom,1);//0件目は認めない→１件目に変更

		if(is_numeric($this->displayCountTo)){
			$logic->limit = $this->getDisplayCountTo()- (int)$this->getDisplayCountFrom()+1;//n件目～m件目はm-n+1個のエントリ
		}

		if(is_numeric($this->displayCountFrom)){
			$logic->offset = $this->displayCountFrom-1;//offsetは0スタートなので、n件目=offset:n-1
		}

		if($this->order == self::ORDER_ASC){
			$logic->setReverse(true);
		}

		$array = array();
		try{
			if(defined("CMS_PREVIEW_ALL")){
				$array = $logic->getByLabelId($this->labelId);
			}else{
				$array = $logic->getOpenEntryByLabelId($this->labelId);
			}
		}catch(Exception $e){
			//do nothing
		}

		//エントリー管理者が変更できるかどうか
		$editable = true;
		if(defined("CMS_PREVIEW_MODE")){
			if(!UserInfoUtil::hasSiteAdminRole()){
				try{
					$label = SOY2DAOFactory::create("cms.LabelDAO")->getById($this->labelId);
					$editable = $label->isEditableByNormalUser();
				}catch(Exception $e){
					//安全側に倒しておく
					$editable = false;
				}
			}
		}
		
		$articlePageUrl = "";
		if($this->isStickUrl){
			try{
				$pageDao = SOY2DAOFactory::create("cms.BlogPageDAO");
				$blogPage = $pageDao->getById($this->blogPageId);

				if(defined("CMS_PREVIEW_MODE")){
					$articlePageUrl = SOY2PageController::createLink("Page.Preview") ."/". $blogPage->getId() . "?uri=". $blogPage->getEntryPageURL();
				}else{
					$articlePageUrl = $page->getSiteRootUrl() . $blogPage->getEntryPageURL();
				}
			}catch(Exception $e){
				$this->isStickUrl = false;
			}
		}

		return SOY2HTMLFactory::createInstance("LabeledBlockComponent_ViewPage",array(
			"list" => $array,
			"isStickUrl" => $this->isStickUrl,
			"articlePageUrl" => $articlePageUrl,
			"blogPageId"=>$this->blogPageId,
			"soy2prefix"=>"block",
			"editable" => $editable,
			"labelId" => $this->labelId
		));

	}

	/**
	 * @return string
	 * 一覧表示に出力する文字列
	 */
	function getInfoPage(){
		$dao = SOY2DAOFactory::create("cms.LabelDAO");

		try{
			$label = $dao->getById($this->labelId);
			return $label->getCaption() ." ".( (strlen($this->displayCountFrom) OR strlen($this->displayCountTo)) ? $this->displayCountFrom."-".$this->displayCountTo : "" );
		}catch(Exception $e){
			return CMSMessageManager::get("SOYCMS_NO_SETTING");
		}


	}

	/**
	 * @return string コンポーネント名
	 */
	function getComponentName(){
		return CMSMessageManager::get("SOYCMS_LABELED_ENTRY_BLOCK");
	}

	function getComponentDescription(){
		return  CMSMessageManager::get("SOYCMS_LABELED_ENTRY_BLOCK_DESCRIPTION");
	}


	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
	}

	function getDisplayCountFrom() {
		return $this->displayCountFrom;
	}
	function setDisplayCountFrom($displayCountFrom) {
		if(is_numeric($displayCountFrom)){
			$this->displayCountFrom = (int)$displayCountFrom;
		}
	}

	function getDisplayCountTo() {
		return $this->displayCountTo;
	}
	function setDisplayCountTo($displayCountTo) {
		if(is_numeric($displayCountTo)){
			$this->displayCountTo = (int)$displayCountTo;
		}
	}

	function getBlogPageId() {
		return $this->blogPageId;
	}
	function setBlogPageId($blogPageId) {
		$this->blogPageId = $blogPageId;
	}

	function getIsStickUrl() {
		return $this->isStickUrl;
	}
	function setIsStickUrl($isStickUrl) {
		$this->isStickUrl = $isStickUrl;
	}

	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}
}


class LabeledBlockComponent_FormPage extends HTMLPage{

	private $entity;
	private $blogPages = array();

	function LabeledBlockComponent_FormPage(){
		HTMLPage::HTMLPage();

	}

	function execute(){

		HTMLHead::addLink("editor_css",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("css/entry/editor.css")."?".SOYCMS_BUILD_TIME
		));

		$this->createAdd("label_loop","LabeledBlock_LabelList",array(
			"list"=>$this->getLabelList(),
			"currentLabel"=>$this->entity->getLabelId()
		));


		$this->createAdd("display_number_start","HTMLInput",array(
			"value"=>$this->entity->getDisplayCountFrom(),
			"name"=>"object[displayCountFrom]"
		));
		$this->createAdd("display_number_end","HTMLInput",array(
			"value"=>$this->entity->getDisplayCountTo(),
			"name"=>"object[displayCountTo]"
		));

		$this->createAdd("display_order_asc","HTMLCheckBox",array(
			"type"      => "radio",
			"name"      => "object[order]",
			"value"     => BlockComponent::ORDER_ASC,
			"selected"  => $this->entity->getOrder() == BlockComponent::ORDER_ASC,
			"elementId" => "display_order_asc",
		));
		$this->createAdd("display_order_desc","HTMLCheckBox",array(
			"type"      => "radio",
			"name"      => "object[order]",
			"value"     => BlockComponent::ORDER_DESC,
			"selected"  => $this->entity->getOrder() == BlockComponent::ORDER_DESC,
			"elementId" => "display_order_desc",
		));

		$this->createAdd("no_stick_url","HTMLHidden",array(
			"name" => "object[isStickUrl]",
			"value" => 0,
		));
		$this->createAdd("stick_url","HTMLCheckBox",array(
			"name" => "object[isStickUrl]",
			"label" => CMSMessageManager::get("SOYCMS_ADD_HYPERLINK_TO_BLOG_ENTRYPAGE"),
			"value" => 1,
			"selected" => $this->entity->getIsStickUrl(),
			"visible" =>  (count($this->blogPages) > 0)
		));

		$style = SOY2HTMLFactory::createInstance("SOY2HTMLStyle");
		$style->display = ($this->entity->getIsStickUrl()) ? "" : "none";

		$this->createAdd("blog_page_list","HTMLSelect",array(
			"name" => "object[blogPageId]",
			"selected" => $this->entity->getBlogPageId(),
			"options" => $this->blogPages,
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		$this->createAdd("blog_page_list_label","HTMLLabel",array(
			"text" => CMSMessageManager::get("SOYCMS_BLOCK_SELECT_BLOG_TITLE"),
			"visible" => (count($this->blogPages) > 0),
			"style" => $style
		));

		$this->createAdd("main_form","HTMLForm",array());

		if(count($this->blogPages) === 0){
			DisplayPlugin::hide("blog_link");
		}

	}

	/**
	 * ラベル表示コンポーネントの実装を行う
	 */
	function setEntity(LabeledBlockComponent $block){
		$this->entity = $block;
	}

	/**
	 * ブログページを渡す
	 *
	 * array(ページID => )
	 */
	function setBlogPages($pages){
		$this->blogPages = $pages;
	}

   /**
     *  ラベルオブジェクトのリストを返す
     *  NOTE:個数に考慮していない。ラベルの量が多くなるとpagerの実装が必要？
     */
    function getLabelList(){
    	$dao = SOY2DAOFactory::create("cms.LabelDAO");
    	return $dao->get();
    }

	function getTemplateFilePath(){

		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"||!file_exists(CMS_BLOCK_DIRECTORY . "LabeledBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html")){
		   return CMS_BLOCK_DIRECTORY . "LabeledBlockComponent" . "/form.html";
		}else{
			return CMS_BLOCK_DIRECTORY . "LabeledBlockComponent" . "/form_".SOYCMS_LANGUAGE.".html";
		}
	}

}


class LabeledBlockComponent_ViewPage extends HTMLList{

	var $isStickUrl;
	var $articlePageUrl;
	var $blogPageId;
	var $editable = true;
	var $labelId;

	function setIsStickUrl($flag){
		$this->isStickUrl = $flag;
	}

	function setArticlePageUrl($articlePageUrl){
		$this->articlePageUrl = $articlePageUrl;
	}

	function setBlogPageId($id){
		$this->blogPageId = $id;
	}

	function setEditable($flag){
		$this->editable = $flag;
	}

	function setLabelId($labelId){
		$this->labelId = $labelId;
	}

	function getStartTag(){

		if(defined("CMS_PREVIEW_MODE") && $this->editable){
			return parent::getStartTag() . CMSUtil::getEntryHiddenInputHTML('<?php echo $'.$this->getId().'["entry_id"]; ?>','<?php echo strip_tags($'.$this->getId().'["title"]); ?>');
		}else{
			return parent::getStartTag();
		}
	}

	function getEndTag(){

		if(defined("CMS_PREVIEW_MODE") && $this->editable){
			return parent::getEndTag().'<?php echo "<button type=\"button\" class=\"cms_hidden_entry_id\" blocklabelid=\"'.$this->labelId.'\" style=\"display:none;\">ここに記事を追加する</button>"; ?>';
		}else{
			return parent::getEndTag();
		}
	}

	function populateItem($entity){

		$hTitle = htmlspecialchars($entity->getTitle(), ENT_QUOTES, "UTF-8");
		$entryUrl = $this->articlePageUrl.rawurlencode($entity->getAlias());

		if($this->isStickUrl){
			$hTitle = "<a href=\"".htmlspecialchars($entryUrl, ENT_QUOTES, "UTF-8")."\">".$hTitle."</a>";
		}

		$this->createAdd("entry_id","CMSLabel",array(
			"text"=> $entity->getId(),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("title","CMSLabel",array(
			"html"=> $hTitle,
			"soy2prefix"=>"cms"
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

		//entry_link追加
		//cms:idで呼び出せるように　2009.04.14
		$this->createAdd("entry_link","HTMLLink",array(
			"link" => $entryUrl,
			"soy2prefix"=>"cms"
		));

		//リンクの付かないタイトル 1.2.6～
		$this->createAdd("title_plain","CMSLabel",array(
			"text"=> $entity->getTitle(),
			"soy2prefix"=>"cms"
		));

		//1.2.7～
		$this->createAdd("more_link","HTMLLink",array(
			"soy2prefix"=>"cms",
			"link" => $entryUrl ."#more",
			"visible"=>(strlen($entity->getMore()) != 0)
		));
		
		$this->createAdd("more_link_no_anchor", "HTMLLink", array(
			"soy2prefix"=>"cms",
			"link" => $entryUrl,
			"visible"=>(strlen($entity->getMore()) != 0)
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

		$this->createAdd("entry_url","HTMLLabel",array(
			"text"=>$entryUrl,
			"soy2prefix"=>"cms",
		));

		CMSPlugin::callEventFunc('onEntryOutput',array("entryId"=>$entity->getId(),"SOY2HTMLObject"=>$this,"entry"=>$entity));
	}

}

class LabeledBlock_LabelList extends HTMLList{
	private $currentLabel;

	function populateItem($entity){

		$elementID = "label_".$entity->getId();

		$this->createAdd("radio","HTMLCheckBox",array(
			"value"     => $entity->getId(),
			"selected"  => ((string)$this->currentLabel == (string)$entity->getId()),
			"elementId" => $elementID
		));
		$this->createAdd("label","HTMLModel",array(
			"for" => $elementID,
		));
		$this->createAdd("label_text","HTMLLabel",array(
			"text" => $entity->getCaption(),
		));

		$this->createAdd("label_icon","HTMLImage",array(
			"src" => $entity->getIconUrl()
		));
	}

	function getCurrentLabel() {
		return $this->currentLabel;
	}
	function setCurrentLabel($currentLabel) {
		$this->currentLabel = $currentLabel;
	}
}
?>
