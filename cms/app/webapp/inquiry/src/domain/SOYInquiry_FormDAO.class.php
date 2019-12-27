<?php
/**
 * @entity SOYInquiry_Form
 */
abstract class SOYInquiry_FormDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYInquiry_Form $bean);

    abstract function update(SOYInquiry_Form $bean);

    /**
     * @index id
     */
    abstract function get();

    /**
     * @return object
     */
    abstract function getByFormId($formId);

    /**
     * @return object
     */
    abstract function getById($id);

    abstract function delete($id);
}
