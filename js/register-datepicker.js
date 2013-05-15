jQuery(document).ready(function($) {
	$(function() {
		var pickerOpts = {
			dateFormat: 'yy-mm-dd'
		};
		$('#acc_begin_date').datepicker(pickerOpts);
	});
});