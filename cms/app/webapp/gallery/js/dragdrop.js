(function(){
	var columns = document.querySelectorAll('.column');
	var dustbox = document.querySelector('#dustbox');
	var dragSrcEl = null;
	var form = document.querySelector('#onsubmit');
	
	//ファイルのアップロードに関すること
	document.querySelector('#file').addEventListener('change', function(e){
		document.querySelector('#uploadsubmit').submit();
	} , false);
	
	//画像のドラック開始時点
	this.handleDragStart = function(e){
		dragSrcEl = this;
  	};
	
	this.handleDragEnter = function(e){
		this.classList.add('over');
	};
	
	this.handleDragOver = function(e){
		if(e.preventDefault){
			e.preventDefault();
		}	
	};
	
	this.handleDragLeave = function(e){
		this.classList.add('over');
	};
	
	this.handleDrop = function(e){
		
		if(e.stopPropagation){
			e.stopPropagation();
		}
		
		//ブラウザのディフォルトの動作を禁止する
		e.preventDefault();
		
		if(dragSrcEl != this) {
			
			var oldImage = dragSrcEl.childNodes[0];
			var oldId = dragSrcEl.id;	//ドラック元
			
			var newId = this.id;	//ドロップ先
			var last = columns.length;
			
			//移動してきたノードを先に削除
			form.removeChild(dragSrcEl);
			
			//thisより上に新しいノードを作成する
			var div = document.createElement("div");
			div.setAttribute("class", "column");
			div.appendChild(oldImage);
			
			//どこに挿入するか？上から下に移動したい時
			if(oldId > newId){
				//作成したノードを指定のノードの上に挿入する
				if(newId != last){
					form.insertBefore(div, this);
				
				//最後のノードへ移動した時
				}else{
					var last = document.querySelector('.last_column');
					form.insertBefore(div, last);
				}
					
			//下から上に移動したい時
			}else{
				//作成したノードを指定のノードの下に挿入する
				if(oldId != last){
					form.insertBefore(div, this.nextSibling);
				
				//最後のノードから移動した時
				}else{
					form.insertBefore(div, this);
				}
				
				
			}
			
			//前のノードを削除しなければならない 画像を持たないノードを掃除する
			var check_columns = document.querySelectorAll('.column');
			
			//addEventListener一式をリセットする
			addEvent(check_columns, dustbox);
			[].forEach.call(check_columns, function(check_column){
				if(typeof(check_column.childNodes[0]) == 'undefined'){
					form.removeChild(check_column);
				}
			});
			
			dragSrcEl = this;
		}
		
		/*
		 * @ToDo Ajaxでデータを保存する
		 */
		saveData(sortData());
	};
	
	this.handleDragEnd = function(e){
		
		[].forEach.call(columns, function(column){
			column.classList.remove('over');
		});
	};
	
	//ゴミ箱
	this.handleDustboxDrop = function(e){
		if(e.preventDefault){
			e.preventDefault();
		}
		
		var dustId = dragSrcEl.childNodes[0].id.replace("column_", "");
		
		//移動してきたノードを先に削除
		form.removeChild(dragSrcEl);
		
		//処理が終わったら、連番の振り直し
		var after_columns = document.querySelectorAll('.column');
		
		addEvent(after_columns, dustbox);
		
		var dustData = {"public" : dustId};
		saveData(dustData);
	};
	
	this.handleDustboxDragOver = function(e){
		if(e.preventDefault){
			e.preventDefault();
		}
	};
	
	addEvent(columns, dustbox);
})();

function addEvent(columns, dustbox){
	[].forEach.call(columns, function(column, i){
		column.id = i + 1;
		column.setAttribute('draggable', 'true');
		column.addEventListener('dragstart', this.handleDragStart, false);
		column.addEventListener('dragenter', this.handleDragEnter, false);
		column.addEventListener('dragover', this.handleDragOver, false);
		column.addEventListener('dragleave', this.handleDragLeave, false);
		column.addEventListener('drop', this.handleDrop, false);
		column.addEventListener('dragend', this.handleDragEnd, false);
	});
	
	//ゴミ箱
	dustbox.setAttribute('draggable', 'true');
	dustbox.addEventListener('dragover', this.handleDustboxDragOver, false);
	dustbox.addEventListener('drop', this.handleDustboxDrop, false);
}

function sortData(){
	var columns = document.querySelectorAll('.column');
	
	var ids = [];	//並び順を格納する配列
	[].forEach.call(columns, function(column){
		var id = column.childNodes[0].id.replace("column_", "");
		ids.push(id);
	});
	
	var data = {"sort" : ids};
	return data;
}

function saveData(data){
	
	//非同期でPOSTする
	var xmlHttpRequest = new XMLHttpRequest();
	xmlHttpRequest.onreadystatechange = function(){
		var READYSTATE_COMPLETED = 4;
		var HTTP_STATUS_OK = 200;
		
		if(this.readyState == READYSTATE_COMPLETED && this.status == HTTP_STATUS_OK){
			//alert(this.responseText);
		}
	};
	
	var submit = document.querySelector('#onsubmit');
	xmlHttpRequest.open('POST', submit.action);
	xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xmlHttpRequest.send(EncodeHTMLForm(data));
}

function EncodeHTMLForm(data){
	var params = [];
	
	for(var name in data){
		var value = data[name];
		var param = encodeURIComponent( name ).replace( /%20/g, '+' )
		+ '=' + encodeURIComponent( value ).replace( /%20/g, '+' );

		params.push( param );
	}
	return params.join( '&' );
}
