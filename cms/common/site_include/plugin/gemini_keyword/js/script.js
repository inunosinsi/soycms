const GeminiKeywordApp = {
	getCandidateCommon : function(v){
		let dl = document.querySelector("#gemini_keyword_list");
		if(dl){
			/** datalistの方のoptionをすべて削除 **/
			while (dl.firstChild) {
				dl.removeChild(dl.firstChild);
			}
		}
		let ds = document.querySelector("#gemini_keyword_site_id");
		if(v.length > 0 && dl && ds){
			let xhr = new XMLHttpRequest();

			xhr.open("POST", "/"+ds.value+"/gemini_keyword.json");
			xhr.setRequestHeader("content-type", "application/x-www-form-urlencoded;charset=UTF-8");
			xhr.send("q="+v);
			
			xhr.onreadystatechange = function(){
				/** 下記で受信完了 **/
			    if(xhr.readyState === 4 && xhr.status === 200){
					let arr = JSON.parse(xhr.responseText);
					console.log(arr);
					if(arr.length > 0){
						for(var i = 0; i < arr.length; i++){
							let opt = document.createElement("option");
							opt.value = arr[i];
							gemini_keyword_list.appendChild(opt);
						}
					}
				}
			}
		}
	},
	getCandidate : function(){
		let v = "";
		let dk = document.querySelector("#gemini_keyword");
		if(dk) v = dk.value.trim();
		GeminiKeywordApp.getCandidateCommon(v);
	},
	getCandidateSpecifyForm : function(id){
		let v = "";
		let dk = document.querySelector("#"+id);
		if(dk) v = dk.value.trim();
		GeminiKeywordApp.getCandidateCommon(v);
	}
};

const gemini_keyword_form = document.querySelector("#gemini_keyword");
if(gemini_keyword_form){
	const gemini_keyword_list = document.querySelector("#gemini_keyword_list");
	if(gemini_keyword_list){
		gemini_keyword_form.addEventListener("keyup", GeminiKeywordApp.getCandidate);
	}
}
