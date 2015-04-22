/*	Link Picker Metabox
*
*	Assumes an element with a class of "linkpicker" contains two elemets:
*	1. input - (type of "text") to recieve the url
*	2. input - (type and class of "button") to open the link picker
*/

jQuery(document).ready(function($){
	var clickedEle = "";

	$('body').on('click', '.cmb-type-linkpicker .button', function(event) {
		clickedEle = $(this);
		wpActiveEditor = true;
		wpLink.open();
		return false;
	});

	$('body').on('click', '#wp-link-submit', function(event) {
		var linkAtts = wpLink.getAttrs();
		$(clickedEle).siblings("input[type=text]").val(linkAtts.href);
		wpLink.textarea = $('body');
		wpLink.close();

		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		event.stopPropagation();
		return false;
	});


	$('body').on('click', '#wp-link-cancel, #wp-link-close', function(event) {
		wpLink.textarea = $('body');
		wpLink.close();

		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		event.stopPropagation();
		return false;
	});
});