<?php

class ColumnPage extends WebPage{
	var $id;
	var $dao;
	var $errorMessage;
	var $formDao;
	
	function doPost(){
		
	}
	
	function prepare(){
		$this->dao = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");
		$this->formDao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
				
		parent::prepare();
	}	
	
	function __construct($args) {
		if(count($args) < 1) CMSApplication::jump("Form");
		$this->id = (int)$args[0];
		
		//レイヤーモードで
		CMSApplication::setMode("layer");
		
		WebPage::__construct();
				
		$this->createAdd("column_list", "ColumnList", array(
			"list" => $this->getColumns($this->id),
			"isLinkageSOYMail" => true,
			"isLinkageSOYShop" => $this->checkSOYShopConnect($this->id)
		));
	}
	
	function getColumns($formId){
		try{
			$columns = $this->dao->getOrderedColumnsByFormId($formId);
		}catch(Exception $e){
			$columns = array();
		}
		return $columns;
	}
	
	function checkSOYShopConnect($id){
		try{
			$form = $this->formDao->getById($this->id);
		}catch(Exception $e){
			$form = new SOYInqiry_Form();
		}
		
		$connectConfig = $form->getConfigObject()->getConnect();
		return ($connectConfig["siteId"] > 0);
	}
}

class ColumnList extends HTMLList{
	private $isLinkageSOYMail;
	private $isLinkageSOYShop;
	
	protected function populateItem($entity){
		
		$this->createAdd("column_hash","HTMLLabel",array(
			"name" => "column_" . $entity->getId(),
			"text" => $entity->getColumnId()
		));
		
		$this->createAdd("label","HTMLInput",array(
			"name" => "Column[label]",
			"value" => $entity->getLabel()
		));
		
		$this->createAdd("column_type","HTMLLabel",array(
			"text" => $entity->getTypeText()
		));
		
		$this->createAdd("configure_link","HTMLLink",array(
			"onclick" => '$(\'#configure_wrapper_' . $entity->getId() . '\').toggle();',
			"link" => "javascript:void(0);"
		));
		
		$this->createAdd("configure_wrapper","HTMLModel",array(
			"id" => "configure_wrapper_" . $entity->getId() 
		));
		
		
		$this->createAdd("require_cell","HTMLModel",array(
			"onclick" => 'changeColor(this,$(\''."#column_require_" . $entity->getId().'\'));changeRepalce($(\''."#column_replace_" . $entity->getId().'\'),$(\''."#column_require_" . $entity->getId().'\'));',
			"style" => ($entity->getRequire()) ? "background-color:#FF8888;" : "",
			"checkColor" => "#FF8888"
		));
		
		$this->createAdd("not_require","HTMLInput",array(
			"type" => "hidden",
			"name" => "Column[require]",
			"value" => 0,
		));
		
		$this->createAdd("require","HTMLCheckbox",array(
			"elementId" => "column_require_" . $entity->getId(),
			"name" => "Column[require]",
			"selected" => $entity->getRequire(),
			"value" => 1,
			"onmouseup" => 'changeColor(this.parentNode,this);changeRepalce($(\''."#column_replace_" . $entity->getId().'\'),$(\''."#column_require_" . $entity->getId().'\'));'
		));
		
		$column = $entity->getColumn();
		$this->createAdd("configure","HTMLLabel",array(
			"html" => $column->getConfigForm()
		));
		
		$this->createAdd("column_form","HTMLForm",array(
			"action" => SOY2PageController::createLink(APPLICATION_ID . ".Form.Design.UpdateColumn." . $entity->getId())
		));
		
		$this->createAdd("linkage_soymail","HTMLSelect",array(
			"name" => "Column[config][SOYMailTo]",			
			"options" => (is_array($column->getLinkagesSOYMailTo())) ? $column->getLinkagesSOYMailTo() : array(),
			"selected" => $column->getSOYMailTo(),
			"visible" => $this->isLinkageSOYMail && count($column->getLinkagesSOYMailTo())>1
		));
		
		$this->createAdd("linkage_soyshop","HTMLSelect",array(
			"name" => "Column[config][SOYShopFrom]",			
			"options" => (is_array($column->getLinkagesSOYShopFrom())) ? $column->getLinkagesSOYShopFrom() : array(),
			"selected" => $column->getSOYShopFrom(),
			"visible" => $this->isLinkageSOYShop && count($column->getLinkagesSOYShopFrom())>1
		));
		
		$this->createAdd("replace","HTMLInput",array(
			"value" => $column->getReplacement(),
			"name" => "Column[config][replacement]",
		));
		
		$this->createAdd("replace_wrapper","HTMLModel",array(
			"id" => "column_replace_".$entity->getId(),
			"style" => ($column->getIsRequire()) ? "" : "visibility:hidden"
		));
		
		$this->createAdd("annotation","HTMLInput",array(
			"value" => $column->getAnnotation(),
			"name" => "Column[config][annotation]"
		));
	}


	/**#@+
	 * 
	 * @access public
	 */
	function getIsLinkageSOYMail() {
		return $this->isLinkageSOYMail;
	}
	function setIsLinkageSOYMail($isLinkageSOYMail) {
		$this->isLinkageSOYMail = $isLinkageSOYMail;
	}
	
	/**#@+
	 * 
	 * @access public
	 */
	function getIsLinkageSOYShop() {
		return $this->isLinkageSOYShop;
	}
	function setIsLinkageSOYShop($isLinkageSOYShop) {
		$this->isLinkageSOYShop = $isLinkageSOYShop;
	}
	
	/**#@-*/

}

?>