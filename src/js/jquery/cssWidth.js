/**
 * Sett width into css, somehow is diferent to use jquery.width() function
 * @param {String} style
 */
jQuery.fn.cssWidth = function (width)
{
	this.css("width", parseFloat(width) + "px");
	return this;
};