<?php
/**
 * CSV(TSV)のベースクラス
 *
 * 基本的なオブジェクトのimport,exportはこれで対応出来るようになっている
 */
class ExImportLogicBase extends SOY2LogicBase{

    private $quote = true;
    private $charset = "UTF-8";
    private $separator = ",";

    private $items = array();
    private $labels = array();

    protected $_func;

    /**
     * オブジェクトをCSV,TSVに変換
     */
    function export($obj){
        if(!$this->_func)$this->buildExFunc($this->getItems());
        $array = call_user_func($this->_func, $obj);
        return $this->encodeTo($this->implodeToLine($array));
    }

    /**
     * CSV,TSVの一行から連想配列（オブジェクト）に変換
     */
    function import($line){
        $line = $this->encodeFrom($line);
        $items = $this->explodeLine($line);
        if(!$this->_func)$this->buildImFunc($this->getItems());
        return call_user_func($this->_func,$items);
    }

    /**
     * import用のfunction
     */
    function buildImFunc($items){
        $function = array();
        $function[] = '$res = array();';

        $items = array_keys($items);
        foreach($items as $key => $item){
            if(!$item)continue;

            $function[] = 'if(isset($items['.$key.'])){ ';
            $function[] = '  $item = trim($items['.$key.']);';
            $function[] = '  $res["'.$item.'"] = $item;';
            $function[] = '}';
        }

        $function[] = 'return $res;';
		$this->_func = function($items) use ($function) { return eval(implode("\n", $function)); };
    }

    /**
     * export用のfunction（オブジェクトを配列にする）
     * ついでにラベルも設定しなおす
     */
    function buildExFunc($items){
        $labels = $this->getLabels();
        $used_labels = array();
        $function = array();
        $function[] = '$res = array();';
        foreach($items as $key => $item){
            if(!$item)continue;

            $getter = "get" . ucwords($key);
            $function[] = '$res[] = (method_exists($obj,"'.$getter.'")) ? $obj->'.$getter.'()  : "" ;';

            //ラベル
            $used_labels[] = $labels[$key];
        }

        $function[] = 'return $res;';

        $this->_func = function($obj) use ($function) { return eval(implode("\n", $function)); };
        $this->setLabels($used_labels);
    }

    /**
     * 配列をCSVの一行にする
     * @param Array
     * @param boolean 見出し行かどうか
     * @return String
     */
    function implodeToLine($array, $isHeader = false){
        $quote = $this->getQuote();
        $separator = $this->getSeparator();
        foreach($array as $key => $value){
            if(
              $quote
              || ( strpos($value, "\"") !== false )
              || ( strpos($value, "\n") !== false )
              || ( strpos($value, "\r") !== false )
              || ( $separator == "tab" && strpos($value, "\t") !== false )
              || ( $separator != "tab" && strpos($value, ",") !== false )
              || ( $isHeader && strncmp("ID", $value, 2) == 0 )// CSVファイルの先頭にIDという文字があるとExcelではSYLKファイルと誤認識されるので "ID" にしておく
            ){
                $array[$key] = '"' . str_replace('"', '""', $value) . '"';
            }
        }
        return implode($this->getSeparatorString(), $array);
    }

    /**
     * CSV一行をばらす
     * @param String
     * @return Array
     */
    function explodeLine($line){
        $quote = $this->getQuote();
        $separator = $this->getSeparator();

        if($separator == "tab"){
            preg_match_all('/([^"]*?(?:"[^"]*?"[^"]*?)*)(?:\t|\r\n|\r|\n|$)/', $line, $matches);
        }else{
            preg_match_all('/([^"]*?(?:"[^"]*?"[^"]*?)*)(?:,|\r\n|\n|\r|$)/', $line, $matches);
        }

        $values = array();
        foreach($matches[1] as $value){
            if(
                $quote
                OR strlen($value) >1 AND $value[0] == '"' AND $value[strlen($value)-1] = '"'
                OR ( strpos($value, "\n") !== false )
                OR ( strpos($value, "\r") !== false )
                OR ( $separator == "tab" AND strpos($value, "\t") !== false )
                OR ( $separator != "tab" AND strpos($value, ",") !== false )
            ){
                $value = preg_replace("/^\"/","",$value);//substr($value, 1, strlen($value)-2);
                $value = preg_replace("/\"\$/","",$value);//substr($value, 1, strlen($value)-2);
                $value = str_replace('""', '"', $value);
            }

            $values[] = $value;
        }

        return $values;
    }


