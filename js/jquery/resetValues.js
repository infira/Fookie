//depend:jquery/setVal
/**
 * Get Values
 * Returns select,input,textarea,data-field alues
 * @return json
 */
jQuery.fn.resetValues = function (voidFields)
{
	var $th = $(this);
	var $formItems = $th.find("select,input,textarea,[data-field]");
	if (Is.string(voidFields) && voidFields.length > 0)
	{
		voidFields = voidFields.split(",");
	}
	$formItems.each(function ()
	{
		var $el = $(this);
		if (!$el.attr("data-void-reset"))
		{
			var val = (typeof $el.attr("data-reset-value") != "undefined") ? $el.attr("data-reset-value") : "";
			if (Is.array(voidFields))
			{
				if ($el.attr("data-field"))
				{
					if (!$el.attr("data-field").inArray(voidFields))
					{
						$el.setVal(val);
					}
				}
				else
				{
					$el.setVal(val);
				}
			}
			else
			{
				$el.setVal(val);
			}
		}
	})
	return this;
};