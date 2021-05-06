jQuery(document).ready(function(){

    !function($) {
        "use strict"
    }(window.jQuery),

    function($) {
        "use strict"

        var Discount = function() {

        }

        Discount.prototype.Init = function() {
            $(document).on('change', 'input[name="form[type]"]', function() {
                var val = $(this).val();
                if (val === "PERCENTAGE") {
                    $('.value-suffix').html('%');
                } else {
                    $('.value-suffix').html('IDR');
                }
            });
        }

        $.Discount = new Discount, $.Discount.Constructor = Discount;
    }(window.jQuery),

    function ($) {
        'use strict'

        $.Discount.Init();
    }(window.jQuery)

})