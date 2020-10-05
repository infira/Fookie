//depend:jquery/setVal,jquery/getField,js/Is
/**
 * Set Values
 * Returns select,input,textarea,data-field alues
 * @return json
 */
jQuery.fn.setValues = function (object, voidFields)
{
	if (Is.string(voidFields) && voidFields.length > 0)
	{
		voidFields = voidFields.split(",");
	}
	for (i in object)
	{
		if (Is.array(voidFields))
		{
			if (!i.inArray(voidFields))
			{
				this.getField(i).setVal(object[i]);
			}
		}
		else
		{
			this.getField(i).setVal(object[i]);
		}
	}
	return this;
};