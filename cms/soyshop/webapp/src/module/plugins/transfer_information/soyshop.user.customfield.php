<?php

class TransferInformationUserCustomfield extends SOYShopUserCustomfield{

	function __construct(){
		SOY2::import("module.plugins.transfer_information.util.TransferInfoUtil");
	}
	function getForm($app, int $userId){
		return array(TransferInfoUtil::BANK_INFO => array("name" => "振込先情報", "form" => self::_buildTransferForm($userId)));
	}

	private function _buildTransferForm(int $userId){
		SOY2::import("module.plugins.transfer_information.form.TransferInfoBankFormPage");
		$form = SOY2HTMLFactory::createInstance("TransferInfoBankFormPage");
		$form->setUserId($userId);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * UserAttributeに登録する
	 * @param MyPageLogic || CartLogic $app
	 * @param integer $userId
	 */
	function register($app, int $userId){
		if(isset($_POST[TransferInfoUtil::BANK_INFO])){
			$isEmpty = true;
			foreach($_POST[TransferInfoUtil::BANK_INFO] as $v){
				if(strlen($v)) {
					$isEmpty = false;
					break;
				}
			}

			$attr = soyshop_get_user_attribute_object($userId, TransferInfoUtil::BANK_INFO);
			$v = (!$isEmpty) ? soy2_serialize($_POST[TransferInfoUtil::BANK_INFO]) : "";
			$attr->setValue($v);
			soyshop_save_user_attribute_object($attr);
		}
	}
}
SOYShopPlugin::extension("soyshop.user.customfield", "transfer_information", "TransferInformationUserCustomfield");
