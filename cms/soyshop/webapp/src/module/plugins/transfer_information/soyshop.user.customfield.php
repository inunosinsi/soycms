<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class TransferInformationUserCustomfield extends SOYShopUserCustomfield{

	function __construct(){
		SOY2::import("module.plugins.transfer_information.util.TransferInfoUtil");
	}

	function clear($app){}
	function doPost($param){}

	function getForm($app, $userId){
		return array(TransferInfoUtil::BANK_INFO => array("name" => "振込先情報", "form" => self::_buildTransferForm($userId)));
	}

	private function _buildTransferForm($userId){
		SOY2::import("module.plugins.transfer_information.form.TransferInfoBankFormPage");
		$form = SOY2HTMLFactory::createInstance("TransferInfoBankFormPage");
		$form->setUserId($userId);
		$form->execute();
		return $form->getObject();
	}

	function hasError($param){}
	function confirm($app){}

	/**
	 * UserAttributeに登録する
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, $userId){
		if(isset($_POST[TransferInfoUtil::BANK_INFO])){
			$isEmpty = true;
			foreach($_POST[TransferInfoUtil::BANK_INFO] as $v){
				if(strlen($v)) {
					$isEmpty = false;
					break;
				}
			}

			$attr = TransferInfoUtil::getUserAttr($userId, TransferInfoUtil::BANK_INFO);
			if($isEmpty){
				$attr->setValue("");
			}else{
				$attr->setValue(soy2_serialize($_POST[TransferInfoUtil::BANK_INFO]));
			}
			TransferInfoUtil::saveAttr($attr);
		}
	}
}
SOYShopPlugin::extension("soyshop.user.customfield", "transfer_information", "TransferInformationUserCustomfield");
