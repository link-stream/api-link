$(document).ready(function () {

    $("#login").validate({
        onkeyup: false,
        errorClass: "state-error",
        validClass: "state-success",
        errorElement: "em",
        errorPlacement: function (label, element) {
            label.addClass('invalid-tooltip');
            label.insertAfter(element);
        },
        wrapper: 'div',
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    $("#register").validate({
        onkeyup: false,
        errorClass: "state-error",
        validClass: "state-success",
        errorElement: "em",
        errorPlacement: function (label, element) {
            label.addClass('invalid-tooltip');
            label.insertAfter(element);
        },
        rules: {
            username: {
                required: true,
                remote: {
                    url: urlBase + "app/verify_username",
                    type: "post",
                    data: {
                        field_value: function () {
                            return $("#username").val();
                        },
                        field_name: 'username'
                    }
                }
            },
            email: {
                required: true,
                email: true,
                remote: {
                    url: urlBase + "app/verify_email",
                    type: "post",
                    data: {
                        field_value: function () {
                            return $("#email").val();
                        },
                        field_name: 'email'
                    }
                }
            },
            password: {
                required: true,
                //pwcheck: true,
                //minlength: 8
            },
            repassword: {
                required: true,
                equalTo: "#password"
            }
        }, messages: {
            password: {
                pwcheck: "At least 1 number, 1 lowercase, 1 uppercase letter, 1 special character from @#$%&"
            },
            username: {
                remote: 'User Name Already Register'
            },
            email: {
                remote: 'Email Already Register'
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    $.validator.addMethod("pwcheck", function (value) {
        //var pattern = /^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%&]).*$/;
        var pattern = /^.*(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%&]).*$/;
//                    return /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) // consists of only these
//                            && /[a-z]/.test(value) // has a lowercase letter
//                            && /\d/.test(value) // has a digit
        return pattern.test(value);
    });

    $("#forgot").validate({
        onkeyup: false,
        errorClass: "state-error",
        validClass: "state-success",
        errorElement: "em",
        errorPlacement: function (label, element) {
            label.addClass('invalid-tooltip');
            label.insertAfter(element);
        },
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

});
