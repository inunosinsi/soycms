<?php

class InquiryLogic extends SOY2LogicBase{

	private $dao;
	private $form;

    /**
     * カラム→問い合わせ本文作成
     */
    function getInquiryBody($inquiry,$columns,$useMailBody = false){

    	$body = array();

    	$maxLabelWidth = 0;
    	$labels = array();
    	$values = array();

    	foreach($columns as $i => $column){
			//保存しない項目は飛ばす
			if($column->getNoPersistent()) continue;

    		$column->setInquiry($inquiry);

    		$id = $column->getId();
			if($useMailBody){	//HTMLタグを除きたいが念の為に、メール送信時のみに限定する
				$label = strip_tags($column->getLabel());
			}else{
				$label = $column->getLabel();
			}


			//連番の場合
			if($column->getType() == "SerialNumber"){
				SOY2::import("util.SOYInquiryUtil");
				$value = SOYInquiryUtil::buildSerialNumber(soy2_unserialize($column->getConfig()));
			}else{
				$value = ($useMailBody) ? $column->getColumn()->getMailText() : $column->getContent();
			}

    		//改行が含まれる場合は空白をあける
    		if(strpos($value,"\n") !== false){
    			$label = "\n" . $label;
    			$value = "\n" . $value."\n";
    		}

    		$labels[$id] = $label;
    		$values[$id] = $value;

    		$maxLabelWidth = max(mb_strwidth($label), $maxLabelWidth);

			//記事名 [SOY CMSブログ連携]の場合は次の行で確認用のURLを挿入
			if($useMailBody && $column->getType() == "SOYCMSBlogEntry"){
				SOY2::import("util.SOYInquiryUtil");
				$blogEntryUrl = SOYInquiryUtil::getBlogEntryUrlByInquiryId($inquiry->getId());
				if(strlen($blogEntryUrl)){
					$labels[$id . "_url"] = "記事のURL";
					$values[$id . "_url"] = $blogEntryUrl;

					$maxLabelWidth = max(mb_strwidth($labels[$id . "_url"]), $maxLabelWidth);
				}
			}
    	}

    	if($useMailBody){
    		$label = "問い合わせ番号";
    		$body[] = $label.str_repeat(" ", max(0, $maxLabelWidth - mb_strwidth($label))) . ": ".$inquiry->getTrackingNumber();
    		$label = "問い合わせ日時";
    		$body[] = $label.str_repeat(" ", max(0, $maxLabelWidth - mb_strwidth($label))) . ": ".date("Y-m-d H:i:s",$inquiry->getCreateDate());
    	}

    	foreach($labels as $id => $label){
    		$body[] = $label.str_repeat(" ", max(0, $maxLabelWidth - mb_strwidth($label))) . ": ".$values[$id];
    	}

    	return implode("\n",$body);
    }

    /**
     * 問い合わせメール本文作成
     */
    function getInquiryMailBody($inquiry,$columns){
    	return $this->getInquiryBody($inquiry,$columns,true);
    }

    /**
     * 問い合わせ追加
     *
     * @return SOYInquiry_Inquiry
     */
    function addInquiry($formId){

    	$inquiryDao = $this->getDAO();
    	$inquiry = new SOYInquiry_Inquiry();

    	$inquiry->setFormId($formId);
		$inquiry->setIpAddress($_SERVER['REMOTE_ADDR']);
    	$inquiry->setCreateDate(time());

    	$id = $inquiryDao->insert($inquiry);
    	$inquiry->setId($id);

		// GETでentry_idがあれば、soyinquiry_entry_relationに登録しておく
		$entryId = SOYInquiryUtil::getParameter("entry_id");
		if(is_numeric($entryId)){
			$relDao = SOY2DAOFactory::create("SOYInquiry_EntryRelationDAO");
			$rel = new SOYInquiry_EntryRelation();
			$rel->setInquiryId($id);
			$rel->setEntryId($entryId);

			$siteId = SOYInquiryUtil::getParameter("site_id");
			if(is_numeric($siteId)) $rel->setSiteId($siteId);

			$pageId = SOYInquiryUtil::getParameter("page_id");
			if(is_numeric($pageId)) $rel->setPageId($pageId);

			try{
				$relDao->insert($rel);
			}catch(Exception $e){
				//
			}
		}
		//セッションのクリア
		SOYInquiryUtil::clearParameters();

    	return $inquiry;

    }

