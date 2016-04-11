# soycms
非公式 PHP7用  
PHP7でSOY CMSを動かすためにいくつか修正している他、PHP5でも影響のある不具合を修正  
公式:http://www.soycms.net/  
  
~~Download ZIPでダウンロードしたファイル群をルートディレクトリに配置したら動作します。~~  
packageディレクトリ内にパッケージ化したzipファイルがあります。
動作しない場合は、https://saitodev.co/contact までご連絡ください。

そのまま配置した場合はSQLite版になりますが、  
MySQL版をご利用したい場合は、  

/cms/common/soycms.config.phpの2行目の  
define("SOYCMS_DB_TYPE","sqlite"); → define("SOYCMS_DB_TYPE","mysql");  
と修正してください。  

近々、パッケージ化したzipファイルをお渡しできる方法を用意します。
