//depend:jquery/getVal,jquery/deparam
jQuery.fn.getFieldValues = function (rowSelector)
{
	var $th = $(this);
	if (rowSelector)
	{
		var d = [];
		$th.find(rowSelector).each(function ()
		{
			d.push($(this).getFieldValues());
		});
		return d;
	}
	else
	{
		var valuesString = [];
		var $formItems = $th.find("select,input,textarea,[data-field]");
		if ($th.attr("data-field") && $th.attr("data-value"))
		{
			valuesString.push($th.attr("data-field") + "=" + $th.attr("data-value"));
		}
		$formItems.each(function ()
		{
			var $el = $(this);
			var name;
			if ($el.attr("data-field") && $el.attr("name") && $el.data("get-field-values"))
			{
				name = $el.attr($el.data("get-field-values"));
			}
			else if ($el.attr("data-field"))
			{
				name = $el.attr("data-field");
			}
			else
			{
				name = $el.attr("name");
			}
			if (name)
			{
				if ($el.is("input[type='checkbox']") || $el.is("input[type='radio']"))
				{
					if ($el.attr("data-exists"))
					{
						valuesString.push(name + "=" + ($el.is(":checked") ? 1 : 0));
					}
					else
					{
						if ($el.is(":checked"))
						{
							valuesString.push(name + "=" + $el.val());
						}
					}
				}
				else
				{
					var val = $el.getVal();
					if (Is.string(val))
					{
						val = val.replace(/&/g, "[[--AMP--]]");
						val = val.replace(/\+/g, "[[--PLUS--]]");
					}
					valuesString.push(name + "=" + val);
				}
			}
		});
		$th.find("[data-field-radio-group]").each(function ()
		{
			var fieldName = $(this).attr("data-field-radio-group");
			var $checked = $(this).find("[data-group-field='" + fieldName + "']:checked");
			if ($checked.length > 0)
			{
				valuesString.push(fieldName + "=" + $checked.val());
			}
		});
		var d = $.String.deparam(valuesString.join("&"));
		var fixD = function (d)
		{
			for (var i in d)
			{
				if (Is.string(d[i]))
				{
					d[i] = d[i].replace(/\[\[--AMP--\]\]/g, "&");
					d[i] = d[i].replace(/\[\[--PLUS--\]\]/g, "+");
				}
				else if (Is.object(d[i]))
				{
					d[i] = fixD(d[i]);
				}
			}
			return d;
		};
		d = fixD(d);
		return d;
	}
};