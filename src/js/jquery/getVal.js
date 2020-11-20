//depend:jquery/isFormEl
/**
 * Get html elemetn value or html
 */
jQuery.fn.getVal = function ()
{
	var $me = this;
	if ($me.is("[data-field]") && $me.is("[data-value]"))
	{
		return $me.data("value");
	}
	else if ($me.is("input[type='checkbox']") || $me.is("input[type='radio']"))
	{
		if ($me.attr("data-getval"))
		{
			return ($me.is(":checked")) ? $me.val() : 0;
		}
		else
		{
			return ($me.is(":checked")) ? 1 : 0;
		}
	}
	else if ($me.isFormEl() || $me.data("force-val"))
	{
		return $me.val();
	}
	else
	{
		return $me.html();
	}
};