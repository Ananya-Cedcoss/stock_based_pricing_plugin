jQuery(document).ready( function($) {

	$('body').on('click', '.do-api-refund', function() {
        $(document).ajaxComplete(function() {
			location.reload(true);
        })
	});
});