    function encodeTo($str){
        if($this->charset != "UTF-8"){
            return mb_convert_encoding($str,$this->charset,"UTF-8");
        }

        return $str;
    }

    function encodeFrom($str){
        if($this->charset != "UTF-8"){
            return mb_convert_encoding($str,"UTF-8",$this->charset);
        }
        return $str;
    }

    /**
     * 見出し行の出力
     */
    function getHeader(){
        return $this->encodeTo($this->implodeToLine($this->getLabels(), true));
    }

    /**
     * 改行を含むデータの場合に行を正しく認識しなおす
     */
    function GET_CSV_LINES($lines){
        if(!is_array($lines)){
            $lines = str_replace(array("\r\n","\r"), "\n", $lines);
            $lines = explode("\n", $lines);
        }
        $csv_lines = array();
        $status = 0;
        $buffer = array();
        foreach($lines as $line){
            $buffer[] = $line;
            //まずはバッファーに付け足す
            $status = ($status + substr_count($line, '"')) % 2;
            //"が閉じていれば0
            if( $status == 0 ){
                //"が閉じていれば
                $csv_lines[] = implode("\n", $buffer);    //バッファーの中身を移す
                $buffer = array();    //バッファーを空にする
            }
        }
        return $csv_lines;
    }

    /**
     * ファイルのチェック
     */
    public function checkFileContent($file){
        if($file["size"] == 0) return false;

        $head = file_get_contents($file["tmp_name"], false, null, 0, 1000);
        if( false === strpos($head,$this->getSeparatorString()) ) return false;
        if( $this->getQuote() && false === strpos($head,'"') ) return false;

        return true;

    }

    /**
     * アップロードされたファイルのエラーをチェックする
     * @param Array("name"=>,"type"=>,"size"=>,"tmp_name","error")
     * @return True or String
     */
    public function checkUploadedFile($file){
        return ( $file["error"] === UPLOAD_ERR_OK );
/*
        switch($file["error"]){
            case UPLOAD_ERR_OK:
                return true;
            case UPLOAD_ERR_INI_SIZE://大きすぎ init
                return "ファイルサイズが制限（".ini_get("upload_max_filesize")."）を超えています。可能であれば分割してアップロードしてください。";
            case UPLOAD_ERR_FORM_SIZE://大きすぎ HTML Form
                return "ファイルサイズが制限（" . $_POST["MAX_FILE_SIZE"]."）を超えています。可能であれば分割してアップロードしてください。";
            case UPLOAD_ERR_PARTIAL://途中まで
            case UPLOAD_ERR_NO_FILE://失敗 0
                return "ファイルがサーバーに届きません。";
            case UPLOAD_ERR_NO_TMP_DIR://一時ディレクトリがない（PHP 4.3.10以降, 5.0.3以降）
            case UPLOAD_ERR_CANT_WRITE://書き込み失敗（PHP 5.1.0以降）
                return "ファイルの保存に失敗しました。";
            //case UPLOAD_ERR_EXTENSION://拡張モジュールによって停止？（PHP 5.2.0以降）
            default:
                return "何らかのエラーによってファイルのアップロードに失敗しました。";
        }
*/
    }

    /**
     * タイトルの数とインポート予定の項目数が合っているかどうかをチェックする
     */
    public function checkTitles($titles){
        if(!is_array($titles)){
            $titles = $this->explodeLine($titles);
        }

        $itemCount = 0;
        $items = $this->getItems();
        foreach($items as $item){
            if(!$item)continue;
            $itemCount++;
        }

        return ($itemCount == count($titles));
    }

    /* setter getter */

    function getQuote() {
        return $this->quote;
    }
    function setQuote($quote) {
        $this->quote = $quote;
    }
    function getCharset() {
        return $this->charset;
    }
    function setCharset($charset) {

        switch($charset){
            case "Shift-JIS":
            case "Shift_JIS":
                $charset = "SJIS-win";
                break;
            default:
                $charset = "UTF-8";
                break;
        }

        $this->charset = $charset;
    }
    function getSeparator() {
        return $this->separator;
    }
    function setSeparator($separator) {
        $this->separator = $separator;
    }
    function getSeparatorString(){
        if($this->separator == "tab"){
            return "\t";
        }else{
            return ",";
        }
    }
    function getItems() {
        return $this->items;
    }
    function setItems($items) {
        $this->items = $items;
    }

    function getLabels() {
        return $this->labels;
    }
    function setLabels($labels) {
        $this->labels = $labels;
    }
}
