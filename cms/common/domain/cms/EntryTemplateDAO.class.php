<?php
/**
 * @entity cms.EntryTemplate
 */
class EntryTemplateDAO extends SOY2DAO{

    /**
	 * テンプレートのディレクトリを返す
	 * @final
	 */
	public function getTemplateDirectory(){
		return UserInfoUtil::getSiteDirectory()."/.entry_template";
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
		if(is_null($xml->name)){
			return false;
		}
		
		if(is_null($xml->name->description)){
			return false;
		}
		
		if(is_null($xml->content)){
			return false;
		}
		
		if(is_null($xml->more)){
			return false;
		}
		
		if(is_null($xml->style)){
			return false;
		}
		
		return true;
	}
	
	/**
	 * テンプレートファイルをパースして、Templateオブジェクトを返す
	 */
	private function parse($relative_fname){
		$filename = $this->getTemplateDirectory().'/'.$relative_fname;
		if(!file_exists($filename)){
			throw new Exception($filename." does not exists");
		}
		
		@$xml = simplexml_load_file($filename);
		if(!$xml){
			throw new Exception($filename." does not xml file");
		}
		
		
		
		$template = new EntryTemplate();
		$template->setId(str_replace('.xml','',$relative_fname));
		$template->setName((string)$xml->name);
		$template->setDescription((string)$xml->description);
		$templates = $xml->templates[0];
		$temp_arr = array();
		$temp_arr['content']=(string)$templates->content;
		$temp_arr['more']=(string)$templates->more;
		$temp_arr['style']=(string)$templates->style;
		$temp_arr['labelId']=(int)$templates->labelId;
		$template->setTemplates($temp_arr);
		$labelRestrictionPositive=((array)$xml->labelRestrictionPositive);
		$template->setLabelRestrictionPositive((array)@$labelRestrictionPositive["labelId"]);
		
		return $template;
		
	}

	/**
	 * ディレクトリを走査してテンプレートのリストを返す
	 * 
	 */
	function get(){
		
		$ret_val = array();
		$res_dir = opendir($this->getTemplateDirectory());

		while( $file_name = readdir( $res_dir ) ){
			if($file_name[0] != "." && preg_match('/[\d]+\.xml$/i',$file_name)){
				$ret_val[str_replace(".xml","",$file_name)] = $this->parse($file_name);
			}
		}
		
		closedir( $res_dir );
		
		return $ret_val;
	}
	
	/**
	 * ファイル名からテンプレートを取得する
	 */
	function getByFileName($filename){
		return $this->parse($filename);
	}
	
	function getById($id){
		return $this->getByFileName($id.'.xml');	
	}
	
	/**
	 * テンプレートファイルを書き出す
	 */
	function insert(EntryTemplate $temp,$filename = null){
		
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
		$temp_arr = $temp->getTemplates();
		
		$xml[] = '<name>'.$temp->getName().'</name>';
		$xml[] = '<description><![CDATA['.$temp->getDescription().']]></description>';
		$xml[] = '<templates>';
		$xml[] = '<content><![CDATA['.$temp_arr['content'].']]></content>';
		$xml[] = '<more><![CDATA['.$temp_arr['more'].']]></more>';
		$xml[] = '<style><![CDATA['.$temp_arr['style'].']]></style>';
		$xml[] = '<labelId>'.(int)$temp_arr['labelId'].'</labelId>';
		$xml[] = '</templates>';
		$xml[] = '<labelRestrictionPositive>';
		foreach($temp->getLabelRestrictionPositive() as $positiveLabelId){
			$xml[] = '<labelId>'.$positiveLabelId.'</labelId>';
		}
		$xml[] = '</labelRestrictionPositive>';		
		
		$res = '<?xml version="1.0" encoding="UTF-8"?><template>'.implode("\n",$xml).'</template>';
		
		return file_put_contents($this->getTemplateDirectory()."/".$filename,$res);
	}
	
	/**
	 * テンプレートを更新する
	 */
	function update(EntryTemplate $template){
		//内部的には現在の内容で上書きをする
		return $this->insert($template,$template->getId().'.xml');
	}
	
	/**
	 * テンプレートを削除する
	 */
	function delete($filename){
		return unlink($this->getTemplateDirectory()."/".$filename);
		
	}
	function deleteById($id){
		return $this->delete($id.'.xml');
	}
	
}
?>