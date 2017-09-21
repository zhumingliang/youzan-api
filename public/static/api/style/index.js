$("#up").click(function () {
    /* alert("111");
     var form = new FormData(document.getElementById("goods"));
 */
    var form = new FormData(document.getElementById("goods"));

    $.ajax({
        url: SCOPE['up_url'],
        type: 'POST',
        data: form,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $("#dataLoad").css('display', 'block');
        },
        success: function (data, response, status) {
            $("#dataLoad").css('display', 'none');
            var ret_code = data['ret_code'];
            if (ret_code == 0) {
                //系统出错
                $("#group_success").css('display', 'none');
                $("#group_fail").css('display', 'none');
                $("#access_token").css('display', 'none');
                $("#sys_error_msg").html(data['msg']);
            } else if (ret_code == 1) {
                $("#sys_fail").css('display', 'none');
                $("#access_token").css('display', 'none');
                $("#group_success").html(data['success_msg']);
                $("#group_fail").html(data['error_msg']);

            } else if (ret_code == 4) {
                //access_token过期
                $("#group_success").css('display', 'none');
                $("#group_fail").css('display', 'none');
                $("#sys_fail").css('display', 'none');
                $("#url").attr("href", data['url']);
                $("#url").text(data['url'].substr(0, 50) + "............");

            }
            $("#ReturnModal").modal();

        },
    });

});


