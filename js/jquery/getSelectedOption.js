/**
 * Get form select selected option object
 * return {Object}
 */
jQuery.fn.getSelectedOption = function ()
{
	return $('option:selected', this);
};