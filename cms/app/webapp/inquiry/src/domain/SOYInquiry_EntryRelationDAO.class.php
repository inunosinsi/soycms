<?php
/**
 * @entity SOYInquiry_EntryRelation
 */
abstract class SOYInquiry_EntryRelationDAO extends SOY2DAO {

	abstract function insert(SOYInquiry_EntryRelation $bean);

	/**
	 * @return object
	 */
	abstract function getByInquiryId($inquiryId);
}
