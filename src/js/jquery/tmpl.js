var __template = function (template, varsData)
{
	if (typeof varsData === 'string')
	{
		varsData = varsData.deparam();
	}
	var vars = template.matchAll(/\{.+?\}|%.+?%/i);
	if (vars.length > 0)
	{
		var varName,
		    sp,
		    callFunction,
		    val,
		    match;
		for (var i = 0; i < vars.length; i++)
		{
			match              = vars[i];
			varName            = match.substring(1, match.length - 1);
			callFunction       = false;
			callFunctionParams = false;
			if (varName.getMatch(/:/))
			{
				sp             = varName.split(":");
				var funcString = sp.splice(1).join(":").toString().trim();
				if (funcString.indexOf("(") >= 0)
				{
					callFunction       = funcString.substr(0, funcString.indexOf("("));
					funcString         = funcString.substr(funcString.indexOf("(") + 1);
					callFunctionParams = funcString.substr(0, funcString.lastIndexOf(")")).trim().split(",");
				}
				else
				{
					callFunctionParams = [];
					callFunction       = funcString;
				}
				varName = sp[0].toString().trim();
			}
			
			val = undefined;
			if (varName.match(/\w\.\w/i) !== null)
			{
				fields = varName.split(/\./);
				val    = varsData;
				
				$.each(fields, function (i, vName)
				{
					if (typeof val[vName] != "undefined")
					{
						val = val[vName];
					}
					else
					{
						val = "";
						return false;
					}
				});
			}
			else
			{
				if (typeof varsData[varName] != "undefined")
				{
					val = varsData[varName];
				}
			}
			if (val !== undefined)
			{
				if (val === null)
				{
					val = ""
				}
				if (callFunction)
				{
					callFunction = eval(callFunction);
					if (Is.func(callFunction))
					{
						var params = [val];
						if (Is.array(callFunctionParams))
						{
							callFunctionParams.each(function (vv)
							{
								params.push(eval(vv))
							})
						}
						val = callFunction.apply(window, params);
					}
				}
				template = template.replace(match, val);
				template = template.replace(new RegExp(match, "g"), val);
				template = template.replace(new RegExp(match, "i"), val);
			}
		}
	}
	r = r.replace(/tmp-src.*=.*?"/ig, 'src="');
	r = r.replace(/divTR/ig, "tr").replace(/divTD/ig, "td").replace(/divLI/ig, "li");
	r = r.replace(/data-is-checked="1"|data-is-checked='1'|data-is-checked="true"|data-is-checked='true'/g, 'checked="checked"');
	r = r.replace(/data-is-checked="0"|data-is-checked='0'|data-is-checked="false"|data-is-checked='false'/g, '');
	return template;
};

String.prototype.tpl = function (vars)
{
	return __template(this.valueOf().toString(), vars);
};
Number.prototype.tpl = String.prototype.tpl;

String.prototype.handlebars = function (view)
{
	var r = Handlebars.compile(this.valueOf())(view).toString();
	r     = r.replace(/tmp-src.*=.*?"/ig, 'src="');
	r     = r.replace(/divTR/ig, "tr").replace(/divTD/ig, "td").replace(/divLI/ig, "li");
	r     = r.replace(/data-is-checked="1"|data-is-checked='1'|data-is-checked="true"|data-is-checked='true'/g, 'checked="checked"');
	r     = r.replace(/data-is-checked="0"|data-is-checked='0'|data-is-checked="false"|data-is-checked='false'/g, '');
	return r;
}

/**
 * Handlebars
 */
jQuery.fn.handlerbars = function (data)
{
	this.html(this.html().handlebars(data));
	return this;
};
jQuery.fn.getTempl    = function (data, asArray)
{
	var $th  = $(this);
	var tmpl = "";
	if (asArray)
	{
		data.each(function (item)
		{
			tmpl += $th.data("templateString").handlebars(item);
		})
	}
	else
	{
		tmpl += $th.data("templateString").handlebars(data);
	}
	return tmpl;
};
jQuery.fn.fetchTmpl   = function (data, asArray, prepend)
{
	var $th        = $(this);
	var apFunction = (prepend) ? "prepend" : "append";
	if (asArray)
	{
		var index = 0;
		if (Is.array(data))
		{
			data.each(function (item)
			{
				if (typeof item.index == "undefined")
				{
					item.index = index;
					index++;
				}
				$th[apFunction]($th.data("templateString").handlebars(item));
			});
		}
		else
		{
			var item;
			for (var k in data)
			{
				item = data[k];
				if (typeof item.index == "undefined")
				{
					item.index = index;
					index++;
				}
				$th[apFunction]($th.data("templateString").handlebars(item));
			}
		}
	}
	else
	{
		$th[apFunction]($th.data("templateString").handlebars(data));
	}
	return this;
};
jQuery.fn.prependTmpl = function (name, data, asArray)
{
	var $th = $(this);
	if (asArray)
	{
		data.each(function (item)
		{
			if (typeof item.index == "undefined")
			{
				item.index = index;
				index++;
			}
			$th.prepend(Template.get(name).handlebars(item));
		})
	}
	else
	{
		$th.prepend(Template.get(name).handlebars(data));
	}
	return this;
};
jQuery.fn.appendTmpl  = function (name, data, asArray)
{
	var $th = $(this);
	if (asArray)
	{
		data.each(function (item)
		{
			if (typeof item.index == "undefined")
			{
				item.index = index;
				index++;
			}
			$th.append(Template.get(name).handlebars(item));
		})
	}
	else
	{
		$th.append(Template.get(name).handlebars(data));
	}
	return this;
};