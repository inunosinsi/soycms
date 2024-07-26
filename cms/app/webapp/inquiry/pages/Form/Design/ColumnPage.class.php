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

		parent::__construct();

		$this->createAdd("column_list", "_common.Form.Design.ColumnListComponent", array(
			"list" => self::_columns($this->id),
			"isLinkageSOYMail" => true,
			"isLinkageSOYShop" => self::_checkSOYShopConnect($this->id),
			"formDesign" => self::_getFormDesignById($this->id)	//何のテンプレートを使用しているか？
		));
	}

	/**
	 * @param int
	 * @return array
	 */
	private function _columns(int $formId){
		try{
			return $this->dao->getOrderedColumnsByFormId($formId);
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * @param int
	 * @return bool
	 */
	private function _checkSOYShopConnect(int $formId){
		$connectConfig = self::_getFormConfigById($formId)->getConnect();
		return ($connectConfig["siteId"] > 0);
	}

	/**
	 * @param int
	 * @return string
	 */
	private function _getFormDesignById(int $formId){
		$designConfig = self::_getFormConfigById($formId)->getDesign();
		return (isset($designConfig["theme"])) ? $designConfig["theme"] : "default";
	}

	/**
	 * @param int
	 * @return array
	 */
	private function _getFormConfigById(int $formId){
		static $cnf;
		if(is_null($cnf)){
			try{
				$cnf = $this->formDao->getById($formId)->getConfigObject();
			}catch(Exception $e){
				$cnf = array();
			}
		}
		return $cnf;
	}
}
