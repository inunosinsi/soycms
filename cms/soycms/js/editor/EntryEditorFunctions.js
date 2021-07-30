
//メモを編集
function edit_entry_memo(){
	var str = prompt("メモを入力して下さい", $("#entry_description").val());

	if(str == null)return;

	$("#entry_memo").html(str);
	$("#entry_description").val(str);

	if(!str){
		$("#entry_memo_wrapper").hide();
	}else{
		$("#entry_memo_wrapper").show();
	}
}

//ラベルを作成
function create_label(){
	var str = prompt("新しいラベル名を入力してください","");
	if(str == null || str.length < 1)return;

	var callback = function(result){
		var obj = eval('('+result+')');
		if(obj.result == 1){
			var checkbox = $("<input/>");
			checkbox.attr("type","checkbox");
			checkbox.val(obj.labelId);
			checkbox.attr("onclick",function(){
				if(this.checked){
					$('#entry_label_memo_'+this.value).show();
				}else{
					$('#entry_label_memo_'+this.value).hide();
				}
			});
			checkbox.attr("id",'entry_label_memo_'+obj.labelId);
			checkbox.attr("name",'label[]');

			var wrapper = $("<div/>");
			wrapper.attr("class","label_wrapper");
			wrapper.attr("className","label_wrapper");

			var label = $("<label/>");
			label.attr("for",checkbox.id);
			label.append(document.createTextNode(str));

			var labelmemo = $("<p/>");
			labelmemo.html("["+str+"]が設定されています。");
			labelmemo.attr("id",'entry_label_memo_'+obj.labelId);
			labelmemo.css("display","none");

			wrapper.append(checkbox);
			wrapper.append(label);

			$("#labels_wrapper").append(wrapper);
			$("#labelmemos").append(labelmemo);
		}
		alert(obj.message);
	};

	$.ajax({
		url: CreateLabelLink,
		data: "caption="+encodeURI(str),
		type: 'post',
		success: callback,
		error:function(result){
			alert("失敗");
		}
	});
}

function toggle_labelmemo(value,checked){
	if(checked){
		$('#entry_label_memo_'+ value).show();

		/** 複数ラベルに対応するための書き換え→2行下がshow()だったので変更の必要なし **/
		var obj = $('.toggled_by_label_' + value);
		$.each(obj,function(){
			$(this).show();
		});

	}else{
		$('#entry_label_memo_'+ value).hide();

		/** @ToDo 複数ラベルに対応するための書き換え **/
		var obj = $('.toggled_by_label_' + value);
		$.each(obj,function(){
			//他にtoggled_by_label_{label_id}のクラスがないか？調べる
			var label_classes = obj.prop("class").split(" ");
			if(label_classes.length < 2){	//付与されているクラスが1個の場合は無条件でhide()
				$(this).hide();
			}else{
				//今回押したラベル以外のもののチェックの状況を調べる
				var flg = true;
				for(var i = 0; i < label_classes.length; i++){
					cls = label_classes[i].replace("toggled_by_", "");
					if(flg && $("#" + cls).prop("checked")){
						flg = false;
					}
				}
				if(flg){	//該当するすべてのラベルにチェックがない場合はtrueで指定の項目も非表示にする
					$(this).hide();
				}
			}
		});
	}

	if(is_ie){
		$('#entry_content_wrapper').css("top","10px");
		$('#entry_content_wrapper').css("top","0px");
	}
}

function confirm_open(){
	var obj = document.getElementsByName('isPublished');
	var i;
	for(i =0; i<obj.length; i++){
		if(obj[i].value == 1){
			break;
		}
	}



	if(obj[i].checked){
		return confirm("「公開」状態で新規作成しますがよろしいですか？");
	}else{
		return true;
	}
}

function confirm_trackback(){
	var obj = document.getElementsByName('isPublished');
	var i;
	for(i =0; i<obj.length; i++){
		if(obj[i].value == 1){
			break;
		}
	}

	var tr = document.getElementById("trackback_id");

	if(!obj[i].checked && tr.value.length != 0){
		return confirm("トラックバック送信先を入力されましたが、\n非公開状態ではトラックバックの送信は行われません。\n続行しますか？");
	}else{
		return true;
	}

}
