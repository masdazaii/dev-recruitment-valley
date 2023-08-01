(function($){

    $(window).on('keydown', function(event) {
        if (event.ctrlKey || event.metaKey) {
            switch (String.fromCharCode(event.which).toLowerCase()) {
                case 's':
                    event.preventDefault(); 
                    $('input[type="submit"]').trigger('click'); 
                    break; 
            }
        }
    });
 
})(jQuery);