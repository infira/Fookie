//depend:js/function
var TemplateClass = function ()
{
};
TemplateClass.extend(
{
	list: {},
	get: function (name)
	{
		if (typeof this.list[name] == "undefined" && console.error)
		{
			console.error("Template " + name + " not found");
			return false;
		}
		return this.list[name];
	},
	
	set: function (name, template)
	{
		this.list[name] = this.parseTempalteString(template);
	},
	
	parseTempalteString: function (template)
	{
		template = template.replace(/tmp-src.*=.*?"/ig, 'src="');
		template = template.replace(/divTR/ig, "tr").replace(/divTD/ig, "td").replace(/divLI/ig, "li");
		template = template.replace(/data-is-checked="1"|data-is-checked='1'|data-is-checked="true"|data-is-checked='true'/g, 'checked="checked"');
		template = template.replace(/data-is-checked="0"|data-is-checked='0'|data-is-checked="false"|data-is-checked='false'/g, '');
		
		return template;
	},
	
	collect: function ($wrapper)
	{
		var me = this;
		
		$wrapper.find("[data-as-template='true']").reverse().each(function ()
		{
			$(this).data("templateString", me.parseTempalteString($(this).html()));
		})
		$wrapper.find("[data-as-template='true']").html("").removeAttr("data-as-template");
		
		$wrapper.find("[data-template]").each(function ()
		{
			var $th = $(this);
			me.set($th.attr("data-template"), $th.html());
		});
		$wrapper.find("[data-template]").remove();
		
	}
});
var Template = new TemplateClass();
