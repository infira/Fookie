//depend:jquery/isFormEl,jquery/tick
/**
 * Set html elemetn value or html
 */
jQuery.fn.setVal = function (val)
{
	var $me = this;
	if ($me.attr("data-setvalue-format"))
	{
		val = $me.attr("data-setvalue-format").replace("%s", val);
	}
	if ($me.is("input[type='checkbox']") || $me.is("input[type='radio']"))
	{
		var check = (val === 1 || val === "1" || val === true) ? true : false
		$me.tick(check)
	}
	else if ($me.isFormEl())
	{
		$me.val(val);
	}
	else
	{
		$me.html(val);
	}
	return $me;
};