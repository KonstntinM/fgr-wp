jQuery(document).ready(function($) {
    // 1. Kill the parent's initialization
    $('.vn-menu > ul').superfish('destroy');

    // 2. Re-initialize with touch-friendly settings
    $('.vn-menu > ul').superfish({
        delay: 300,
        animation: { opacity: 'show' }, // Removed height for mobile stability
        speed: 'fast',
        onBeforeShow: function() {
            // This fix ensures the menu works on modern touch devices
            if (this.is('.sf-js-enabled')) {
                this.parent().addClass('sf-touch-active');
            }
        }
    });
});
