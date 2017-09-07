<?php

class AddColumnPage extends WebPage{
	
	var $dao;
	var $columnDao;
	var $form;
	var $formId;
	
	function doPost(){
	
		$formId = $this->formId;
    	$this->addColumn($formId);
    	
    	//問題なく追加された場合
    	$this->outputHeader();
    	$this->outputReload();
    	$this->outputFooter();
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
    		$this->outputHeader();
	    	$this->outputReload();
	    	$this->outputFooter();
	    	exit;
    	}
    	
    	$this->createAdd("add_form","HTMLForm");
    	
    	$this->createAdd("form_name","HTMLLabel",array(
    		"text" => $this->form->getName()
    	));
    	
    	$this->createAdd("column_name","HTMLInput",array(
    		"name" => "Column[label]",
    	));    	
    	$this->createAdd("column_type","HTMLSelect",array(
    		"name" => "Column[type]",
    		"options" => SOYInquiry_Column::$columnTypes
    	));
    	$this->createAdd("column_require","HTMLCheckbox",array(
    		"name" => "Column[require]",
    		"value" => 1,
    		"label" => "必須項目にする"
    	));
    	
    	$columns = $this->columnDao->getOrderedColumnsByFormId($this->formId);
    	
    	$this->createAdd("column_list","ColumnList",array(
    		"list" => $columns    	
    	));
    	
    	$this->createAdd("order_default","HTMLInput",array(
    		"name" => "Column[order]",
    		"value" => 10 * count($columns) + 1,
    		"checked" => "checked"
    	));
    }
    
    /**
     * カラム追加
     */
    function addColumn($formId){
    	    	
    	$post = $_POST["Column"];
    	
    	$column = new SOYInquiry_Column();
    	$column->setFormId($formId);
    	
    	SOY2::cast($column,(object)$post);
    	
    	$this->columnDao->begin();
    	$this->columnDao->insert($column);
    	$this->columnDao->reorderColumns($formId);
    	$this->columnDao->commit();
    	
    }
    
    function outputReload(){
    	
    	echo '<script type="text/javascript">';
    	echo 'top.reloadColumnPage();';
    	echo '</script>';
    	
    }
    
    /* 共通 */
    function outputHeader(){
    	echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>';
    }
    function outputFooter(){
    	echo '</body></html>';
    }
   
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
			"name" => "Column[order]",
			"label" => "ここに追加",
			"value" => $entity->getOrder() - 1,
		));			
	}
	
}
?>