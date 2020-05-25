<?php

class DepositManagerDetailPage extends WebPage {

	private $detailId;

	function __construct(){
		SOY2::import("module.plugins.deposit_manager.util.DepositManagerUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Deposit"])){
			$id = self::_logic()->save($this->detaiId, $_POST["Deposit"]);
			if(is_numeric($id)){
				SOY2PageController::jump("Extension.Detail.deposit_manager." . $id . "?updated");
			}
		}
		SOY2PageController::jump("Extension.Detail.deposit_manager." . $this->detailId . "?failed");
	}

	function execute(){
		$deposit = self::_logic()->getById($this->detailId);
		if(is_numeric($deposit->getUserId())){
			$userId = $deposit->getUserId();
		}else{
			$userId = (isset($_GET["user_id"])) ? (int)$_GET["user_id"] : null;
		}

		$user = soyshop_get_user_object($userId);
		if(!is_numeric($user->getId())) SOY2PageController::jump("Extension.deposit_manager");

		parent::__construct();

		self::_buildForm($deposit, $user);
	}

	private function _buildForm(SOYShop_DepositManagerDeposit $deposit, SOYShop_User $user){
		$this->addForm("form");

		$this->addLink("user_name", array(
			"link" => SOY2PageController::createLink("User.Detail." . $user->getId()),
			"text" => $user->getName()
		));
		$this->addInput("user_id_hidden", array(
			"name" => "Deposit[userId]",
			"value" => $user->getId()
		));
		$this->addSelect("subject_id", array(
			"name" => "Deposit[subjectId]",
			"options" => DepositManagerUtil::getSubjectList(true),
			"selected" => $deposit->getSubjectId(),
			"attr:required" => "required"
		));

		$this->addInput("deposit_price", array(
			"name" => "Deposit[depositPrice]",
			"value" => $deposit->getDepositPrice(),
			"style" => "width:150px;",
			"attr:required" => "required"
		));

		$this->addInput("deposit_date", array(
			"name" => "Deposit[depositDate]",
			"value" => ((int)$deposit->getDepositDate() > 0) ? date("Y-m-d", $deposit->getDepositDate()) : "",
			"style"=> "width:200px;",
			"readonly" => true,
			"attr:required" => "required"
		));

		$this->addTextArea("memo", array(
			"name" => "Deposit[memo]",
			"value" => $deposit->getMemo()
		));
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.deposit_manager.logic.DepositLogic");
		return $logic;
	}

	function setDetailId($detailId){
		$this->detailId = $detailId;
	}
}