    /**
     * 問い合わせ情報を更新
     */
    function updateInquiry(SOYInquiry_Inquiry $inquiry, $body, $data, $url){
		$inquiryDao = $this->getDAO();

    	$inquiry->setContent($body);
    	$inquiry->setData(serialize($data));
    	$inquiry->setFormUrl($url);

    	//問い合わせ番号生成->保存
    	if(is_null($inquiry->getTrackingNumber()))$inquiry->setTrackingNumber($this->getTrackingNumber($inquiry));

    	$inquiryDao->update($inquiry);
    	return true;
    }

    /**
     * 未読問い合わせを数える
     * @return number
     */
    function countUnreadInquiryByFormId($formId){
    	return $this->getDAO()->countUnreadInquiryByFormId($formId);
    }

    /**
     * フラグ別の問い合わせを数える
     * @return number
     */
    function countInquiryByFormIdByFlag($formId, $flag){
    	return $this->getDAO()->countInquiryByFormIdByFlag($formId, $flag);
    }

    /**
     * 問い合わせを数える（削除分を除く）
     * @return number
     */
    function countUndeletedInquiryByFormId($formId){
    	return $this->getDAO()->countUndeletedInquiryByFormId($formId);
    }

    /**
     * DAO取得
     */
    function getDAO(){
    	if(!$this->dao)$this->dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
    	return $this->dao;
    }


    /**
     * onSend
     */
    function invokeOnSend($inquiry, $columns){
    	foreach($columns as $column){
			$obj = $column->getColumn($this->form);
			$obj->onSend($inquiry);
		}

		self::_updateToSOYMail($columns);
		self::_updateToSOYShop($columns);
    }

    /**
     * CSV用のデータを作成
     *
     * @return array
     */
    function buildCSVData($inquiry, $columns){

		$res = array();

		foreach($columns as $column){
			//NoPersistentは無視して全項目を保存する
			$id = $column->getId();
			$column->setInquiry($inquiry);
			$res[$id] = $column->getContent();
		}

		return $res;
    }

    /**
     * SOYMailと同期して更新する
     */
    private function _updateToSOYMail($columns){
		list($sql, $binds) = self::_buildQueryAndBinds($columns, "mail");
		if(!strlen($sql)) return;

		//DSNの書き換え
		if(defined("SOYMAIL_DSN")){
			$old = SOY2DAOConfig::Dsn(SOYMAIL_DSN);
			try{
	    		$dao = new SOY2DAO();
	    		$dao->executeUpdateQuery($sql,$binds);
			}catch(Exception $e){
				//
			}

			SOY2DAOConfig::Dsn($old);
		}
    }

