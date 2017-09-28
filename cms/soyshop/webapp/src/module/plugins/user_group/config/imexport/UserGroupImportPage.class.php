<?php

class UserGroupImportPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function doPost(){
		if(!soy2_check_token()){
            SOY2PageController::jump("Config.Detail?plugin=user_group&import&failed");
            exit;
        }

		set_time_limit(0);

		$logic = SOY2Logic::createInstance("module.plugins.user_group.logic.UserGroupCSVLogic");

		$charset = (isset($_POST["charset"])) ? $_POST["charset"] : "Shift-JIS";
		$logic->setCharset($charset);

        $file  = $_FILES["import"];

		if(!$logic->checkUploadedFile($file)){
            SOY2PageController::jump("Config.Detail?plugin=user_group&import&failed");
            exit;
        }
        if(!$logic->checkFileContent($file)){
			SOY2PageController::jump("Config.Detail?plugin=user_group&import&invalid");
            exit;
        }

        //ファイル読み込み・削除
        $fileContent = file_get_contents($file["tmp_name"]);
        unlink($file["tmp_name"]);

        //データを行単位にばらす
        $lines = $logic->GET_CSV_LINES($fileContent);    //fix multiple lines
		array_shift($lines);
		if(count($lines)){
			$dbLogic = SOY2Logic::createInstance("module.plugins.user_group.logic.DataBaseLogic");
			$fieldList = self::getFieldList();
			foreach($lines as $line){
				$values = explode(",", $logic->encodeFrom($line));
				if(count($values) < 2) continue;
				$groupId = array_shift($values);
				$groupName = array_shift($values);
				$groupCode = array_shift($values);
				$groupId = self::import($groupId, $groupName, $groupCode);

				if(isset($groupId) && is_numeric($groupId)){
					$counter = 0;	//何個目のカラムにチェックボックスがあるか？
					$customs = array();
					$posts = array();
					foreach($values as $v){
						$v = trim($v, "\"");
						$conf = $fieldList[$counter++];
						if($conf["type"] == UserGroupCustomSearchFieldUtil::TYPE_CHECKBOX){
							$v = str_replace("\n", ",", $v);
						}
						$posts[$conf["fieldId"]] = $v;
					}
					$dbLogic->save($groupId, $posts);
				}
			}
		}
	}

	private function getFieldList(){
		$list = array();
		$configs = UserGroupCustomSearchFieldUtil::getConfig();
		if(count($configs)){
			foreach($configs as $fieldId => $conf){
				$list[] = array("fieldId" => $fieldId, "type" => $conf["type"]);
			}
		}

		return $list;
	}

	private function import($groupId, $groupName, $groupCode){
		try{
			$group = self::dao()->getById($groupId);
		}catch(Exception $e){
			$group = new SOYShop_UserGroup();
		}
		$group->setName($groupName);
		$group->setCode($groupCode);

		//新規
		if(is_null($group->getId())){
			try{
				$groupId = self::dao()->insert($group);
			}catch(Exception $e){
				var_dump($e);
				return null;
			}
		}else{
			try{
				self::dao()->update($group);
			}catch(Exception $e){
				return null;
			}
		}

		return $groupId;
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("failed", (isset($_GET["failed"])));
		DisplayPlugin::toggle("invalid", isset($_GET["invalid"]));

		$this->addForm("import_form", array(
             "ENCTYPE" => "multipart/form-data"
        ));
	}

	private function dao(){
		static $dao;
		if(is_null($dao)){
			SOY2::imports("module.plugins.user_group.domain.*");
			$dao = SOY2DAOFactory::create("SOYShop_UserGroupDAO");
		}
		return $dao;
	}

	function setConfigObj($configObj){
        $this->configObj = $configObj;
	}
}
