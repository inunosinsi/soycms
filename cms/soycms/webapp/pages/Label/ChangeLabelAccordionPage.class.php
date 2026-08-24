<?php

class ChangeLabelAccordionPage extends CMSWebPageBase{

	function __construct() {
		header('Content-Type: application/json; charset=utf-8');
		
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		    http_response_code(405);
		    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
		    exit;
		}
		
		$data = json_decode(file_get_contents('php://input'), true);

		if(!isset($data['label'])){
			http_response_code(405);
		    echo json_encode(['status' => 'error', 'message' => 'No data available']);
		    exit;
		}
		
		$lbl = $data['label'];
		$isChecked = (isset($data['isChecked'])) ? (bool)$data['isChecked'] : false;

		SOY2::import("util.LabelAccordionUtil");
		LabelAccordionUtil::save($lbl, $isChecked);		
		
		// 成功レスポンス
		echo json_encode([
			'status'  => 'success'
		]);
		exit;
	}
}
