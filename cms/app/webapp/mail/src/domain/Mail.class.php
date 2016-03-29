<?php
/**
 * @table soymail_mail
 */
class Mail {

	/**
	 * @id
	 */
	private $id;

	private $title;

	/**
	 * @column sender_address
	 */
	private $senderAddress;

	/**
	 * @column sender_name
	 */
	private $senderName;

	/**
	 * @column return_address
	 */
	private $returnAddress;

	/**
	 * @column return_name
	 */
	private $returnName;

	/**
	 * @column mail_content
	 */
	private $mailContent = "";

	private $selector;

	private $configure;

	/**
	 * @column mail_count
	 */
	private $mailCount;

	private $schedule;

	private $status = 100;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	/**
	 * @column send_date
	 */
	private $sendDate;

	/**
	 * @column sended_date
	 */
	private $sendedDate;

	const STATUS_ERROR = 0;
	const STATUS_DRAFT = 100;	//下書き
	const STATUS_WAIT = 200;	//送信待ち
	const STATUS_SENDING = 300;	//送信中
	const STATUS_HISTORY = 400;	//送信完了

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getTitle() {
		return $this->title;
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getSenderAddress() {
		return $this->senderAddress;
	}
	function setSenderAddress($senderAddress) {
		$this->senderAddress = $senderAddress;
	}
	function getSenderName() {
		return $this->senderName;
	}
	function setSenderName($senderName) {
		$this->senderName = $senderName;
	}
	function getReturnAddress() {
		return $this->returnAddress;
	}
	function setReturnAddress($returnAddress) {
		$this->returnAddress = $returnAddress;
	}
	function getReturnName() {
		return $this->returnName;
	}
	function setReturnName($returnName) {
		$this->returnName = $returnName;
	}
	function getMailContent() {
		return $this->mailContent;
	}
	function setMailContent($mailContent) {
		$this->mailContent = $mailContent;
	}
	function getSelector() {
		return $this->selector;
	}
	function setSelector($selector) {
		$this->selector = $selector;
	}
	function getSelectorObject() {
		return (strlen($this->selector)) ? unserialize($this->selector) : new MailSelector();
	}
	function setSelectorObject($selector) {
		$this->selector = serialize($selector);
	}
	function getConfigure() {
		return $this->configure;
	}
	function setConfigure($configure) {
		$this->configure = $configure;
	}
	function getConfigureObject() {
		return (strlen($this->configure)) ? unserialize($this->configure) : new MailConfig();
	}
	function setConfigureObject($configure) {
		$this->configure = serialize($configure);
	}
	function getMailCount() {
		return (int)$this->mailCount;
	}
	function setMailCount($mailCount) {
		$this->mailCount = $mailCount;
	}
	function getSchedule() {
		return $this->schedule;
	}
	function setSchedule($schedule) {
		if(strtotime($schedule)){
			$schedule = strtotime($schedule);
		}
		$this->schedule = $schedule;
	}
	function getScheduleString(){
		if($this->schedule){
			return date("Y-m-d H:i:s",$this->schedule);
		}else{
			return "";
		}
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getStatusText(){
		switch($this->status){
			case Mail::STATUS_DRAFT;
				return "下書き";
			case Mail::STATUS_WAIT:
				return "送信待ち";
			case Mail::STATUS_SENDING:
				return "送信中";
			case Mail::STATUS_HISTORY:
				return "送信完了";
			case Mail::STATUS_ERROR:
				return "送信エラー";
		}
	}
	function getCreateDate() {
		return $this->createDate;
	}
	function setCreateDate($createDate) {
		$this->createDate = $createDate;
	}
	function getUpdateDate() {
		return $this->updateDate;
	}
	function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;
	}
	function getSendDate() {
		return $this->sendDate;
	}
	function setSendDate($sendDate) {
		$this->sendDate = $sendDate;
	}
	function getSendedDate() {
		return $this->sendedDate;
	}
	function setSendedDate($sendedDate) {
		$this->sendedDate = $sendedDate;
	}
}

class MailSelector{
	private $currentOffset = 0;
	private $gender = array(
		"male" => 1,
		"female" => 1,
		"other" => 1
	);

