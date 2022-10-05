
jQuery(document).ready(function ($) {

    $(".btn_print").click(function () {
        var name = $(this).attr('data-name');
        var date = $(this).attr('data-date');

        $('#certificateDate').text(date);
        $('#certificateName').text(name);
        setTimeout(() => {
            $('#certificate_area').printThis();
        }, 500);

    });


});

