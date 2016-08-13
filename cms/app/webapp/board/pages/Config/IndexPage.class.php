<?php

class IndexPage extends WebPage{
	private $threadId;

	function doPost(){

		$dao = SOY2DAOFactory::create("SOYBoard_ConfigDAO");
		$entity = new SOYBoard_Config();

		$posts = (object)$_POST["config"];

		$entity = SOY2::cast($entity,$posts);

		$entity->setThreadId($this->threadId);

		$logic = SOY2Logic::createInstance("logic.ConfigLogic");
		$logic->update($entity);


		$threadLogic = SOY2Logic::createInstance("logic.ThreadLogic");
		$thread = $threadLogic->getById($this->threadId);

		$thread->setTitle($_POST["threadName"]);
		$threadLogic->update($thread);

		CMSApplication::jump(".Config.".$this->threadId);



		exit;

	}

    function __construct($arg) {
    	$threadId = (isset($arg[0])) ? $arg[0] : null;
    	$this->threadId = $threadId;
    	WebPage::__construct();

    	$logic = SOY2Logic::createInstance("logic.ConfigLogic");
    	$threadLogic = SOY2Logic::createInstance("logic.ThreadLogic");

    	$thread = $threadLogic->getById($threadId);

    	$this->createAdd("thread_title","HTMLLabel",array(
    		"text"=>$thread->getTitle()."の詳細設定"
    	));

    	try{
    		$config = $logic->getByThreadId($threadId);
    	}catch(Exception $e){
    		CMSApplication::jump();
    		exit;
    	}



    	$this->createAdd("main_form","HTMLForm");

    	$this->createAdd("default_name","HTMLInput",array(
			"name"=>"config[defaultName]",
			"value"=>$config->getDefaultName()
		));
    	$this->createAdd("max_response","HTMLInput",array(
			"name"=>"config[maxResponse]",
			"value"=>$config->getMaxResponse()
		));



		$this->createAdd("isStopped_true","HTMLCheckBox",array(
			"name"=>"config[isStopped]",
			"label"=>"凍結する",
			"value"=>"1",
			"selected"=>($config->getIsStopped() == 1)
		));

		$this->createAdd("isStopped_false","HTMLCheckBox",array(
			"name"=>"config[isStopped]",
			"label"=>"凍結しない",
			"value"=>"0",
			"selected"=>($config->getIsStopped() == 0)
		));


		$this->createAdd("back_link","HTMLLink",array(
			"link"=>SOY2PageController::createLink(APPLICATION_ID )
		));

    	$this->createAdd("thread_name","HTMLInput",array(
    		"name"=>"threadName",
    		"value"=>$thread->getTitle()
    	));

    }


}
?>