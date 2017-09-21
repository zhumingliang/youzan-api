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
            var code = data['ret_code'];

            switch (code) {

                case 0:

                    //系统出错
                    $("#group_success").css('display', 'none');
                    $("#group_fail").css('display', 'none');
                    $("#access_token").css('display', 'none');
                    $("#sys_error_msg").html(data['msg']);
                    ;

                    break;

                case 1:

                    $("#sys_fail").css('display', 'none');
                    $("#access_token").css('display', 'none');
                    $("#success_msg").html(data['success_msg']);
                    $("#fail_msg").html(data['error_msg']);
                    ;

                    break;

                case 4:

                    //access_token过期
                    $("#group_success").css('display', 'none');
                    $("#group_fail").css('display', 'none');
                    $("#sys_fail").css('display', 'none');
                    $("#url").attr("href", data['url']);
                    $("#url").text(data['url'].substr(0, 50) + "............");

                    break;

                default:



            }

            $("#excel_id").val("");
            $("#img_id").val("");
            $("#ReturnModal").modal();
        },
    });

});


