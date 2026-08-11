<?php
class OrderLaterSendmailConfigFormPage extends WebPage{

	private $config;

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    	SOY2::imports("module.plugins.order_later_sendmail.util.*");
    }

    function doPost(){
    	if(soy2_check_token() && isset($_POST["Config"])){

    		$config = $_POST["Config"];
    		$config["date"] = (int)$config["date"];
    		OrderLaterSendmailUtil::saveConfig($config);
    		OrderLaterSendmailUtil::saveMailTitle($_POST["Mail"]["title"]);
    		OrderLaterSendmailUtil::saveMailContent($_POST["Mail"]["content"]);

    		$this->config->redirect("updated");
    	}
    }

    function execute(){
    	$config = OrderLaterSendmailUtil::getConfig();

    	parent::__construct();
    	    	
    	$this->addForm("form");

    	$this->addLabel("job_path", array(
			"text" => $this->buildPath(). " " . SOYSHOP_ID
		));

		$this->addLabel("site_id", array(
			"text" => SOYSHOP_ID
		));

		$mode = (isset($config["mode"])) ? (int)$config["mode"] : 0;
		$this->addCheckBox("order_register_mode", array(
			"name" => "Config[mode]",
			"value" => OrderLaterSendmailUtil::MODE_REGISTER,
			"selected" => ($mode === OrderLaterSendmailUtil::MODE_REGISTER),
			"label" => "新規注文"
		));

		$this->addCheckBox("payment_confirm_mode", array(
			"name" => "Config[mode]",
			"value" => OrderLaterSendmailUtil::MODE_PAYMENT,
			"selected" => ($mode === OrderLaterSendmailUtil::MODE_PAYMENT),
			"label" => "支払確認"
		));

		$this->addCheckBox("order_send_mode", array(
			"name" => "Config[mode]",
			"value" => OrderLaterSendmailUtil::MODE_SEND,
			"selected" => ($mode === OrderLaterSendmailUtil::MODE_SEND),
			"label" => "発送"
		));

		$this->addInput("sendmail_date", array(
			"name" => "Config[date]",
			"value" => (isset($config["date"])) ? (int)$config["date"] : 0
		));

		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => OrderLaterSendmailUtil::getMailTitle()
		));

		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => OrderLaterSendmailUtil::getMailContent()
		));
    }

    function buildPath(){
		return dirname(dirname(__FILE__)) . "/job/exe.php";
	}

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>
