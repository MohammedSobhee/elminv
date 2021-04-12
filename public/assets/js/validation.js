/* eslint indent: ["error", 4] */
(function($, window, document) { // eslint-disable-line

    $(function() {});

    $.validator.addMethod('pwcheck', function(value) {
        return /^(?=.*[A-Z])(?=.*\d)[A-Za-z\d\W]{6,}$/.test(value) // uppercase letter, number, at least 6 characters
    }, 'Please supply a valid password');

    $.validator.addMethod('regex', function(value, element, regexp) {
        return this.optional(element) || regexp.test(value);
    }, 'Please check your input.');

    $.validator.addMethod('exactlength', function(value, element, param) {
        return this.optional(element) || value.length == param;
    }, $.validator.format('Please enter exactly {0} numbers.'));

    $.validator.addMethod('yearforward', function(value, element, param) {
        var currentYear = (new Date()).getFullYear();
        return this.optional(element) ||
            ((parseInt(value,10) >= currentYear) && (parseInt(value,10) <= currentYear + param));
    }, 'Invalid year');


    //
    // Set Defaults
    // --------------------------------------------------------------------------
    $.validator.setDefaults({
        ignore: ':not(:visible)',
        errorElement: 'em',
        rules: {
            first_name: 'required',
            last_name: 'required',
            phone: {
                required: true,
                //regex: /^[0-9]*[1-9][1-9][1-9][0-9]*$/,
                regex: /^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/,
                minlength: 10
            }
            // email: {
            //     required: true,
            //     email: true
            //     //remote: 'email-check.php'
            // },
            // username: {
            //     required: true
            //     //email: true
            //     //remote: 'email-check.php'
            // }
        },
        messages: {
            // email: {
            //     email: 'Supply a valid email.'
            // },
            // username: {
            //     username: 'Supply a valid email.'
            // },
            phone: {
                regex: 'Invalid phone number',
                minlength: 'Minimum of 10 characters.'
            }
        }
    });

    $.validator.messages.required = function (param, input) {
        return input.name.charAt(0).toUpperCase() + input.name.slice(1) + ' is required.';
    };

    //
    // Login
    // --------------------------------------------------------------------------
    $('#form-login').validate({
        rules: {
            password: {
                required: true
            }
            // username: {
            //     required: true
            // },
            // email: {
            //     required: true
            // }
        }
    });

    //
    // Password reset
    // --------------------------------------------------------------------------
    $('#form-password-reset').validate({
        rules: {
            password: {
                required: true,
                pwcheck: true
            },
            password_confirmation: {
                equalTo: '#password'
            }
        },
        messages: {
            password: 'Requires at least one uppercase letter, a number, and 6 characters or more.',
            password_confirmation: 'Passwords do not match.'
        }
    });

    //
    // Create School
    // --------------------------------------------------------------------------
    $('#form-createschool').validate({
        rules: {
            school_name: {
                required: true
            },
            // contract_expiration_date: {
            //     required: true
            // },
            school_type: {
                required: true
            }
            // max_classes: {
            //     required: true,
            //     number: true
            // },
            // students_per_class: {
            //     required: true,
            //     number: true
            // }
        }
    });

    //
    // User Activation
    // --------------------------------------------------------------------------
    $('#form-activation-contactinfo').validate({
        rules: {
            password: {
                required: true,
                pwcheck: true
            },
            password_confirm: {
                equalTo: '#password'
            }
        },
        messages: {
            password: 'Requires at least one uppercase letter, a number, and 6 characters or more.',
            password_confirmation: 'Passwords do not match.'
        }
    });

    $.extend($.validator.messages, {
        required: 'Required',
        number: 'Numbers only'
    });

}(window.jQuery, window, document));
