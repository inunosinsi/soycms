$(function () {
    $("#restore_from_backup").click(function () {
        AutoSaveObject.restore();
    });

    setInterval(function () {
        AutoSaveObject.save();
    }, 10000);
});

var AutoSaveObject = {
    save: function () {
        //バックアップファイルがある時はバックアップしない
        if ($("#restoration_area").css("display") == "inline") return;

        var title = $('#mail_title').val();
        var overview = $('#mail_overview').val();
        var content = $('#mail_content').val();

        if (title.length || content.length) {
            $.ajax({
                type: "POST",
                url: $("#auto_save_action").val(),
                data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=auto_save&login_id=" + $("#current_login_id").val() + "&title=" + title + "&overview=" + overview + "&content=" + content,
                dataType: 'text',
                success: function (data) {
                    var res = eval("array=" + data);
                    $("input[name=soy2_token]").val(res.soy2_token);

                    //一瞬だけsubmitボタンを押せない様にする
                    $("#submit_button").attr("disabled", true);

                    //バックアップに成功した場合
                    if (res.result) {
                        var now = new Date();
                        var m = now.getMonth() + 1;
                        $("#auto_save_entry_message").html(now.getFullYear() + "-" + m + "-" + now.getDate() + " " + now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds() + " 記事のバックアップを行いました。");
                        //失敗した場合
                    } else {
                        //
                    }

                    //0.5秒後に戻す
                    setTimeout(function () {
                        $("#submit_button").attr("disabled", false);
                    }, 500);
                }
            });
        }
    },

    restore: function () {
        $("#restoration_area").css("display", "none");
        $("#auto_save_entry_message").css("display", "inline");
        $.ajax({
            type: "POST",
            url: $("#restore_action").val(),
            data: "soy2_token=" + $("input[name=soy2_token]").val() + "&mode=load&login_id=" + $("#current_login_id").val(),
            dataType: 'text',
            success: function (data) {
                var res = eval("array=" + data);
                $("input[name=soy2_token]").val(res.soy2_token);

                //一瞬だけsubmitボタンを押せない様にする
                $("#submit_button").attr("disabled", true);

                $("#mail_title").val(res.title);
                $('#mail_overview').val(res.overview);
                $('#mail_content').html(res.content);

                //0.5秒後に戻す
                setTimeout(function () {
                    $("#submit_button").attr("disabled", false);
                }, 500);
            }
        });
    }
};
