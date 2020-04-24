<?php
/**
 * @table soyshop_order_state_history
 */
class SOYShop_OrderStateHistory {

	/**
	 * @id
	 */
    private $id;

    /**
     * @column order_id
     */
    private $orderId;

    private $author;

    private $content;

    private $more;

    /**
     * @column order_date
     */
    private $date;

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }

    function getOrderId() {
    	return $this->orderId;
    }
    function setOrderId($orderId) {
    	$this->orderId = $orderId;
    }

    function getAuthor() {
    	return $this->author;
    }
    function setAuthor($author) {
    	$this->author = $author;
    }

    function getContent() {
    	return $this->content;
    }
    function setContent($content) {
    	$this->content = $content;
    }

	function getMore() {
    	return $this->more;
    }
    function setMore($more) {
    	$this->more = $more;
    }

    function getDate() {
    	if(!$this->date) return time();
    	return $this->date;
    }
    function setDate($date) {
    	$this->date = $date;
    }
}
