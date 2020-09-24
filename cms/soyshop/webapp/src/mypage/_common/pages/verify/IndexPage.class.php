<?php
class IndexPage extends MainMyPagePageBase{

	const MODE_UPLOAD = "upload";
	const MODE_DELETE = "delete";

    function doPost(){
		if(soy2_check_token() && soy2_check_referer()){
			$userId = $this->getUser()->getId();

			$doExe = false;

			for($i = 1; $i <= 2; $i++){
				$prop = ($i == 1) ? PurchaseAppUtil::UPLOAD_PROP : PurchaseAppUtil::UPLOAD_PROP_2;
				if(isset($_FILES[$prop])){
					$imgName = PurchaseAppUtil::createHashImgName($_FILES[$prop]["name"]);
					$filepath = $this->getUser()->getAttachmentsPath() . $imgName;

					$tmpname = $_FILES[$prop]["tmp_name"];
					@move_uploaded_file($tmpname, $filepath);

					@chmod($filepath,0604);

					if(PurchaseAppUtil::checkResizableSize($filepath)){
						soy2_resizeimage($filepath, $filepath, PurchaseAppUtil::RESIZE_WIDTH);
					}

					$attr = PurchaseAppUtil::getUserAttr($userId, $prop);
					$attr->setValue($imgName);

					PurchaseAppUtil::saveAttr($attr);

					//運営側の確認を外す
					$attr = PurchaseAppUtil::getUserAttr($userId, PurchaseAppUtil::VERIFIED_IDENTITY);
					$attr->setValue("");
					PurchaseAppUtil::saveAttr($attr);

					$doExe = true;
				}
			}

			if($doExe){
				//メールを送信する
				self::_send(self::MODE_UPLOAD);
				$this->jump("verify/" . $this->id . "?updated");
			}

			for($i = 1; $i <= 2; $i++){
				$prop = ($i == 1) ? PurchaseAppUtil::UPLOAD_PROP : PurchaseAppUtil::UPLOAD_PROP_2;
				if(isset($_POST[$prop . "_delete"])){
					$attr = PurchaseAppUtil::getUserAttr($userId, $prop);
					$attr->setValue("");
					PurchaseAppUtil::saveAttr($attr);
				}
			}

			if($doExe){
				//メールを送信する
				self::_send(self::MODE_DELETE);
				$this->jump("verify/" . $this->id . "?updated");
			}

			$this->jump("verify/" . $this->id . "?failed");
		}
	}

	private function _send($mode){

		$content = array();
		$content[] = $this->getUser()->getName() . "様の本人確認書類に変更がありました。";
		$content[] = "-----------------------------------------";
		switch($mode){
			case self::MODE_UPLOAD:
				$content[] = "本人確認書類がアップロードされました。";
				$content[] = "内容に誤りがないかご確認ください。";
				break;
			case self::MODE_DELETE:
				$content[] = "本人確認書類が削除されました。";
				break;
		}

		$body = implode("\n",$content);

		return SOY2Logic::createInstance("logic.mail.MailLogic")->sendMail("admin", "本人確認書類の変更がありました", $body);
	}

    function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		//買取プラグインがアクティブでない場合はトップページに飛ばす
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("purchase_manager")) $this->jumpToTop();

		$user = $this->getUser();

		SOY2::import("module.plugins.purchase_manager.util.PurchaseAppUtil");

        parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));

		//本人確認用画像
		$imgName = PurchaseAppUtil::getUserAttr($user->getId(), PurchaseAppUtil::UPLOAD_PROP)->getValue();
		$imgPath = $user->getAttachmentsPath() . $imgName;
		$isIdImg = (strlen($imgName) && file_exists($imgPath));
		DisplayPlugin::toggle("is_identity", $isIdImg);
		DisplayPlugin::toggle("no_identity", !$isIdImg);

		$imgUrl = $user->getAttachmentsUrl() . $imgName;
		$this->addImage("idetity_image", array(
			"src" => ($isIdImg && PurchaseAppUtil::checkResizableSize($imgPath)) ? "/" . SOYSHOP_ID . "/im.php?src=" . $imgUrl . "&width=800" : $imgUrl
		));

		$isId = PurchaseAppUtil::checkVerifiedIdByUserId($user->getId());
		$this->addLabel("identity_status", array(
			"text" => ($isId) ? "確認済み" : "確認中",
			"attr:class" => ($isId) ? "alert alert-success" : "alert alert-warning"
		));

		$this->addForm("delete_form");

		$this->addCheckBox("identity_image_delete", array(
			"name" => PurchaseAppUtil::UPLOAD_PROP . "_delete",
			"value" => 1
		));

		$this->addForm("upload_form");

		$this->addInput("upload_input", array(
			"name" => PurchaseAppUtil::UPLOAD_PROP
		));

		$this->addInput("upload_input_2", array(
			"name" => PurchaseAppUtil::UPLOAD_PROP_2
		));

		$this->addForm("re_upload_form");

		$this->addInput("re_upload_input", array(
			"name" => PurchaseAppUtil::UPLOAD_PROP
		));

		$this->addInput("re_upload_input_2", array(
			"name" => PurchaseAppUtil::UPLOAD_PROP_2
		));
    }
}
