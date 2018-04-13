<?php

class Trackingmore{

    const API_BASE_URL             = 'http://api.trackingmore.com/v2/';
    const ROUTE_CARRIERS           = 'carriers/';
	const ROUTE_CARRIERS_DETECT    = 'carriers/detect';
    const ROUTE_TRACKINGS          = 'trackings';
	const ROUTE_LIST_ALL_TRACKINGS = 'trackings/get';
	const ROUTE_CREATE_TRACKING    = 'trackings/post';
    const ROUTE_TRACKINGS_BATCH    = 'trackings/batch';
	const ROUTE_TRACKINGS_REALTIME = 'trackings/realtime';
    protected $apiKey              = '69df89f5-f760-4c24-b90b-e7cd5d76bf65';

	function __construct($hoge){
		$this->apiKey = $hoge;
	}


    protected function _getApiData($route, $method = 'GET', $sendData = array()){
		$method     = strtoupper($method);
        $requestUrl = self::API_BASE_URL.$route;
        $curlObj    = curl_init();
        curl_setopt($curlObj, CURLOPT_URL,$requestUrl);
		if($method == 'GET'){
            curl_setopt($curlObj, CURLOPT_HTTPGET,true);
        }elseif($method == 'POST'){
            curl_setopt($curlObj, CURLOPT_POST, true);
        }elseif ($method == 'PUT'){
            curl_setopt($curlObj, CURLOPT_PUT, true);
        }else{
			curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, $method);
		}

        curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlObj, CURLOPT_TIMEOUT, 90);

        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        $headers = array(
            'Trackingmore-Api-Key: ' . $this->apiKey,
            'Content-Type: application/json',
        );
        if($sendData){
            $dataString = json_encode($sendData);
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $dataString);
            $headers[] = 'Content-Length: ' . strlen($dataString);
        }
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curlObj);
        curl_close($curlObj);
        unset($curlObj);
        return $response;
    }



    // List all carriers
    public function getCarrierList(){
        $returnData = array();
        $requestUrl = self::ROUTE_CARRIERS;
        $result = $this->_getApiData($requestUrl, 'GET');
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

	/*Detect a carrier by tracking code
	* @param string $trackingNumber  Tracking number
    * @return array
	*/
	public function detectCarrier($trackingNumber)
    {
        $returnData = array();
        $requestUrl = self::ROUTE_CARRIERS_DETECT;
		$sendData['tracking_number'] = $trackingNumber;
        $result = $this->_getApiData($requestUrl, 'POST',$sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

    /**
	* List all trackings
	* @access public
	* @param int $page  Page to display (optional)
	* @param int $limit Items per page (optional)
	* @param int $createdAtMin Start date and time of trackings created (optional)
	* @param int $createdAtMax
	* @return array
	*/
	public function getTrackingsList($page = 1,$limit = 100,$createdAtMin = 0,$createdAtMax = 0){
        $returnData = array();
		$sendData   = array();
        $requestUrl = self::ROUTE_LIST_ALL_TRACKINGS;
		$createdAtMax = !empty($createdAtMax)?$createdAtMax:time();
		$sendData['page']           = $page;
		$sendData['limit']          = $limit;
		$sendData['created_at_min'] = $createdAtMin;
		$sendData['created_at_max'] = $createdAtMax;
        $result = $this->_getApiData($requestUrl, 'GET', $sendData);
        if ($result) {
            $returnData = $result;
        }
        return $returnData;
    }

	/**
	* Create a tracking item
	* @access public
	* @param string $trackingNumber  Tracking number
	* @param string $carrierCode Carrier code
	* @param array $extraInfo Title,Customer name,email,order ID (optional)
	* @return array
	*/
	public function createTracking($carrierCode,$trackingNumber,$extraInfo = array()){
        $returnData = array();
		$sendData   = array();
        $requestUrl = self::ROUTE_CREATE_TRACKING;

		$sendData['tracking_number'] = $trackingNumber;
		$sendData['carrier_code']    = $carrierCode;
		$sendData['title']           = !empty($extraInfo['title'])?$extraInfo['title']:null;
		$sendData['customer_name']   = !empty($extraInfo['customer_name'])?$extraInfo['customer_name']:null;
		$sendData['customer_email']  = !empty($extraInfo['customer_email'])?$extraInfo['customer_email']:null;
		$sendData['order_id']        = !empty($extraInfo['order_id'])?$extraInfo['order_id']:null;

        $result = $this->_getApiData($requestUrl, 'POST', $sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

	/**
	* Create multiple trackings.
	* @access public
	* @param  array $multipleData Multiple tracking number,carrier code,title,customer name,customer email,order id
	* @return array
	*/
	public function createMultipleTracking($multipleData){
        $returnData = array();
		$sendData   = array();
        $requestUrl = self::ROUTE_TRACKINGS_BATCH;
		if(!empty($multipleData)){
			foreach($multipleData as $val){
				$items                    = array();
			    $items['tracking_number'] = !empty($val['tracking_number'])?$val['tracking_number']:null;
				$items['carrier_code']    = !empty($val['carrier_code'])?$val['carrier_code']:null;
				$items['title']           = !empty($val['title'])?$val['title']:null;
				$items['customer_name']   = !empty($val['customer_name'])?$val['customer_name']:null;
				$items['customer_email']  = !empty($val['customer_email'])?$val['customer_email']:null;
				$items['order_id']        = !empty($val['order_id'])?$val['order_id']:null;
                $sendData[]               = $items;
			}
		}

        $result = $this->_getApiData($requestUrl, 'POST', $sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }


	/**
	* Get tracking results of a single tracking
	* @access public
	* @param string $trackingNumber  Tracking number
	* @param string $carrierCode Carrier code
	* @return array
	*/
	public function getSingleTrackingResult($carrierCode,$trackingNumber){
        $returnData = array();
        $requestUrl = self::ROUTE_TRACKINGS.'/'.$carrierCode.'/'.$trackingNumber;
        $result = $this->_getApiData($requestUrl, 'GET');
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

	/**
	* Update Tracking item
	* @access public
	* @param string $trackingNumber  Tracking number
	* @param string $carrierCode Carrier code
	* @param array $extraInfo Title,Customer name,email,order ID (optional)
	* @return array
	*/
	public function updateTrackingItem($carrierCode,$trackingNumber,$extraInfo){
        $returnData = array();
        $requestUrl = self::ROUTE_TRACKINGS.'/'.$carrierCode.'/'.$trackingNumber;
		$sendData['title']           = !empty($extraInfo['title'])?$extraInfo['title']:null;
		$sendData['customer_name']   = !empty($extraInfo['customer_name'])?$extraInfo['customer_name']:null;
		$sendData['customer_email']  = !empty($extraInfo['customer_email'])?$extraInfo['customer_email']:null;
		$sendData['order_id']        = !empty($extraInfo['order_id'])?$extraInfo['order_id']:null;
        $result = $this->_getApiData($requestUrl, 'PUT',$sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

    /**
	* Delete a tracking item
	* @access public
	* @param string $trackingNumber  Tracking number
	* @param string $carrierCode Carrier code
	* @return array
	*/
	public function deleteTrackingItem($carrierCode,$trackingNumber){
        $returnData = array();
        $requestUrl = self::ROUTE_TRACKINGS.'/'.$carrierCode.'/'.$trackingNumber;
        $result = $this->_getApiData($requestUrl, 'DELETE');
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

	/**
	* Get realtime tracking results of a single tracking
	* @access public
	* @param string $trackingNumber  Tracking number
	* @param string $carrierCode Carrier code
	* @param array  $extraInfo Destination_code,Tracking_ship_date Customer_email,Tracking_postal_code,SpecialNumberDestination,order,lang (optional)
	* @return array
	*/
	public function getRealtimeTrackingResults($carrierCode,$trackingNumber,$extraInfo=array()){
        $returnData = array();
        $requestUrl = self::ROUTE_TRACKINGS_REALTIME;
		$sendData['tracking_number'] = $trackingNumber;
		$sendData['carrier_code']    = $carrierCode;
		$sendData['destination_code']           = !empty($extraInfo['destination_code'])?$extraInfo['destination_code']:null;
		$sendData['tracking_ship_date']   = !empty($extraInfo['tracking_ship_date'])?$extraInfo['tracking_ship_date']:null;
		$sendData['customer_email']  = !empty($extraInfo['customer_email'])?$extraInfo['customer_email']:null;
		$sendData['tracking_postal_code']        = !empty($extraInfo['tracking_postal_code'])?$extraInfo['tracking_postal_code']:null;
		$sendData['specialNumberDestination']        = !empty($extraInfo['specialNumberDestination'])?$extraInfo['specialNumberDestination']:null;
		$sendData['order ']        = !empty($extraInfo['order '])?$extraInfo['order ']:null;
		$sendData['lang  ']        = !empty($extraInfo['lang  '])?$extraInfo['lang  ']:null;
        $result = $this->_getApiData($requestUrl, 'POST',$sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

}