	private $areas = array();

	private $attributes = array("","","","");

	private $carrier = array(
		"pc" => true,
		"docomo" => true,
		"au" => true,
		"softbank" => true,
		"willcom" => true,
		"other" => ""
	);

	private $memo;

	private $age;

	private $month;

	function getGender() {
		return $this->gender;
	}
	function setGender($gender) {
		$this->gender = $gender;
	}
	function getAreas() {
		return $this->areas;
	}
	function setAreas($areas) {
		$this->areas = $areas;
	}
	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = $attributes;
	}
	function getCarrier() {
		return $this->carrier;
	}
	function setCarrier($carrier) {
		$this->carrier = $carrier;
	}

	private static $cellphone_domain = array(
		"docomo" => array(
			"docomo.ne.jp"
		),
		"au" => array(
			"ezweb.ne.jp"
		),
		"softbank" => array(
			"softbank.ne.jp",
			"d.vodafone.ne.jp",
			"h.vodafone.ne.jp",
			"t.vodafone.ne.jp",
			"c.vodafone.ne.jp",
			"k.vodafone.ne.jp",
			"r.vodafone.ne.jp",
			"n.vodafone.ne.jp",
			"s.vodafone.ne.jp",
			"q.vodafone.ne.jp",
			"jp-d.ne.jp",
			"jp-h.ne.jp",
			"jp-t.ne.jp",
			"jp-c.ne.jp",
			"jp-k.ne.jp",
			"jp-r.ne.jp",
			"jp-n.ne.jp",
			"jp-s.ne.jp",
			"jp-q.ne.jp",
		),
		"willcom" => array(
			"pdx.ne.jp",
			"wm.pdx.ne.jp",
			"di.pdx.ne.jp",
			"dj.pdx.ne.jp",
			"dk.pdx.ne.jp",
			"willcom.com"
		),
	);

