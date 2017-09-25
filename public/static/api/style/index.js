/**
 * 页面加载完成，执行检测token
 */
$(function () {
    var id = getParam('id');
    if (id === null) {
        alert('链接不正确，请在浏览器输入正确链接：http://youzan.partywall.cn:8080/youzan-api/public/index.php/?id=xxxxxxx');
    } else {
        $.ajax({
            url: SCOPE['check_url'],
            type: 'POST',
            dataType: 'json',
            data: {id: id},
            processData: true,
            success: function (data, response, status) {
               if (data['ret_code']===2){
                   alert(data['msg']);

               }else if (data['ret_code']===0){
                   window.location.href=data['url'];
               }
            },
        });
    }

});

/**
 * 上传文件
 */
$("#up").click(function () {
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


/**
 * 获取指定的URL参数值
 * URL:http://www.quwan.com/index?name=tyler
 * 参数：paramName URL参数
 * 调用方法:getParam("name")
 * 返回值:tyler
 */
function getParam(paramName) {
    paramValue = "", isFound = !1;
    if (this.location.search.indexOf("?") == 0 && this.location.search.indexOf("=") > 1) {
        arrSource = unescape(this.location.search).substring(1, this.location.search.length).split("&"), i = 0;
        while (i < arrSource.length && !isFound) arrSource[i].indexOf("=") > 0 && arrSource[i].split("=")[0].toLowerCase() == paramName.toLowerCase() && (paramValue = arrSource[i].split("=")[1], isFound = !0), i++
    }
    return paramValue == "" && (paramValue = null), paramValue
}



