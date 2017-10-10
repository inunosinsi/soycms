<?php

/**
 * @table soyshop_user_group
 */
class SOYShop_UserGroup{

	const NO_DISABLED = 0;
    const IS_DISABLED = 1;

	const DISPLAY_ORDER_MAX = 2147483647;
	const DISPLAY_ORDER_MIN = 0;

	/**
	 * @id
	 */
	private $id;
	private $name;
	private $code;

	private $lat;
	private $lng;

	/**
     * @column group_order
     */
    private $order = 2147483647;

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

	function getCode(){
		return $this->code;
	}
	function setCode($code){
		$this->code = $code;
	}

	function getLat(){
		return $this->lat;
	}
	function setLat($lat){
		$this->lat = $lat;
	}

	function getLng(){
		return $this->lng;
	}
	function setLng($lng){
		$this->lng = $lng;
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
