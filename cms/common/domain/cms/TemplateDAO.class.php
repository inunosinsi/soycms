<?php

/**
 * @entity cms.Template
 */
abstract class TemplateDAO extends SOY2DAO{

	/**
	 * テンプレートのディレクトリを返す
	 * @final
	 */
	public function getTemplateDirectory(){
		return UserInfoUtil::getSiteDirectory().".template";
	}
	
	/**
	 * テンプレートが形式どおりかどうかチェック
	 * @final
	 */
	public static function test($filename){
		if(!file_exists($filename)){
			return false;
		}
		
		@$xml = simplexml_load_file($filename);
		if(!$xml){
			return false;
		}
		
		if(is_null($xml->id)){
			return false;
		}
		
		if(is_null($xml->type)){
			return false;
		}
		
		if(is_null($xml->templates)){
			return false;
		}
		
		return true;
	}
	
	/**
	 * テンプレートファイルをパースして、Templateオブジェクトを返す
	 */
	private function parse($relative_fname,$withDetail = false){
		$filename = $this->getTemplateDirectory().'/'.$relative_fname;
		if(!file_exists($filename)){
			throw new Exception($filename." does not exists");
		}
		
		@$xml = simplexml_load_file($filename);
		if(!$xml){
			throw new Exception($filename." does not xml file");
		}
		
		$template = new Template();
		$template->setId(str_replace('.xml','',$relative_fname));
		$template->setName((string)$xml->name);
		$template->setDescription((string)$xml->description);
		$template->setPageType((string)$xml->type);
		$template->setArchieveFileName($this->getTemplateDirectory().'/' . (string)$xml->id . ".zip");
		$template->setTemplatesDirectory($this->getTemplateDirectory().'/' . $template->getId() . "/");
				
		if($withDetail){
			$templates = array();		
			foreach($xml->templates->template as $tmp){
				$templates[(string)$tmp->id] = array(
					"id" => (string)@$tmp->id,
					"name" => (string)@$tmp->name,
					"type" => (string)@$tmp->type,
					"description" => (string)@$tmp->description
				);
			}
			$template->setTemplate($templates);
			
			$files = array();
			foreach($xml->files->file as $tmp){
				$files[(string)@$tmp->name] = array(
					"name" => (string)@$tmp->name,
					"path" => (string)@$tmp->path,
					"description" => (string)@$tmp->description
				);				
			}
			
			$template->setFileList($files);
		}
		
		if(@$xml->active == 1){
			$template->setActive(1);
		}
				
		return $template;
		
	}

	/**
	 * ディレクトリを走査してテンプレートのリストを返す
	 */
	function get($type = null,$flag = false){
		
		$ret_val = array();
		$res_dir = opendir($this->getTemplateDirectory());

		if($type !== null ){
			$regex = $type."_[a-zA-Z0-9_]*_manifest\.xml";
		}else{
			$regex = "[0-9]*_[a-zA-Z0-9_]*_manifest\.xml";
		}

		while( $file_name = readdir( $res_dir ) ){
			if($file_name[0] == ".")continue;
			
			if(preg_match('/^'.$regex.'$/i',$file_name)){
				$ret_val[$file_name] = $this->parse($file_name,$flag);
			}
		}
		
		closedir( $res_dir );
		
		return $ret_val;
	}
	
	/**
	 * ファイル名からテンプレートを取得する
	 */
	function getByFileName($filename){
		return $this->parse($filename,true);
	}
	
	function getById($id){
		return $this->getByFileName($id.'.xml');	
	}
	
	/**
	 * テンプレートファイルを書き出す
	 */
	function insert(Template $temp,$filename = null){
		
		if(is_null($filename)){
			$max = 0;
			foreach($this->get() as $template){
				if($max < $template->getId()){
					$max = $template->getId();
				}
			}
			$filename = ($max+1).'.xml';
		}
		
		$xml = array();
		
		$xml[] = '<name>'.$temp->getName().'</name>';
		$xml[] = '<description><![CDATA['.$temp->getDescription().']]></description>';
		$xml[] = '<page_type>'.$temp->getPageType().'</page_type>';
		$templates = array();
		foreach($temp->getTemplate() as $key => $temp_str){
			
			if(!is_numeric($key)){
				$startTag= '<template id="'.$key.'">';
				$endTag = '</template>';
			}else{
				$startTag= '<template>';
				$endTag = '</template>';
			}
			$templates[] = $startTag.'<![CDATA['.$temp_str.']]>'.$endTag;
		}
		
		$xml[] = '<templates>'.implode("\n",$templates).'</templates>';
		
		$res = '<?xml version="1.0" encoding="UTF-8"?><template>'.implode("\n",$xml).'</template>';
		
		return file_put_contents($this->getTemplateDirectory()."/".$filename,$res);
		
	}
	
	/**
	 * テンプレートを更新する
	 */
	function update(Template $template){
		//内部的には現在の内容で上書きをする
		return $this->insert($template,$template->getId().'.xml');
	}
	
	/**
	 * テンプレートを削除する
	 */
	function delete($filename){
		list($type,$fname,$suffix) = explode("_",$filename);
		$fname = $this->getTemplateDirectory() . "/" . $fname . ".zip";
		
		if(file_exists($fname)){
			unlink($fname);	
		}
		
		$xml_fname = $this->getTemplateDirectory() . "/" . $filename;
		if(file_exists($xml_fname)){
			unlink($xml_fname);
		}
		return true;
		
	}
	
	function deleteById($id){
		return $this->delete($id.'.xml');
	}
	

}
?>