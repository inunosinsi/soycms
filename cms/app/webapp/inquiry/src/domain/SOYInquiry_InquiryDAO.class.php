<?php
/**
 * @entity SOYInquiry_Inquiry
 */
abstract class SOYInquiry_InquiryDAO extends SOY2DAO{

    /**
	 * @return id
	 * @trigger onInsert
	 */
    abstract function insert(SOYInquiry_Inquiry $bean);

	/**
	 * @trigger onUpdate
	 */
    abstract function update(SOYInquiry_Inquiry $bean);

    /**
     * 読み込みフラグを設定
     *
     * @final
     */
    function setReaded($id){
    	$this->updateFlagById($id, SOYInquiry_Inquiry::FLAG_READ, time());
    }

    /**
     * @columns id,flag,update_date
     */
    abstract function updateFlagById(int $id, int $flag, int $updateDate);

    abstract function get();

    abstract function getByFormId($formId);

    /**
     * @return object
     */
    abstract function getByTrackingNumber($trackingNumber);

	/**
	 * @query update_date = :updateDate AND flag = :flag
	 * @order by update_date ASC, create_date ASC
	 */
	abstract function getByUpdateDateAndFlag(int $updateDate, int $flag);

    /**
     * 検索を行う
     *
     * @order create_date desc
     */
    function search(string $formId, int $start, int $end, string $trackId, int $flag, $commentFlag = null){
		$query = $this->getQuery();
    	$binds = $this->getBinds();

    	$where = array();

    	if(strlen($formId)){
    		$where[] = "form_id = :formId";
    		$binds[":formId"] = $formId;
		}

		if($start > 0){
			$where[] = "create_date >= :start";
	    	$binds[":start"] = $start;
		}
	
		if($end > 0) {
			$where[] = "create_date <= :end";
	    	$binds[":end"] = $end + 24 * 60 * 60;
		}	
    	
		if(strlen($trackId)){
    		$where[] = "tracking_number = :trackId";
    		$binds[":trackId"] = $trackId;
    	}
    	if($flag >= 0){
    		$where[] = "flag = :flag";
    		$binds[":flag"] = $flag;
    	}else{
    		$where[] = "flag <> :flag";
    		$binds[":flag"] = SOYInquiry_Inquiry::FLAG_DELETED;
    	}

    	if(count($where) > 0) $query->where = implode(" AND ",$where);
    	try{
    		$res = $this->executeQuery($query, $binds);
    	}catch(Exception $e){
    		$res = array();
    	}

    	$commentDao = SOY2DAOFactory::create("SOYInquiry_CommentDAO");

    	$result = array();
    	foreach($res as $row){
    		if(!isset($row["id"]) || !strlen($row["id"]))continue;

    		//コメントの有無の分岐
    		if($commentFlag){
    			try{
    				$comments = $commentDao->getByInquiryId($row["id"]);
    			}catch(Exception $e){
    				$comments = array();
    			}

    			//コメント有り コメントの数が0の場合はスルー
    			if($commentFlag == SOYInquiry_Inquiry::COMMENT_HAS && !count($comments)) continue;

    			//コメント無し コメントがある場合はスルー
    			if($commentFlag == SOYInquiry_Inquiry::COMMENT_NONE && count($comments)) continue;
    		}

    		$obj = $this->getObject($row);
    		$result[$obj->getId()] = $obj;
    	}

    	return $result;
    }

    /**
     * @return object
     */
    abstract function getById($id);

	abstract function getByFlag($flag);

    abstract function delete($id);

    abstract function deleteByFormId($formId);

    /**
     * @return column_cnt
     * @columns count(id) as cnt
     * @query form_id = :formId and flag = 0
     */
    abstract function countUnreadInquiryByFormId($formId);

    /**
     * @return column_cnt
     * @columns count(id) as cnt
     * @query form_id = :formId and flag = :flag
     */
    abstract function countInquiryByFormIdByFlag($formId, $flag);

    /**
     * @return column_cnt
     * @columns count(id) as cnt
     * @query form_id = :formId and flag <> 2
     */
    abstract function countUndeletedInquiryByFormId($formId);

	/**
	 * @final
	 */
	function getSuggestionInquiryIdsForAutomaticPhysicalDeletion(){
		try{
			$res = $this->executeQuery(
				"SELECT id FROM soyinquiry_inquiry ".
				"WHERE flag = ".SOYInquiry_Inquiry::FLAG_DELETED." ".
				"AND update_date < ".strtotime("-".SOYInquiryUtil::SOYINQUIRY_PHYSICAL_DELETE_DAYS."days")
			);
		}catch(Exception $e){
			return array();
		}
		if(!count($res)) return array();

		foreach($res as $v){
			$ids[] = (int)$v["id"];
		}

		return $ids;
	}

	/**
	 * @final
	 */
	function onInsert(SOY2DAO_Query $query, array $binds){
		static $i;
		if(is_null($i)) $i = 0;

		for(;;){
			$i++;
			try{
				$res = $this->executeQuery("SELECT id FROM soyinquiry_inquiry WHERE form_id = :formId AND create_date = :createDate LIMIT 1;", array(":formId" => $binds[":formId"], ":createDate" => $binds[":createDate"] + $i));
			}catch(Exception $e){
				$res = array();
			}

			if(!count($res)) break;
		}
		$binds[":createDate"] += $i;
		$binds[":updateDate"] = 0;

		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function onUpdate(SOY2DAO_Query $query, array $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
