var main = (function ($, document, window, undefined) {

    var app = {

    };

    var main = {
        init: function(){
            $(document).ready(function(){
                $(document).foundation();
            });
        }
    };

    main.init();

    return app;

})(jQuery.noConflict(), document, window);