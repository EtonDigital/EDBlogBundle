$(document).ready(function() {
    $("[data-ed-autocomplete]").each(function () {
        var obj = $(this);
        var source = obj.attr('data-source');

        obj.autocomplete({
            serviceUrl: source,
            deferRequestBy: 50,
            minChars: 3,
            containerClass: 'dropdown-menu'
        });
    });
});