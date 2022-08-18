function copyUrl(a){
    var b=document.createElement("textarea");
    document.getElementsByTagName("body")[0].appendChild(b);
    b.value=a;
    b.select();
    document.execCommand("copy");
    b.parentNode.removeChild(b);
    alert("URLをクリップボードにコピーしました。");
}