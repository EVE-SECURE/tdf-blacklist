// JavaScript Document

// Function to build accordion event listeners
function loadAccordion(content,button,message_visible,message_hidden) {
	outputToggle = $(button).observe('click',
		function() {
			if ($(content).visible()) {
				new Effect.BlindUp($(content));
				void($(button).innerHTML=message_hidden);
			} else {
				new Effect.BlindDown($(content));
				void($(button).innerHTML=message_visible);
			}
	});
	new Effect.BlindUp($(content));
	void($(button).innerHTML=message_hidden);
}