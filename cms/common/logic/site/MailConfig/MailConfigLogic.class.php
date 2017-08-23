<?php
SOY2::import("domain.cms.DataSets");


class MailConfigLogic extends SOY2LogicBase{
	
	const CONFIG_KEY = "site.mail_config";
	
	private $config;
	
	/**
	 * 設定取得
	 * @return SOY2Mail_ServerConfig
	 */
	public function get(){
		if(!$this->config){
			$this->load();
		}
		
		return $this->config;
    }
    
    /**
     * 設定読み込み
     */
    private function load(){
    	
    	try{
    		$this->config = DataSets::get(self::CONFIG_KEY);
    	}catch(Exception $e){
    		$this->config = new SOY2Mail_ServerConfig();
    	}
    	
    	if(is_null($this->config)){
    		$this->config = new SOY2Mail_ServerConfig();
    	}
    	
    	
    }
    
    /**
     * 設定保存
     * @param SOY2Mail_ServerConfig $config
     */
    public function save(SOY2Mail_ServerConfig $config){
    	DataSets::put(self::CONFIG_KEY, $config);
    }
    
}
?>