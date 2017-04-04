/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function get_selected_rows(table_id)
{
	var el_table = document.getElementById(table_id);
	var tbodies = el_table.getElementsByTagName("tbody");
	var rows = tbodies.item(0).getElementsByTagName("tr");

	var list = "";
	for(var i = 0;i<rows.length;i++)
	{
		var row = rows.item(i);
		
		if (row.className=="selected")
		{
			if(list!="")
				list+=",";
			list+=i;
		}	
	}
	return list;
}


function on_change_combo(form_id)
{
	var form = document.getElementById(form_id);
	form.submit();
} 

function on_select_row(table_id, el)
{
	var form_id = "form_" + table_id;
	var events_form = document.getElementById(form_id);
	var buttons = events_form.getElementsByTagName("input");
		
	
	if (el.className=="selected")
		el.className="";
	else
		el.className="selected";

	//comma separated list of row indices...
	var list = get_selected_rows(table_id);

	var hidden_id = "selected_rows_" + table_id;
	var hidden_element = document.getElementById(hidden_id);
	
	hidden_element.value = list;

	for (var i = 0;i<buttons.length;i++)
	{
		var button = buttons.item(i);
		
		if (button.type != "submit")
			continue;

		if (list!="")
		{
			button.disabled = false;
		}
		else
			button.disabled = true;
	}
}

function enable_input(element_id, enabled)
{
	var inp = document.getElementById(element_id);
	inp.disabled = !enabled;
}

function on_click_checkbox(el, fields)
{
	//explode fields....
	var temp = new Array();
	temp = fields.split(',');

	var enabled = el.checked;

	var i;
	for(i = 0;i<temp.length;i++)
		enable_input(temp[i], enabled);

}

function on_submit_events(table_id, hidden_id)
{	
	var list = get_selected_rows(table_id);	
	var el_hidden = document.getElementById(hidden_id);
	el_hidden.value=list;
}