	/**
	 * @param $includeNotSendUser default:false
	 */
	function generateConditions($onlyNotSendUser = false){
		$binds = array();
		$where_array = array();

		//GENDER
		$gender = $this->getGender();
		if( array_key_exists("male", $gender)
		   AND array_key_exists("female", $gender)
		   AND array_key_exists("other", $gender)
		){
		   	//do nothing
		}else{
			$where_gender = array();
			if( array_key_exists("male", $gender) ){
				$where_gender[] = "gender = 0";
			}
			if( array_key_exists("female", $gender) ){
				$where_gender[] = "gender = 1";
			}
			if( array_key_exists("other", $gender) ){
				$where_gender[] = "gender = ''";
			}
			$where_array[] = (count($where_gender)) ? "(".implode(" OR ", $where_gender).")" : "" ;
		}

		//AGE
		$age = $this->getAge();
		if($age > 0){
			$where_array[] = "birthday > :age0 and birthday <= :age1";
			$now = time();
			$decade = 60 * 60 * 24 * 365;
			$binds[":age0"] = $now - ($age+10) * $decade;
			$binds[":age1"] = $now - $age * $decade;
		}

		//AREA
		$areas = $this->getAreas();
		if( in_array("all", $areas) ){
			//nothing
		}else{
			$where_array[] = "area in (".implode(",", $areas).")";
		}

		//ATTRIBUTES
		$attributes = $this->getAttributes();
		for($i=1; $i<=3; $i++){
			if( strlen($attributes[$i]) ){
				$where_array[] = "attribute$i like :attribute".$i;
				$binds[":attribute".$i] = $attributes[$i];
			}
		}

		//carrier
		$carrier = $this->getCarrier();
		if( array_key_exists("pc", $carrier)
		   AND array_key_exists("docomo", $carrier)
		   AND array_key_exists("au", $carrier)
		   AND array_key_exists("softbank", $carrier)
		   AND array_key_exists("willcom", $carrier)
		){
			//do nothing
		}else{
			$where_carrier = array();

			if( array_key_exists("pc", $carrier) ){
				$__pc = array();
				for($i=0; $i<count(self::$cellphone_domain["docomo"]); $i++){
					$__pc[] = "mail_address NOT like :domain_pc_docomo".$i;
					$binds[":domain_pc_docomo".$i] = "%".self::$cellphone_domain["docomo"][$i];
				}
				for($i=0; $i<count(self::$cellphone_domain["au"]); $i++){
					$__pc[] = "mail_address NOT like :domain_pc_au".$i;
					$binds[":domain_pc_au".$i] = "%".self::$cellphone_domain["au"][$i];
				}
				for($i=0; $i<count(self::$cellphone_domain["softbank"]); $i++){
					$__pc[] = "mail_address NOT like :domain_pc_softbank".$i;
					$binds[":domain_pc_softbank".$i] = "%".self::$cellphone_domain["softbank"][$i];
				}
				for($i=0; $i<count(self::$cellphone_domain["willcom"]); $i++){
					$__pc[] = "mail_address NOT like :domain_pc_willcom".$i;
					$binds[":domain_pc_willcom".$i] = "%".self::$cellphone_domain["willcom"][$i];
				}
				$where_carrier[] = implode(" AND ", $__pc);
			}

			if( array_key_exists("docomo", $carrier) ){
				$__docomo = array();
				for($i=0; $i<count(self::$cellphone_domain["docomo"]); $i++){
					$__docomo[] = "mail_address like :domain_docomo".$i;
					$binds[":domain_docomo".$i] = "%".self::$cellphone_domain["docomo"][$i];
				}
				$where_carrier[] = implode(" OR ", $__docomo);
			}

			if( array_key_exists("au", $carrier) ){
				$__au = array();
				for($i=0; $i<count(self::$cellphone_domain["au"]); $i++){
					$__au[] = "mail_address like :domain_au".$i;
					$binds[":domain_au".$i] = "%".self::$cellphone_domain["au"][$i];
				}
				$where_carrier[] = implode(" OR ", $__au);
			}

			if( array_key_exists("softbank", $carrier) ){
				$__softbank = array();
				for($i=0; $i<count(self::$cellphone_domain["softbank"]); $i++){
					$__softbank[] = "mail_address like :domain_softbank".$i;
					$binds[":domain_softbank".$i] = "%".self::$cellphone_domain["softbank"][$i];
				}
				$where_carrier[] = implode(" OR ", $__softbank);
			}

			if( array_key_exists("willcom", $carrier) ){
				$__willcom = array();
				for($i=0; $i<count(self::$cellphone_domain["willcom"]); $i++){
					$__willcom[] = "mail_address like :domain_willcom".$i;
					$binds[":domain_willcom".$i] = "%".self::$cellphone_domain["willcom"][$i];
				}
				$where_carrier[] = implode(" OR ", $__willcom);
			}

			if( array_key_exists("other", $carrier) AND strlen($carrier["other"]) ){
				$where_carrier[] = "mail_address like :domain_other";
				$binds[":domain_other"] = "%".$carrier["other"];
			}

			$where_array[] = (count($where_carrier)) ? "(".implode(" OR ", $where_carrier).")" : "" ;

		}

		//memo
		$memo = $this->getMemo();
		if(strlen($memo)>0){
			$memo = str_replace("　"," ",$memo);
			$memos = explode(" ",$memo);
			$tmp = array();
			foreach($memos as $key => $value){
				$tmp[] = "memo LIKE :memo$key";
				$binds[":memo$key"] = "%" . $value . "%";
			}

			$where_array[] = implode(" AND ",$tmp);
		}

		if($onlyNotSendUser){
			$where_array[] = "not_send = 1";
		}else{
			$where_array[] = "not_send != 1";
		}

		//削除ユーザにはメールを送信しない
		$where_array[] = "is_disabled != 1";

		$where = implode(" AND ", $where_array);

		return array($where, $binds);
	}

