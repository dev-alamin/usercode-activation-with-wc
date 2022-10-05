// User reg code submit button 
const urCodeSubmitBtn = document.getElementById("urcode_submitbtn");
const urInputField = document.getElementById("ursubmit_input");



if (urCodeSubmitBtn) {
    urCodeSubmitBtn.onclick = (e) => {

        if (urInputField.value == "") {
            e.preventDefault();
            Swal.fire(
                'Error!',
                'Please input your code',
                'error'
            )
        }

        if (urInputField.value.length > 11 || urInputField.value.length < 11 && urInputField.value != "") {
            e.preventDefault();
            Swal.fire(
                'Error!',
                'Opps! Looks like this is not a valid key.',
                'error'
            )
        }
    }
}


/**
 * Update user input in my-account page
 */
jQuery(document).ready(function ($) {
    jQuery('#bbg_user_input_form').submit(ajaxSubmit);
    var adminUrl = ''
    function ajaxSubmit() {
        var bbgUserInputFormData = jQuery(this).serialize();
        jQuery.ajax({
            type: "POST",
            url: "/wp/wp-admin/admin-ajax.php",
            data: bbgUserInputFormData,
            success: function (data) {
                jQuery("#feedback").html(data);
            }
        });

        return false;
    }
});



jQuery(function ($) {
    $("#awesome_form").validate({

        submitHandler: function () {
            document.getElementById("loader").style.display = 'inline-block';


            var url = action_url_ajax.ajax_url;

            var form = $("#awesome_form");

            $.ajax({
                url: url,
                data: form.serialize() + '&action=' + 'update_bbg_user_code',
                type: 'post',
                success: function (data) {
                    document.getElementById("loader").style.display = 'none';
                    urInputField.value = '';

                    $("#awesome_form_message").html(data);
                    setTimeout(() => {
                        const info = $(".notice-info");
                        const success = $(".notice-success");
                        const error = $(".notice-error");
                        const key = $(".notice-key");

                        if (info.attr('id') === "notice-info") {
                            Swal.fire(
                                'Error!',
                                'This code is alreay used!',
                                'error'
                            )
                        }
                        if (success.attr('id') === "notice-success") {
                            Swal.fire(
                                'Success!',
                                'Awesome! You have activated a new Bear.',
                                'success'
                            )
                            setTimeout(() => {
                                location.reload();
                            }, 2000);

                        }
                        if (key.attr('id') === "notice-key") {
                            Swal.fire(
                                'Error!',
                                'Please input your registration key',
                                'error'
                            )
                        }
                        if (error.attr('id') === "notice-error") {
                            Swal.fire(
                                'Error!',
                                'Something went wrong. Please try again later.',
                                'error'
                            )
                        }

                    }, 100);
                }
            });
        }
    });
});

