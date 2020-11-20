/**
 * Populate select
 * @param {Object} obj
 * @param {Object} valueField
 * @param {Object} labelField
 */
jQuery.fn.populate = function (array, valueField, labelField, firstNull, groupOptions)
{
	this.data("relatedData", array);
	if (!Is.array(array) && !array.lnegth > 0)
	{
		return $(this)
	}
	var $me = $(this);
	if (typeof firstNull !== "undefined" && firstNull !== false)
	{
		if (Is.array(firstNull))
		{
			if (firstNull === true)
			{
				firstNull = "---";
			}
			$me.append($('<option selected="selected" />').val(firstNull[0]).text(firstNull[1]));
		}
		else
		{

		}
	}
	var label, value, callFunction;
	var makeLabel = function (valueObject, labelField)
	{
		if (Is.func(labelField))
		{
			label = labelField(valueObject);
		}
		else
		{
			var templateLabelFields = labelField.matchAll(/\{.+?\}/);
			if (templateLabelFields.length > 0)
			{
				label = labelField;
				$.each(templateLabelFields, function (il, labelTemplateName)
				{
					sp = labelTemplateName.replace(/\{|\}/g, "").split(":");
					labelName = sp[0];
					callFunction = sp[1];
					if (typeof valueObject[labelName] != "undefined")
					{
						val = valueObject[labelName];
					}
					else
					{
						val = labelTemplateName;
					}

					if (callFunction)
					{
						callFunction = eval(callFunction);
						val = callFunction(val)
					}
					label = label.replace(labelTemplateName, val);
				})
			}
			else
			{
				label = valueObject[labelField]
			}
		}
		return label;
	}
	jQuery.each(array, function (index, item)
	{
		if (typeof valueField != "string")
		{
			value = item[valueField[0]];
		}
		else
		{
			value = item[valueField];
		}
		var mainLabel = makeLabel(item, labelField);
		if (Is.array(value))
		{
			var doGroup = true;
			if (typeof valueField[3] != "undefined" && valueField[3] == true && value.length <= 0)
			{
				doGroup = false
			}
			if (doGroup)
			{
				var optionsHTML = '';
				jQuery.each(value, function (i, subItem)
				{
					optionsHTML += '<option data-parent-field="' + valueField[0] + '" data-parent-key="' + index + '" data-key="' + i + '" value="' + subItem[valueField[1]] + '">' + makeLabel(subItem, valueField[2]) + '</option>';
				});
				var append = true;
				if (groupOptions)
				{
					var $group = $me.find("[label='" + mainLabel + "']");
					if ($group.length > 0)
					{
						$group.append(optionsHTML);
						append = false;
					}
				}
				if (append)
				{
					$me.append('<optgroup data-group-index="' + index + '" label="' + mainLabel + '">' + optionsHTML + '</optgroup>');
				}
			}
		}
		else
		{
			if (typeof groupOptions != "undefined")
			{
				groupLabel = makeLabel(item, groupOptions);
				var $group = $me.find("optgroup[label='" + groupLabel + "']");
				if ($group.length <= 0)
				{
					$me.append('<optgroup data-group-index="' + index + '" label="' + groupLabel + '" />');
				}
				$group.append($('<option data-key="' + index + '" />').val(value).text(mainLabel))
			}
			else
			{
				$me.append($('<option data-key="' + index + '" />').val(value).text(mainLabel));
			}
		}
	});
	return this;
};