    /**
     * SOYShopと同期する
     */
    private function _updateToSOYShop($columns){
		list($sql, $binds) = self::_buildQueryAndBinds($columns, "shop");
		if(!strlen($sql)) return;

		if(defined("SOYINQUERY_SOYSHOP_CONNECT_SITE_ID") && strlen(SOYINQUERY_SOYSHOP_CONNECT_SITE_ID)){
			$old = SOYInquiryUtil::switchConfig();
			$siteDao = SOY2DAOFactory::create("SOYShop_SiteDAO");
			try{
				$site = $siteDao->getById(SOYINQUERY_SOYSHOP_CONNECT_SITE_ID);
			}catch(Exception $e){
				$site = new SOYShop_Site();
			}
			if(!defined("SOYSHOP_SITE_ID")) define("SOYSHOP_SITE_ID", $site->getSiteId());
			SOYInquiryUtil::resetConfig($old);

			//ここからSOY Shopの顧客名簿に値を放り込む作業
			$old = SOYInquiryUtil::switchSOYShopConfig(SOYSHOP_SITE_ID);

			try{
	    		$dao = new SOY2DAO();
	    		$dao->executeUpdateQuery($sql, $binds);
			}catch(Exception $e){
				//
			}

			//パスワードの登録
			SOY2::import("util.SOYShopPluginUtil");
			if(SOYShopPluginUtil::checkIsActive("generate_password")){	//パスワードの自動生成　後ほどパスワードをメールで通知する
				$mailAddress = "";
				foreach($binds as $bind){
					if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $bind)){
						$mailAddress = trim($bind);
					}
				}

				if(strlen($mailAddress)){
					include_once(SOY2::RootDir() . "base/func/common.php");
					SOY2::import("module.plugins.generate_password.util.GeneratePasswordUtil");
					$cnf = GeneratePasswordUtil::getConfig();
					$len = (isset($cnf["password_strlen"]) && is_numeric($cnf["password_strlen"])) ? (int)$cnf["password_strlen"] : 12;
					$pw = soyshop_create_random_string($len);

					$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
					try{
						$user = $userDao->getByMailAddress($mailAddress);
						GeneratePasswordUtil::saveAutoGeneratePassword($user->getMailAddress(), $pw);
						$user->setPassword($user->hashPassword($pw));
						$userDao->update($user);
					}catch(Exception $e){
						//
					}
				}
			}

