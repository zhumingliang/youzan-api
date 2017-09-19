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
            //$("#dataLoad").css('display', 'block');
        },
        success: function (data, response, status) {
            var ret_code=data['ret_code'];
           if (ret_code==0){
               //系统出错
               //$("#group_success").css('display', 'none');
               //$("#group_fail").css('display', 'none');
               $("#sys_error_msg").html(data['msg']);
           }else
           if (ret_code==1){

           }else
           if (ret_code==1){

           }else
           if (ret_code==4){
               //access_token过期
               $("#url").attr("href",data['url']);
               $("#url").text(data['url'].substr(0,50)+"............");

           }
            $("#ReturnModal").modal();

        },
    });

});


