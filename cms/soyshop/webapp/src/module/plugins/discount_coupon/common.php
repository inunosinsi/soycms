<?php

SOY2DAOFactory::importEntity("SOYShop_DataSets");

/**
 *
 * discount.coupon.config: SOYShopCouponConfig
 * discount.coupon.list: Array(id => SOYShopCoupon)
 * SOYShopCoupon.couponCodes: Array(code => SOYShopCouponCode)
 * discount.coupon.code.XXX: SOYShopCouponCode
 *
 *
 */

class SOYShopCouponUtil{

	/**
	 * 設定を取得する
	 * @return SOYShopCouponConfig
	 */
	public static function getConfig(){

		return SOYShop_DataSets::get("discount.coupon.config", new SOYShopCouponConfig());

	}

	/**
	 * 設定を保存する
	 * @param postedArray array
	 * @return Boolean
	 */
	public static function saveConfig($postedArray){

    	$config = SOYShopCouponUtil::getConfig();
		$obj = (object)$postedArray;
		SOY2::cast($config,$obj);

		try{
			SOYShop_DataSets::put("discount.coupon.config", $config);
			return true;
		}catch(Exception $e){
			return false;
		}

	}

	/**
	 * 全クーポンを取得する
	 * @return array <SOYShopCoupon>
	 */
	public static function getCouponList(){
		return SOYShop_DataSets::get("discount.coupon.list", array());
	}

	/**
	 * 検索条件に合ったクーポンを取得する
	 * @return array <SOYShopCoupon>
	 */
	public static function searchCouponList($searchArray){
		$list = SOYShop_DataSets::get("discount.coupon.list", array());
		foreach($list as $id => $coupon){
			if(isset($searchArray["title"]) && strlen($searchArray["title"]) && strpos($coupon->getTitle(),$searchArray["title"]) === false){
				unset($list[$id]);
				continue;
			}
			if(isset($searchArray["memo"]) && strlen($searchArray["memo"]) && strpos($coupon->getMemo(),$searchArray["memo"]) === false){
				unset($list[$id]);
				continue;
			}
			if(isset($searchArray["expire_start"]) && strlen($searchArray["expire_start"]) && $coupon->getExpirationDatetime() < strtotime($searchArray["expire_start"])){
				unset($list[$id]);
				continue;
			}
			if(isset($searchArray["expire_end"]) && strlen($searchArray["expire_end"]) && $coupon->getExpirationDatetime() > strtotime($searchArray["expire_end"])){
				unset($list[$id]);
				continue;
			}
		}

		return $list;
	}

	/**
	 * クーポンをコードで取得
	 * @return Array <SOYShopCoupon, SOYShopCouponCode>
	 */
	public static function getCouponByCode($code){
		$code = trim($code);
		$couponCode = SOYShop_DataSets::get("discount.coupon.code." . $code, null);
		if($couponCode){
			$coupon = self::getCoupon($couponCode->getCouponId());
			return array($coupon,$couponCode);
		}else{
			return false;
		}
	}

	/**
	 * クーポンを使う
	 */
	public static function useCoupons($codes,$orderId,$userId){
		foreach($codes as $code){
			self::useCoupon($code,$orderId,$userId);
		}
	}
	public static function useCoupon($code,$orderId,$userId){
		list($coupon,$couponCode) = self::getCouponByCode($code);
		if($coupon){
			$couponCodes = $coupon->getCouponCodes();
			if(!isset($couponCodes["$code"])){
				return false;
			}else{
				$couponCode = $couponCodes[$code];//updateのためにこっちのオブジェクトを使う
				$couponCode->setStatus(SOYShopCouponCode::STATUS_USED);
				if($orderId) $couponCode->setOrderId($orderId);
				if($userId) $couponCode->setUserId($userId);
			}
			self::update($coupon->getId(), $coupon);
			SOYShop_DataSets::put("discount.coupon.code." . $code, $couponCode);
		}
	}

	/**
	 * クーポンコードが利用可能か
	 * @return Boolean
	 */
	public static function checkCode($code){
		list($coupon,$couponCode) = self::getCouponByCode($code);
		if(!$coupon){
			return false;
		}else{
			return (
				$couponCode->getStatus() == SOYShopCouponCode::STATUS_PUBLISHED
				AND
				time() <= $coupon->getExpirationDatetime()
			);
		}
	}

	/**
	 * クーポンコードによる割引金額を取得
	 */
	public static function getDiscount($code){
		if(is_array($code)){
			$total = 0;
			foreach($code as $item){
				$total += self::getDiscount($item);
			}
			return $total;
		}else{
			if(self::checkCode($code)){
				list($coupon,$couponCode) = self::getCouponByCode($code);
				return $coupon->getValue();
			}else{
				return 0;
			}
		}
	}

	/**
	 * クーポンを取得
	 * @param id Integer
	 * @return SOYShopCoupon
	 */
	public static function getCoupon($id){
		$coupons = self::getCouponList();
		if(isset($coupons[$id])){
			return $coupons[$id];
		}else{
			return new SOYShopCoupon();
		}
	}

	/**
	 * クーポンを削除
	 */
	public static function delete($id){
		$list = self::getCouponList();
		if(isset($list[$id])){
			$coupon = $list[$id];

			$codes = $coupon->getCouponCodes();
			foreach($codes as $key => $value){
				SOYShop_DataSets::put("discount.coupon.code." . $key, null);
			}

			unset($list[$id]);
			SOYShop_DataSets::put("discount.coupon.list", $list);
		}
	}

