<?php

class ChangeOrderPage extends WebPage{

	var $dao;
	var $columnDao;
	var $form;
	var $formId;

	function doPost(){

		$orders = $_POST["displayOrder"];

		$this->columnDao->begin();

		$count = 1;
    	foreach($orders as $id){
    		$this->columnDao->updateDisplayOrder($id,$count * 10);
    		$count++;
    	}

    	$this->columnDao->commit();


    	$this->columnDao->reorderColumns($this->formId);

    	self::outputReload();
    	exit;

	}

	function prepare(){
		$this->dao = SOY2DAOFactory::create("SOYInquiry_FormDAO");
    	$this->form = new SOYInquiry_Form();

    	$this->columnDao = SOY2DAOFactory::create("SOYInquiry_ColumnDAO");

    	parent::prepare();
	}

	function __construct($args) {

    	$this->formId = $args[0];
    	parent::__construct();

    	//レイヤーモードで
    	CMSApplication::setMode("layer");

    	try{
    		$this->form = $this->dao->getById($this->formId);
    	}catch(Exception $e){
    		self::outputReload();
	    	exit;
    	}

    	$this->createAdd("update_form","HTMLForm");

    	$columns = $this->columnDao->getOrderedColumnsByFormId($this->formId);

    	$this->createAdd("column_list","_common.ColumnListComponent",array(
    		"list" => $columns,
			"mode" => "change"
    	));
	}

	private function outputReload(){
		echo '<script type="text/javascript">';
    	echo 'parent.location.href=parent.location.href;';
    	echo '</script>';
	}

    /* 共通 */
    // function outputHeader(){
    // 	echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>';
    // }
    // function outputFooter(){
    // 	echo '</body></html>';
    // }
}

class ColumnList extends HTMLList{

	protected function populateItem($entity){
		if(!is_a($entity,"SOYInquiry_Column")){
			$entity = new SOYInquiry_Column();
		}

		$obj = $entity->getColumn();
		$label = $obj->getLabel();

		$this->createAdd("label","HTMLLabel",array(
			"text" => $label,
			"visible" => (strlen($label)>0)
		));

		$this->createAdd("form","HTMLLabel",array(
			"html" => $obj->getForm(),
			"colspan" => (strlen($label)>0) ? "1" : "2"
		));

		$this->createAdd("display_order","HTMLCheckbox",array(
			"elementId" => "display_order_" . $entity->getId(),
			"name" => "displayOrders",
			"value" => $entity->getOrder(),
		));

		$this->createAdD("display_order_hidden","HTMLInput",array(
			"name" => "displayOrder[]",
			"value" => $entity->getId()
		));

		$this->createAdd("column_row","HTMLModel",array(
			"onclick" => "select_row(this,'display_order_".$entity->getId()."');"
		));
	}
}
