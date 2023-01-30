<?php

class PlainTextColumn extends SOYInquiry_ColumnBase{

	//値の保存（出力）をするかどうか
	protected $noPersistent = false;//falseは「する」

	/**
	 * フォームでの項目名は空にする：デフォルトテンプレートでcolspan=2になる
	 */
	function getLabel(){
		return "";
	}

	/**
	 * フォーム用
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){
		return $this->label;
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = "入力欄はなく項目名だけがフォームに出力されます。<br>";

		$html .= '<label for="Column[config][noPersistent]'.$this->getColumnId().'_y">';
		$html .= '<input  id="Column[config][noPersistent]'.$this->getColumnId().'_y" type="radio" name="Column[config][noPersistent]" value="0" '.($this->noPersistent ? '' : 'checked="checked"').' />';
		$html .= 'メールに含める</label>';

		$html .= '<label for="Column[config][noPersistent]'.$this->getColumnId().'_n">';
		$html .= '<input  id="Column[config][noPersistent]'.$this->getColumnId().'_n" type="radio" name="Column[config][noPersistent]" value="1" '.($this->noPersistent ? 'checked="checked"' : '').' />';
		$html .= 'メールに含めない</label>';

		return $html;
	}


	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure(array $config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->noPersistent = (isset($config["noPersistent"])) ? str_replace("\"","&quot;",$config["noPersistent"]) : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["noPersistent"] = $this->noPersistent;
		return $config;
	}

	function validate(){
		return true;
	}

	/**
	 * データ投入用
	 */
	function getContent(){
		return "";
	}

	/**
	 * 確認画面用
	 */
	function getView(){
		return $this->label;
	}
}
