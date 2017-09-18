$("#up").click(function () {
    alert("111");

    $.ajax({
        url: SCOPE['index_url'],
        type: 'POST',
        data: {
            import:$('#input_file').val()
        },
        beforeSend: function () {
            $("#dataLoad").css('display', 'block');
        },
        success: function (data, response, status) {
            alert(data);
        },
    })

});


