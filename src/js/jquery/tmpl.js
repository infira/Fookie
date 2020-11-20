String.prototype.handlebars = function (view)
{
	var r = Handlebars.compile(this.valueOf())(view).toString();
	r = r.replace(/tmp-src.*=.*?"/ig, 'src="');
	r = r.replace(/divTR/ig, "tr").replace(/divTD/ig, "td").replace(/divLI/ig, "li");
	r = r.replace(/data-is-checked="1"|data-is-checked='1'|data-is-checked="true"|data-is-checked='true'/g, 'checked="checked"');
	r = r.replace(/data-is-checked="0"|data-is-checked='0'|data-is-checked="false"|data-is-checked='false'/g, '');
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
jQuery.fn.getTempl = function (data, asArray)
{
	var $th = $(this);
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
jQuery.fn.fetchTmpl = function (data, asArray, prepend)
{
	var $th = $(this);
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
jQuery.fn.appendTmpl = function (name, data, asArray)
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