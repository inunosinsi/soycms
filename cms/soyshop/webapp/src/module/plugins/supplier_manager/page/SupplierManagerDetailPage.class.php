<?php

class SupplierManagerDetailPage extends WebPage {

	private $detailId;

	function __construct(){
		SOY2::import("module.plugins.supplier_manager.component.PurchaseHistoryListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			$supplier = SOY2::cast(self::_logic()->getById($this->detailId), $_POST["Supplier"]);
			//住所
			$supplier->setZipCode($_POST["Supplier"]["zipCode1"] . "-" . $_POST["Supplier"]["zipCode2"]);

			$this->detailId = self::_logic()->save($supplier);


			if(is_numeric($this->detailId)){
				SOY2PageController::jump("Extension.Detail.supplier_manager." . $this->detailId . "?updated");
			}
		}

		SOY2PageController::jump("Extension.Detail.supplier_manager." . $this->detailId . "?failed");
	}

	function execute(){
		parent::__construct();

		self::_buildForm();
		self::_buildPurchaseArea();
	}

	private function _buildForm(){

		$supplier = self::_logic()->getById($this->detailId);

		$this->addForm("form");

		$this->addInput("name", array(
			"name" => "Supplier[name]",
			"value" => $supplier->getName(),
			"attr:required" => "required"
		));

		//郵便番号をバラして使う
		$zip = explode("-", $supplier->getZipCode());
		$this->addInput("zip_code1", array(
			"name" => "Supplier[zipCode1]",
			"value" => (isset($zip[0])) ? $zip[0] : null,
			"style" => "ime-mode:inactive;",
			"attr:pattern" => "\d{3}"
		));

		$this->addInput("zip_code2", array(
			"name" => "Supplier[zipCode2]",
			"value" => (isset($zip[1])) ? $zip[1] : null,
			"style" => "ime-mode:inactive;",
			"attr:pattern" => "\d{4}"
		));

		//都道府県
		$this->addSelect("area", array(
			"name" => "Supplier[area]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => $supplier->getArea()
		));

		//住所入力1
		$this->addInput("address1", array(
			"name" => "Supplier[address1]",
			"value" => $supplier->getAddress1(),
		));

		//住所入力2
		$this->addInput("address2", array(
			"name" => "Supplier[address2]",
			"value" => $supplier->getAddress2(),
		));

		//電話番号
		$this->addInput("telephone_number", array(
			"name" => "Supplier[telephoneNumber]",
			"value" => $supplier->getTelephoneNumber(),
			"style" => "ime-mode:inactive;",
		));

		//FAX番号
		$this->addInput("fax_number", array(
			"name" => "Supplier[faxNumber]",
			"value" => $supplier->getFaxNumber(),
			"style" => "ime-mode:inactive;",
		));

		//携帯電話番号
		$this->addInput("cellphone_number", array(
			"name" => "Supplier[cellphoneNumber]",
			"value" => $supplier->getCellphoneNumber(),
			"style" => "ime-mode:inactive;",
		));

		$this->addInput("mail_address", array(
			"name" => "Supplier[mailAddress]",
			"value" => $supplier->getMailAddress(),
			"style" => "ime-mode:inactive;",
		));

		//URL
		$this->addInput("url", array(
			"name" => "Supplier[url]",
			"value" => $supplier->getUrl(),
			"style" => "ime-mode:inactive;",
		));

		//URLの確認
		DisplayPlugin::toggle("url", strlen($supplier->getUrl()));
		$this->addLink("url_link", array(
			"link" => $supplier->getUrl(),
			"target" => "_blank"
		));

		$this->addModel("zip2address_js", array(
			"src" => soyshop_get_zip_2_address_js_filepath()
		));
	}

	private function _buildPurchaseArea(){
		$paid = self::_logic()->getPaidTotalBySupplierId($this->detailId);

		//支払金額
		$this->addLabel("paid_total", array(
			"text" => number_format($paid)
		));

		//未支払い分
		//最初に仕入の合算を取得
		$purchaseLogic = SOY2Logic::createInstance("module.plugins.purchase_slip_manager.logic.PurchaseLogic");
		$purchaseTotal = $purchaseLogic->getPurchaseTotalBySupplierId($this->detailId);

		$this->addLabel("unpaid_total", array(
			"text" => number_format($purchaseTotal - $paid)
		));

		//仕入の履歴
		SOY2::import("module.plugins.supplier_manager.util.SupplierManagerUtil");
		$itemIds = SupplierManagerUtil::getItemIdsBySupplierId($this->detailId);

		$hists = array();
		if(count($itemIds)){
			$hists = $purchaseLogic->getPurchaseHistoriesByItemIds($itemIds);
		}

		DisplayPlugin::toggle("purchase_history", count($hists));

		$this->createAdd("purchase_history_list", "PurchaseHistoryListComponent", array(
			"list" => $hists
		));
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.supplier_manager.logic.SupplierLogic");
		return $logic;
	}

	function setDetailId($detailId){
		$this->detailId = $detailId;
	}
}
