複数ページが続くフォームのページを設置する。

仕様
・各ページの設定はjsonにて行う
jsonのフォーマット
ファイル名：ハッシュ値.json → 日付のハッシュ？

{
		"name":"",
		"type":"",
		"description":"",
		"label":"",
		"order":"",
		"next":"",
		"choice":{

		},
		"item":{

		},
		"extend":"",
		"template":""
}

○name
ページ名

○type
text(文字ページ)、choice(選択式)、form(入力フォームの羅列)、extend(自作のSOY2HTMLページ)
※文字ページは説明文のみを表示し、戻る(prev)と次へ(next)のみのページとなる

○description
ページの説明文

○label
typeで選択式(choice)を選んだ場合に、確認画面で出力するラベル名

○order
並び順

○next
「次へ」等のボタンを設置し、ボタンを押した時にどのページに遷移するか？を決める。値の指定方法はファイル名のハッシュ

○prev(廃止：公開側でrouteセッションに辿ってきたルートを記録すればprevの値は不要になる)
「前へ」や「戻る」ボタンを設置し、ボタンを押した時にどのページに遷移するか？を決める。値の指定方法はファイル名のハッシュ

○choice
typeでchoiceを指定した時に使う。@ToDo 選択肢をどうやって持つか？
choice内の配列array(array("item" => "項目名", "next" => "次のページのハッシュ値", "order" => "並び順"))

○item
typeでformを指定した時に使う？
item内の配列array(array("item" => "項目名", "type" => "項目種別", "order" => "並び順", "replacement" => "置換文字列", "option" => "チェックボックスやセレクトボックス等の場合の項目", "required" => "必須項目か？1 or 0"))

○extend
typeでextendを指定した時に使う。読み込むファイルのパス ○○Page.class.phpの方のパスを記載

○template
テンプレートを指定します。
テンプレートのディレクトリは/サイトID/.multiPageForm/template/以下
