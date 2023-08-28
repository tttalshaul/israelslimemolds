jQuery(document).ready(function($) {
    $('.timeline-item').click(function(e) {
        var $this = $(this);
        var month = $this.data('month');
        var count = $this.data('count');

        // Remove existing popups
        $('.timeline-popup').remove();

        // Create popup
        var $popup = $('<div class="timeline-popup"></div>');
        var $popupContent = $('<div class="popup-content"></div>');
        var $popupMonth = $('<div class="popup-month">' + month + '</div>');
        var $popupCount = $('<div class="popup-count">כמה נמצאו: ' + count + '</div>');
        var $popupTip = $('<div class="popup-tip"></div>');

        // Append content to popup
        $popupContent.append($popupMonth, $popupCount);
        $popup.append($popupContent, $popupTip);

        // Append popup to rectangle
        $this.append($popup);

        // Position the popup
        var popupWidth = $popup.outerWidth();
        var popupHeight = $popup.outerHeight();
        var offsetX = popupWidth / 2;
        var offsetY = popupHeight + 6;
        $popup.css({
            top: e.pageY - offsetY,
            left: e.pageX - offsetX,
        });
    });

    $(document).on('click', function(event) {
        if (!$(event.target).closest('.timeline-item').length) {
            $('.timeline-item').find('.timeline-popup').remove();
        }
    });
});
