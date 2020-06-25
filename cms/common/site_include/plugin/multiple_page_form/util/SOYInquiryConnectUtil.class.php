<?php

class SOYInquiryConnectUtil {

	public static function getConfig(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("mpf_plugin.config", array(
			"form_id" => null
		));
	}

	public static function saveConfig($values){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("mpf_plugin.config", $values);
	}

	public static function getInquiryFormList(){
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");

		try{
			$forms = SOY2DAOFactory::create("SOYInquiry_FormDAO")->get();
		}catch(Exception $e){
			$forms = array();
		}

		SOYAppUtil::resetAppMode($old);
		if(!count($forms)) return array();

		$list = array();
		foreach($forms as $form){
			$list[$form->getFormId()] = $form->getName();
		}

		return $list;
	}

	public static function connect($formId){
		//switchする前にメールアドレスを取得しておく必要がある
		$mailAddress = MPFRouteUtil::getMailAddressOnAllRoute();
		$replaceList = MPFRouteUtil::getReplacementStringList();

		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");

		try{
			$form = SOY2DAOFactory::create("SOYInquiry_FormDAO")->getByFormId($formId);
		}catch(Exception $e){
			$form = new SOYInquiry_Form();
		}

		//接続に成功している
		if(is_numeric($form->getId())){
			$logic = SOY2Logic::createInstance("logic.InquiryLogic");
			$inq = $logic->addInquiry($form->getId());
			$trackNum = $logic->getTrackingNumber($inq);
			$inq->setTrackingNumber($trackNum);

			SOY2::import("site_include.plugin.multiple_page_form.util.MPFRouteUtil");
			$data = MPFRouteUtil::getAllPageValues();

			//bodyを作成
			$body = self::_buildInquiryBody($inq, $data);
			$logic->updateInquiry($inq, $body, $data, $_SERVER["REDIRECT_URL"]);

			/** メールの送信 **/
			$serverConfig = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get();
			$mailLogic = SOY2Logic::createInstance("logic.MailLogic", array(
				"serverConfig" => $serverConfig,
				"formConfig" => $form->getConfigObject()
			));
			$mailLogic->prepareSend();

			//管理者用メールボディ
			$mailBody = self::_convertString($body, $replaceList);
			if($form->getConfigObject()->getIsIncludeAdminURL()){
				//$mailBody[0] .= "\r\n\r\n-- \r\n問い合わせへのリンク:\r\n" . $this->getInquiryLink($inquiry, $this->serverConfig) . "\r\n";
    		}

			SOY2::import("util.SOYInquiryUtil");
			$tmpDir = SOYInquiryUtil::getTemplateDir($form->getConfigObject()->getTheme());

			//拡張 - 管理側のメール
			if(is_readable($tmpDir . "mail.admin.php")){
				ob_start();
				include_once($tmpDir . "mail.admin.php");
				$mailBody .= ob_get_contents();
				ob_end_clean();
			}

			$title = self::_convertString($form->getConfigObject()->getNotifyMailSubject(), $replaceList);
			$mailLogic->sendMail(
				$serverConfig->getAdministratorMailAddress(),
				$title,
				$mailBody,
				null
			);

			//ユーザ側
			if(strlen($mailAddress)){
				// //ユーザー用メールボディ
				$mailCnf = $form->getConfigObject()->getConfirmMail();
				$mailBody = self::_convertString($mailCnf["header"] . "\n" . $body . $mailCnf["footer"], $replaceList);

				//拡張 - ユーザ側のメール
				if(is_readable($tmpDir . "mail.user.php")){
					ob_start();
					include_once($tmpDir . "mail.user.php");
					$mailBody .= ob_get_contents();
					ob_end_clean();
				}

				try{
					$mailLogic->sendMail(
						$mailAddress,
						self::_convertString($mailCnf["title"], $replaceList),
						$mailBody,
						$serverConfig->getAdministratorName(),
						$serverConfig->getReturnMailAddress(),
						$serverConfig->getReturnName()
					);
				}catch(Exception $e){
					var_dump($e);
				}

			}
		}

		SOYAppUtil::resetAppMode($old);
	}

	private static function _buildInquiryBody(SOYInquiry_Inquiry $inquiry, $data){
		$body = array();

    	$maxLabelWidth = 0;
    	$labels = array();
    	$values = array();

    	foreach($data as $idx => $col){
			$label = $col["label"];
			$value = trim($col["value"]);

    		//改行が含まれる場合は空白をあける
    		if(strpos($value,"\n") !== false){
    			$label = "\n" . $label;
    			$value = "\n" . $value."\n";
    		}

    		$labels[$idx] = $label;
    		$values[$idx] = $value;

    		$maxLabelWidth = max(mb_strwidth($label), $maxLabelWidth);
    	}

		$label = "問い合わせ番号";
		$body[] = $label.str_repeat(" ", max(0, $maxLabelWidth - mb_strwidth($label))) . ": ".$inquiry->getTrackingNumber();
		$label = "問い合わせ日時";
		$body[] = $label.str_repeat(" ", max(0, $maxLabelWidth - mb_strwidth($label))) . ": ".date("Y-m-d H:i:s",$inquiry->getCreateDate());

    	foreach($labels as $idx => $label){
    		$body[] = $label.str_repeat(" ", max(0, $maxLabelWidth - mb_strwidth($label))) . ": ".$values[$idx];
    	}

    	return implode("\n",$body);
	}

	//第二引数に置換文字列用のリストを入れる
	private static function _convertString($str, $replaces){
		if(!is_array($replaces) || !count($replaces)) return $str;
		foreach($replaces as $rpl => $v){
			$str = str_replace($rpl, $v, $str);
		}
		return $str;
	}
}