	/**
	 * 送信先をすべて取得
	 * @return Array(SOYMailUser)
	 */
	function searchSendTo($onlyNotSendUser = false){

		list($where, $binds) = $this->generateConditions($onlyNotSendUser);

		$extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		$query = $extendLogic->getDAO();

		$checkSOYShop = $extendLogic->checkSOYShopConnect();
		$tableName = ($checkSOYShop===true) ? "soyshop_user" : "soymail_user";

		$sql = "select * from " . $tableName . " where ".$where;

		if($checkSOYShop===true)$old = SOYMailUtil::switchSOYShopConfig();

		$result = $query->executeQuery($sql, $binds);

		//オブジェクトの配列に変換
		$mailUsers = array();
		foreach($result as $raw){
			$mailUsers[] = $query->getObject($raw);
		}

		if($checkSOYShop===true)$old = SOYMailUtil::resetConfig($old);

		return $mailUsers;
	}

	/**
	 * 送信しない送信先を取得
	 * @return Array(SOYMailUser)
	 */
	function searchNotSendTo(){
		return $this->searchSendTo(true);
	}

	/**
	 * 次の送信先を取得
	 * @return SOYMailUser
	 */
	function getNextSendAddress($offset=0){

		list($where, $binds) = $this->generateConditions();

		$extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		$query = $extendLogic->getDAO();
		$query->setLimit(1);

		//一応、送信名簿の途中から取得を開始することが出来るようにした
		$current = $offset + $this->currentOffset;
		$query->setOffset($current);
		$this->currentOffset++;

		$checkSOYShop = $extendLogic->checkSOYShopConnect();
		$tableName = ($checkSOYShop===true) ? "soyshop_user" : "soymail_user";

		$sql = "select * from " . $tableName;
		if(strlen($where)>0)$sql .= " where ".$where;

		if($checkSOYShop===true)$old = SOYMailUtil::switchSOYShopConfig();

		$result = $query->executeQuery($sql, $binds);

		if($checkSOYShop===true)$old = SOYMailUtil::resetConfig($old);

		if(empty($result))return false;

		return $query->getObject($result[0]);
	}
	function resetOffset(){
		$this->currentOffset = 0;
	}

	/**
	 * 送信先の件数を取得
	 * @return number 送信先の件数
	 */
	function countAddress(){
		list($where, $binds) = $this->generateConditions();

		$extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		$checkSOYShop = $extendLogic->checkSOYShopConnect();
		$tableName = ($checkSOYShop===true) ? "soyshop_user" : "soymail_user";

		$query = new SOY2DAO();
		$sql = "select count(*) as count from " . $tableName;
		if(strlen($where)>0)$sql .= " where ".$where;

		if($checkSOYShop===true)$old = SOYMailUtil::switchSOYShopConfig();

		$result = $query->executeQuery($sql, $binds);

		if($checkSOYShop===true)$old = SOYMailUtil::resetConfig($old);

		return $result[0]["count"];
	}

	function getCurrentOffset() {
		return $this->currentOffset;
	}
	function setCurrentOffset($currentOffset) {
		$this->currentOffset = $currentOffset;
	}
	function getMemo() {
		return $this->memo;
	}
	function setMemo($memo) {
		$this->memo = $memo;
	}

	function getAge() {
		return $this->age;
	}
	function setAge($age) {
		$this->age = $age;
	}

	function getAges(){
		return array(
			"0" => "～10代",
			"10" => "10代",
			"20" => "20代",
			"30" => "30代",
			"40" => "40代",
			"50" => "50代",
			"60" => "60代",
			"70" => "70代",
			"80" => "80代～"

		);
	}
}

class MailConfig{

	private $speedAdjustment;

	private $isHTMLMail = false;


	function getSpeedAdjustment() {
		return $this->speedAdjustment;
	}
	function setSpeedAdjustment($speedAdjustment) {
		$this->speedAdjustment = $speedAdjustment;
	}
	function getIsHTMLMail() {
		return $this->isHTMLMail;
	}
	function setIsHTMLMail($isHTMLMail) {
		$this->isHTMLMail = $isHTMLMail;
	}
}
?>