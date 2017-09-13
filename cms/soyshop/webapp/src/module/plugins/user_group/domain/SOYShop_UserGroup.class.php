<?php

/**
 * @table soyshop_user_group
 */
class SOYShop_UserGroup{

	const NO_DISABLED = 0;
    const IS_DISABLED = 1;

	/**
	 * @id
	 */
	private $id;
	private $name;

	/**
     * @column group_order
     */
    private $order = 0;

	/**
     * @column is_disabled
     */
    private $isDisabled = 0;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}

	function getOrder() {
        return $this->order;
    }
    function setOrder($order) {
        $this->order = $order;
    }

	function getIsDisabled(){
		return $this->isDisabled;
    }
    function setIsDisabled($isDisabled){
		$this->isDisabled = $isDisabled;
    }
}