	/**
	 * クーポンを新規作成
	 * @param postedArray array
	 * @return Integer id
	 */
	public static function issue($postedArray){
		$obj = (object)$postedArray;
		$coupon = new SOYShopCoupon();
		SOY2::cast($coupon,$obj);

		$coupons = self::getCouponList();
		if(count($coupons)){
			$couponIds = array_keys($coupons);
			$newCouponId = max($couponIds) +1;
		}else{
			$newCouponId = 1;
		}
		$coupon->setId($newCouponId);
		$coupon->setIssuedDatetime(time());

		try{
			$couponCodes = self::issueCodes($newCouponId, $coupon->getNumber());
			if($couponCodes){
				$coupon->setCouponCodes($couponCodes);
			}

			$coupons[$newCouponId] = $coupon;
			SOYShop_DataSets::put("discount.coupon.list", $coupons);
			return $newCouponId;
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * クーポンコードを発行
	 * @param Integer couponId クーポンID
	 * @param Integer number   発行数
	 * @return Array <code => SOYShopCouponCode>
	 */
	public static function issueCodes($couponId, $number){
		$codes = self::generateCodes($number);
		if(!$codes) return false;

		$generated = array();
		try{
			$dao = SOY2DAOFactory::create("config.SOYShop_DataSetsDAO");
			$dao->begin();
			foreach($codes as $code){
				$couponCode = new SOYShopCouponCode();
				$couponCode->setCouponId($couponId);
				$couponCode->setCode($code);
				SOYShop_DataSets::put("discount.coupon.code." . $code, $couponCode);
				$generated[$code] = $couponCode;
			}
			$dao->commit();
		}catch(Exception $e){
			$dao->rollback();
		}
		return $generated;
	}

	/**
	 * クーポンコードを生成
	 * @param Integer number 生成数
	 * @return Array <String>
	 */
	public static function generateCodes($number){
		$codes = array();
		$i = 0;
		while($i<100){
			$code = strtoupper( substr( str_replace( array('+', '/'), '', base64_encode( hash('md5',mt_rand(),true) ) ), 0, 10) );
			try{
				SOYShop_DataSets::get("discount.coupon.code." . $code);
				$i++;
			}catch(Exception $e){
				$codes[] = $code;
			}

			if(count($codes)>=$number){
				return $codes;
			}
		}
		return false;
	}

	/**
	 * クーポンを更新
	 */
	public static function update($id,$obj){
		if(is_array($obj)) $obj = (object)$obj;
		$coupon = self::getCoupon($id);
		SOY2::cast($coupon,$obj);

		$coupons = self::getCouponList();
		$coupons[$id] = $coupon;

		try{
			SOYShop_DataSets::put("discount.coupon.list", $coupons);
			return true;
		}catch(Exception $e){
			return false;
		}

	}

	/**
	 * クーポンのCSV出力
	 */
	public static function exportList($list){
    	set_time_limit(0);

    	$logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");

		$logic->setSeparator(",");
		$logic->setQuote(true);
		$logic->setCharset("Shift_JIS");

		//出力する項目にセット
		$logic->setItems(array(
			"id" => 1,
			"number" => 1,
			"value" => 1,
			"title" => 1,
			"memo" => 1,
			"expirationDate" => 1,
			"issuedDate" => 1,
		));
		$logic->setLabels(array(
			"id" => "No.",
			"number" => "枚数",
			"value" => "金額",
			"title" => "件名",
			"memo" => "備考",
			"expirationDate" => "有効期限",
			"issuedDate" => "発行日時",
		));


		//CSV(TSV)に変換
		$lines = array();
    	foreach($list as $item){
    		$lines[] = $logic->export($item);
    	}

    	//ラベル：改行付き
    	$label = $logic->getHeader() . "\r\n";

		//ファイル出力
    	header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=soyshop_coupon.csv");
		header("Content-Type: text/csv; charset=".htmlspecialchars($logic->getCharset()).";");

    	echo $label;
    	echo implode("\r\n", $lines);

    	exit;
	}

	/**
	 * クーポンコードのCSV出力
	 */
	public static function exportCode($list){
    	set_time_limit(0);

    	$logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");

		$logic->setSeparator(",");
		$logic->setQuote(true);
		$logic->setCharset("Shift_JIS");

		//出力する項目にセット
		$logic->setItems(array(
			"couponId" => 1,
			"code" => 1,
			"statusText" => 1,
			"orderId" => 1,
			"userId" => 1,
		));
		$logic->setLabels(array(
			"couponId" => "クーポンNo.",
			"code" => "コード",
			"statusText" => "ステータス",
			"orderId" => "注文ID",
			"userId" => "顧客ID",
		));


		//CSV(TSV)に変換
		$lines = array();
    	foreach($list as $item){
    		$lines[] = $logic->export($item);
    	}

    	//ラベル：改行付き
    	$label = $logic->getHeader() . "\r\n";

		//ファイル出力
    	header("Cache-Control: no-cache");
		header("Pragma: no-cache");
    	header("Content-Disposition: attachment; filename=soyshop_coupon_code-".date("Ymd").".csv");
		header("Content-Type: text/csv; charset=".htmlspecialchars($logic->getCharset()).";");

    	echo $label;
    	echo implode("\r\n", $lines);

    	exit;
	}

	/**
	 * 配列から重複と空を削除する
	 */
	public static function clean($array){
		return array_merge(array_unique(array_diff($array, array(""))), array());
	}

}



