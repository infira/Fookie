jQuery.fn.parseForm = function() {
	Utils.Validator.parseForm($(this));
	return this;
}
jQuery.fn.parseHTML = function() {
	Utils.Html.parse($(this));
	return this;
}