			SOYInquiryUtil::resetConfig($old);
		}
    }

	/**
	 * @modeにはmail or shopが入る
	 */
	private function _buildQueryAndBinds($columns, $mode="mail"){
		if(!is_array($columns) || !count($columns)) return array("", array());

		$values = array();
		foreach($columns as $column){
			$obj = $column->getColumn($this->form);

			switch($mode){
				case "shop":
					$data = $obj->convertToSOYShop();
					break;
				default:
				case "mail":
					$data = $obj->convertToSOYMail();
			}
			if($data) $values = array_merge($values, $data);
		}
		if(!count($values)) return array("", array());

		/*
		 * 投稿元フォーム情報を追加
		 */
		if(!isset($values[SOYMailConverter::SOYMAIL_ATTR3])) {
			$values[SOYMailConverter::SOYMAIL_ATTR3] = "投稿元フォーム：" . $this->form->getName();
		}

		/*
		 *  SQL文を生成する
		 */
		$keys = array();
		$alias = array();
		$binds = array();
		$cnt = 0;

		//メールアドレスの値があるか？チェック
		$isMailAddress = false;
		foreach($values as $key => $val) {
			if(strlen($key) < 1) continue;

			//文字列に@があるものの場合にメールアドレスとみなす
			if($key === "mail_address" && strpos($val, "@") > 0) $isMailAddress = true;

			$keys[$cnt] = $key;
			$alias[$cnt] = ":datum".$cnt;
			$binds[":datum".$cnt] = $val;

			$cnt++;
		}

		//keysが一つもない場合はここで処理を終了する
		if(!count($keys)) return array("", array());

		//メールアドレスの値がない場合はここで処理を終了する
		if(!$isMailAddress) return array("", array());

		//is_disabled
		$keys[] = "is_disabled";
		$alias[] = "0";

		if($mode == "shop"){
			$keys[] = "user_type";
			$alias[] = 1;
		}

		//register_date, update_date
		$keys[] = "register_date";
		$keys[] = "update_date";
		$alias[] = ":now";
		$alias[] = ":now";
		$binds[":now"] = time();

		//SQLを組み立てる
		$sql  = "insert into soy" . $mode . "_user(" . implode(",", $keys) . ") ";
		$sql .= "values(". implode(",", $alias) . ")";
		return array($sql, $binds);
	}

    /**
     * 問い合わせ番号を生成
     *
     * [3桁以上FormId]-
     */
    function getTrackingNumber($inquiry){
    	for($i = 0;;$i++){
	    	$seed = $inquiry->getId().$inquiry->getCreateDate().$i;
   	 		$hash = base_convert(md5($seed),16,10);
    		if($inquiry->getId() < 100000){
    			$trackingnum = substr($hash,2,4)."-".substr($hash,6,4);
    		}else{
    			$trackingnum = substr($hash,2,4)."-".substr($hash,6,4)."-".substr($hash,10,4);
    		}
   				$trackingnum = $inquiry->getFormId()."-".$trackingnum;
    		try{
	    		$inq = $this->getDAO()->getByTrackingNumber($trackingnum);
    		}catch(Exception $e){
				break;
    		}
    	}
	    return $trackingnum;

    }

	/**
	 * 問い合わせのフラグを一括更新
	 */
	function bulk_update_flag($ids, $flag){
    	switch($flag){
    		case SOYInquiry_Inquiry::FLAG_NEW :
    		case SOYInquiry_Inquiry::FLAG_READ :
    		case SOYInquiry_Inquiry::FLAG_DELETED :
    			break;
    		default:
    			return false;
    	}

    	try{
	    	$dao = $this->getDAO();
	    	$dao->begin();
	    	foreach($ids as $id){
		    	if(!is_numeric($id)) continue;
		    	$dao->updateFlagById((int)$id, $flag);
	    	}
	    	$dao->commit();
	    	return true;
    	}catch(Exception $e){
    		$dao->rollback();
    		return false;
    	}
	}

	/**
	 * 一括削除
	 */
	function bulk_delete($ids){
    	try{
	    	$dao = $this->getDAO();
	    	$dao->begin();
	    	foreach($ids as $id){
		    	if(!is_numeric($id)) continue;
		    	$dao->delete((int)$id);
	    	}
	    	$dao->commit();
	    	return true;
    	}catch(Exception $e){
    		$dao->rollback();
    		return false;
    	}
	}

    /**#@+
     *
     * @access public
     */
    function getForm() {
    	return $this->form;
    }
    function setForm($form) {
    	$this->form = $form;
    }
    /**#@-*/

	/** カートをIPアドレスで制限 **/
	function checkRecentInquiryCount($ipAddress){
		SOY2::import("domain.SOYInquiry_DataSetsDAO");

		//IPアドレス除外リスト
		$excludeListRaw = SOYInquiry_DataSets::get("execlude_ip_address_list", null);
		if(!isset($excludeListRaw)){
			$excludeList = array();
		}else{
			$excludeList = explode(",", $excludeListRaw);
		}

		//除外リストに127.0.0.1を加える localhostは無条件で除外
		$excludeList[] = "127.0.0.1";

		foreach($excludeList as $exclude){
			if($ipAddress == trim($exclude)) return false;
		}

		//指定のIPアドレスで1時間以内にお問い合わせが指定の件数あったか？
		$cnt = SOY2DAOFactory::create("SOYInquiry_BanIpAddressDAO")->countInquiryCountByIpAddressWithinHour($ipAddress, 1);
		return ($cnt >= SOYInquiry_DataSets::get("form_ban_count", 30));
	}

	function banIPAddress($ipAddress){
		$dao = SOY2DAOFactory::create("SOYInquiry_BanIpAddressDAO");
		$banObj = new SOYInquiry_BanIpAddress();
		$banObj->setIpAddress($ipAddress);
		try{
			$dao->insert($banObj);
		}catch(Exception $e){
			//
		}
	}

	function checkBanIpAddress(){
		return SOY2DAOFactory::create("SOYInquiry_BanIpAddressDAO")->checkBanByIpAddressAndUpdate($_SERVER['REMOTE_ADDR']);
	}
}
