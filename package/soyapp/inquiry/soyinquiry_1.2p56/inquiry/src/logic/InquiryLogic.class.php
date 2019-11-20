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
    		$label = $column->getLabel();

			//連番の場合
			if($column->getType() == "SerialNumber"){
				$config = soy2_unserialize($column->getConfig());
				$value = (isset($config["serialNumber"]) && is_numeric($config["serialNumber"])) ? (int)$config["serialNumber"] : 1;
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
    	$inquiry->setTrackingNumber($this->getTrackingNumber($inquiry));

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

		$this->updateToSOYMail($inquiry,$columns);

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
    function updateToSOYMail($inquiry,$columns){
    	$values = array();

		foreach($columns as $column){
			$obj = $column->getColumn($this->form);

			$soyMailData = $obj->convertToSOYMail();
			if($soyMailData) $values = array_merge($values, $soyMailData);
		}

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
		$data = array();
		$cnt = 0;

		//メールアドレスの値があるか？チェック
		$isMailAddress = false;
		foreach($values as $key => $val) {
			if(strlen($key)<1)continue;

			//文字列に@があるものの場合にメールアドレスとみなす
			if($key==="mail_address" && strpos($val,"@") > 0)$isMailAddress = true;

			$keys[$cnt] = $key;
			$alias[$cnt] = ":datum".$cnt;
			$data[":datum".$cnt] = $val;

			$cnt++;
		}

		//メールアドレスの値がない場合はここで処理を終了する
		if($isMailAddress===false)return;

		//is_disabled
		$keys[] = "is_disabled";
		$alias[] = "0";

		//register_date, update_date
		$keys[] = "register_date";
		$keys[] = "update_date";
		$alias[] = ":now";
		$alias[] = ":now";
		$data[":now"] = time();

		$sql  = "insert into soymail_user(" . implode(",", $keys) . ") ";
		$sql .= "values(". implode(",", $alias) . ")";

		//DSNの書き換え
		if(defined("SOYMAIL_DSN")){

			$old = SOY2DAOConfig::Dsn(SOYMAIL_DSN);

			try{
	    		$dao = new SOY2DAO();
	    		$dao->executeUpdateQuery($sql,$data);
			}catch(Exception $e){

			}

			SOY2DAOConfig::Dsn($old);
		}

		//SOYShopでも同様の同期作業を行う
		$this->updateToSOYShop($keys, $alias, $data);
    }

    /**
     * @ToDo
     * SOYMailとの同期の際に用いたデータを使用してSOYShopと同期する
     */
    function updateToSOYShop($keys, $alias, $data){

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

			$sql  = "insert into soyshop_user(" . implode(",", $keys) . ") ";
			$sql .= "values(". implode(",", $alias) . ")";

			try{
	    		$dao = new SOY2DAO();
	    		$dao->executeUpdateQuery($sql, $data);
			}catch(Exception $e){
				//
			}

			SOYInquiryUtil::resetConfig($old);
		}
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
