var supervisor = document.getElementById('supervisor');
var invoiceDate = document.getElementById('sheet_receipt_dates');
var invoiceNumber = document.getElementById('reference_number');
var purchaseDetails = document.getElementById('purchase_details');

function checkInputData() {
	var unit_name_val = $("#unit_name").val();
    var passedValidation = false;
	var d = new Date();
	d.setMonth(d.getMonth() - 12);
	var e = new Date();
	
	if(unit_name_val == '0') {
		$("#unit_name").addClass("errorfield");
		$("#unit_name").focus();
		$("#unit_name_span.error_message").html("Please select a Unit.");
	} else if($("#sheet_receipt_dates").datepicker('getDate') < d || ($("#sheet_receipt_dates").datepicker('getDate') > e)) {
		alert("Receipt Date cannot be in the future or > 1 year in the past.");
		$("#sheet_receipt_dates").focus();
		return false;		
	} else {		
		$("#unit_name").removeClass("errorfield");
		$("#unit_name_span.error_message").html("");
		passedValidation = true;
	}
	if(passedValidation) return validate_tax_rate();
    return passedValidation;
}

function validate_tax_rate() {
	var table=document.getElementById('dataTable');
	var rowCount=table.rows.length;
	
	// check of previous net ext, goods and tax rate [ Starts ]
		if(document.getElementById("net_ext_"+parseInt(rowCount)).value == '0') {
			alert('Please select a net ext');
			$("#net_ext_"+parseInt(rowCount)).focus();
			return false;
		}		
	
		if(document.getElementById("goods_"+parseInt(rowCount)).value == '' || document.getElementById("goods_"+parseInt(rowCount)).value == 0 || document.getElementById("goods_"+parseInt(rowCount)).value == '0.00') {
			alert('Please enter a value for goods');
			$("#goods_"+parseInt(rowCount)).focus();
			return false;
		}		
		
		if(document.getElementById("tax_rate_"+parseInt(rowCount)).value == '') {
			alert('Please select a tax rate');
			$("#tax_rate_"+parseInt(rowCount)).focus();
			return false;
		}		
	// check of previous net ext, goods and tax rate [ Ends ]
}

$(document).ready(function() {
    $("#cash_purchase").on("submit", function () {
        return checkInputData();
    });
	
    $("#unit_name").on("change", function () {
		var unit_name_val = $("#unit_name").val();
		if(unit_name_val != '0') {
			$("#unit_name").removeClass("errorfield");
			$("#unit_name_span.error_message").html("");
		}        
    });
	
});

// Net Ext Calculations [ Starts ]
function addRow(tableID) {
	var table=document.getElementById(tableID);
	var rowCount=table.rows.length;
	
	// check of previous net ext, goods and tax rate [ Starts ]
	if(document.getElementById("net_ext_"+parseInt(rowCount)).value == '0') {
		alert('Please select a net ext');
		$("#net_ext_"+parseInt(rowCount)).focus();
		return false;
	}		

	if(document.getElementById("goods_"+parseInt(rowCount)).value == '' || document.getElementById("goods_"+parseInt(rowCount)).value == 0 || document.getElementById("goods_"+parseInt(rowCount)).value == '0.00') {
		alert('Please enter a value for goods');
		$("#goods_"+parseInt(rowCount)).focus();
		return false;
	}		

	//console.log(document.getElementById("tax_rate_"+parseInt(rowCount)).value);
        if(document.getElementById("tax_rate_"+parseInt(rowCount)).value == '') {
		alert('Please select a tax rate');
		$("#tax_rate_"+parseInt(rowCount)).focus();
		return false;
	}		
	// check of previous net ext, goods and tax rate [ Ends ]
	
	var row=table.insertRow(rowCount);
	var colCount=table.rows[0].cells.length;
	
	for(var i=0;i<colCount;i++) {		
		var newcell=row.insertCell(i);
		newcell.innerHTML=table.rows[0].cells[i].innerHTML;
                //console.log(newcell.childNodes[0].type)
		switch(newcell.childNodes[0].type) {
                    case undefined:
                             //console.log(newcell.childNodes[0]);
                            if(i == 1) {
					newcell.childNodes[0].childNodes[1].id="goods_"+parseInt(rowCount + 1);
					newcell.childNodes[0].childNodes[1].name="goods_"+parseInt(rowCount + 1);
//					newcell.childNodes[0].childNodes[1].id="vat_"+parseInt(rowCount + 1);
//					newcell.childNodes[0].childNodes[1].name="vat_"+parseInt(rowCount + 1);
//					newcell.childNodes[0].childNodes[1].id="gross_"+parseInt(rowCount + 1);
//					newcell.childNodes[0].childNodes[1].name="gross_"+parseInt(rowCount + 1);
				}
                                if(i == 3) {
					newcell.childNodes[0].childNodes[1].id="vat_"+parseInt(rowCount + 1);
					newcell.childNodes[0].childNodes[1].name="vat_"+parseInt(rowCount + 1);
				}
				
				if(i == 4) {
					newcell.childNodes[0].childNodes[1].id="gross_"+parseInt(rowCount + 1);
					newcell.childNodes[0].childNodes[1].name="gross_"+parseInt(rowCount + 1);
					$("#rows_counter").val(rowCount + 1);
					$("#maximum_rows").val(rowCount);
				}
				newcell.childNodes[0].childNodes[1].value="";
                        break;
			case"text":
				if(i == 1) {
					newcell.childNodes[0].id="goods_"+parseInt(rowCount + 1);
					newcell.childNodes[0].name="goods_"+parseInt(rowCount + 1);
				}
				
				if(i == 3) {
					newcell.childNodes[0].id="vat_"+parseInt(rowCount + 1);
					newcell.childNodes[0].name="vat_"+parseInt(rowCount + 1);
				}
				
				if(i == 4) {
					newcell.childNodes[0].id="gross_"+parseInt(rowCount + 1);
					newcell.childNodes[0].name="gross_"+parseInt(rowCount + 1);
					$("#rows_counter").val(rowCount + 1);
					$("#maximum_rows").val(rowCount);
				}
				newcell.childNodes[0].value="";
				break;
			case"checkbox":
				newcell.childNodes[0].checked=false;
				break;
			default:
				if(i == 0) {
					newcell.childNodes[0].id="net_ext_"+parseInt(rowCount + 1);
					newcell.childNodes[0].name="net_ext_"+parseInt(rowCount + 1);
				}
				if(i == 2) {
					newcell.childNodes[0].id="tax_rate_"+parseInt(rowCount + 1);
					newcell.childNodes[0].name="tax_rate_"+parseInt(rowCount + 1);
				}
				if(i == 5) {
					newcell.className="td_bin_link";
					newcell.childNodes[0].href = "javascript: deleteRow("+parseInt(rowCount + 1)+")";
				}
				newcell.childNodes[0].selectedIndex=0;
				row.id = "tr_"+parseInt(rowCount + 1);
				//alert(rowCount + 1);
				$("#net_ext_"+parseInt(rowCount + 1)).focus();
				$("#net_ext_"+parseInt(rowCount + 1)).attr('tabindex', (3 * (rowCount + 1) + 4));
				$("#goods_"+parseInt(rowCount + 1)).attr('tabindex', (3 * (rowCount + 1) + 5));
				$("#tax_rate_"+parseInt(rowCount + 1)).attr('tabindex', (3 * (rowCount + 1) + 6));
				$("#add_line").attr('tabindex', (3 * (rowCount + 1) + 7));
				$("#del_line").attr('tabindex', (3 * (rowCount + 1) + 8));
				$("#submit_btn").attr('tabindex', (3 * (rowCount + 1) + 9));
				$("#cancel_btn").attr('tabindex', (3 * (rowCount + 1) + 10));
				break;
		}
	}
}

function setTabbing(tableID) {
	var table = document.getElementById(tableID);
	var rowCount = table.rows.length;
	
	for(var i = 0; i < rowCount; i++) {
		$("#net_ext_"+parseInt(i + 1)).attr('tabindex', (3 * (i + 1) + 3));
		$("#goods_"+parseInt(i + 1)).attr('tabindex', (3 * (i + 1) + 4));
		$("#tax_rate_"+parseInt(i + 1)).attr('tabindex', (3 * (i + 1) + 5));
		$("#add_line").attr('tabindex', (3 * rowCount + 6));
		$("#submit_btn").attr('tabindex', (3 * rowCount + 7));
		$("#cancel_btn").attr('tabindex', (3 * rowCount + 8));
	}
}

function deleteRow(val) {
	var i, n, ne, g, tr, v, gr, len, tbl;
	var count = parseInt($("#rows_counter").val());
	try {
		tbl = document.getElementById('dataTable');
		len=tbl.rows.length;
		n = 1;
		if(count<=1) {
			alert("Cannot delete all the rows.");
			return;
		}
		
		$("#tr_" + val).remove();
		//
		  for (i = 1; i < len + 1; i++) {
			ne = document.getElementById("net_ext_" + i);
			g = document.getElementById("goods_" + i);
			tr = document.getElementById("tax_rate_" + i);
			v = document.getElementById("vat_" + i);
			gr = document.getElementById("gross_" + i);
			if (ne && g && tr && v && gr) {
			  
				ne.id = "net_ext_" + n;
				ne.name = "net_ext_" + n;
				g.id = "goods_" + n;
				g.name = "goods_" + n;
				tr.id = "tax_rate_" + n;
				tr.name = "tax_rate_" + n;
				v.id = "vat_" + n;
				v.name = "vat_" + n;
				gr.id = "gross_" + n;
				gr.name = "gross_" + n;
				++n;
			  
			}
		  }
		//
		calculations();
		count--;
		
		$("#rows_counter").val(count);			
		
	} catch(e) {alert(e);}
}

function calculations() {
	
	var goods_total = 0;
	var vat_total = 0;
	var gross_total = 0;
	var table = document.getElementById('dataTable');
	var row_count = $("#rows_counter").attr("value");
	
	if(row_count)
		var total_rows = parseInt(row_count) + 1;
	else
		var total_rows = 1;
	
	var analysis_goods_0 = 0;
	var analysis_goods_9 = 0;
	var analysis_goods_13 = 0;
	var analysis_goods_23 = 0;
        var analysis_goods_exempt = 0;

	var analysis_vat_0 = 0;
	var analysis_vat_9 = 0;
	var analysis_vat_13 = 0;
	var analysis_vat_23 = 0;
        var analysis_vat_exempt = 0;

	var analysis_gross_0 = 0;
	var analysis_gross_9 = 0;
	var analysis_gross_13 = 0;
	var analysis_gross_23 = 0;
        var analysis_gross_exempt = 0;
	
	for(var i = 1; i <= total_rows; i++) {
		
		var gross_negative = false;
		if ($("#goods_" + i).val()) 
                    var goods_val = parseFloat($("#goods_" + i).val().replace(/,/g, ""));
                else
                    var goods_val = 0.00;
                
		if(!isNaN(goods_val)) {
			if(goods_val >= 0)
				$("#goods_" + i).val(addCommas((Math.round((goods_val + 0.00001) * 100) / 100).toFixed(2)));
			else {
				var goods_abs_val = Math.abs(goods_val);
				$("#goods_" + i).val(addCommas('-' + (Math.round((goods_abs_val + 0.00001) * 100) / 100).toFixed(2)));
			}
		}
                
                var tax_rate_first_part = '';
                var tax_rate_last_part = '';
                var tax_rate_str = '';
		var tax_rate_str = $("#tax_rate_" + i).val();
                if(tax_rate_str && tax_rate_str.length == 8) {
                    var split_tax_rate_str = tax_rate_str.split("-");
                    tax_rate_first_part = split_tax_rate_str[0];
                    tax_rate_last_part = split_tax_rate_str[1];
                    var tax_rate_val = parseFloat(tax_rate_last_part);
                } else
                    var tax_rate_val = parseFloat($("#tax_rate_" + i).val());                
		
		var vat_val = goods_val * tax_rate_val / 100;
                
		if(!isNaN(vat_val)) {
			if(vat_val >= 0)
				$("#vat_" + i).val(addCommas((Math.round(vat_val * 100) / 100).toFixed(2)));
			else {
				var vat_abs_val = Math.abs(vat_val);
				$("#vat_" + i).val('-' + (addCommas(Math.round(vat_abs_val * 100) / 100).toFixed(2)));
			}
		}
			//alert(goods_val + vat_val);
		if(goods_val >= 0) {
			var gross_val = goods_val + vat_val + 0.00001;
		} else {
			gross_negative = true;
			var goods_abs_val = Math.abs(goods_val);
			var gross_val = goods_abs_val + Math.abs(vat_val);
		}
		if(!isNaN(gross_val)) {
			if(gross_val >= 0 && gross_negative == false)
				$("#gross_" + i).val(addCommas((Math.round(gross_val * 100) / 100).toFixed(2)));
			else
				$("#gross_" + i).val(addCommas('-' + (Math.round(gross_val * 100) / 100).toFixed(2)));
		}
			
		goods_total += goods_val;
		if(!isNaN(goods_total)) {
			if(goods_total >= 0) {
				$("#goods_total").val(addCommas((Math.round((goods_total + 0.00001) * 100) / 100).toFixed(2)));
				$("#analysis_goods_total").val(addCommas((Math.round((goods_total + 0.00001) * 100) / 100).toFixed(2)));
			} else {
				var goods_total_abs_val = Math.abs(goods_total);				
				$("#goods_total").val(addCommas('-' + (Math.round((goods_total_abs_val + 0.00001) * 100) / 100).toFixed(2)));
				$("#analysis_goods_total").val(addCommas('-' + (Math.round((goods_total_abs_val + 0.00001) * 100) / 100).toFixed(2)));
			}
		}
		
		if(!isNaN(vat_val) && vat_val != 0) {
			var vat_val_temp = vat_val;
			if(vat_val_temp >= 0)
				vat_total += parseFloat((Math.round((vat_val_temp + 0.00001) * 100) / 100).toFixed(2));
			else {
				var vat_val_temp_abs = Math.abs(vat_val_temp);
				vat_total -= parseFloat((Math.round((vat_val_temp_abs + 0.00001) * 100) / 100).toFixed(2));
			}
		}
		
		//vat_total += vat_val;
		if(!isNaN(vat_total)) {
			if(vat_total >= 0) {
				$("#vat_total").val(addCommas((Math.round((vat_total + 0.00001) * 100) / 100).toFixed(2)));
				$("#analysis_vat_total").val(addCommas((Math.round((vat_total + 0.00001) * 100) / 100).toFixed(2)));
			} else {
				var vat_total_abs_val = Math.abs(vat_total);
				$("#vat_total").val(addCommas('-' + (Math.round((vat_total_abs_val + 0.00001) * 100) / 100).toFixed(2)));
				$("#analysis_vat_total").val(addCommas('-' + (Math.round((vat_total_abs_val + 0.00001) * 100) / 100).toFixed(2)));
			}
		}
		
		gross_val = gross_negative == false ? gross_val : '-' + gross_val;
		
		if(!isNaN(gross_val) && gross_val != 0) {
			var gross_val_temp = gross_val;
			if(gross_val_temp >= 0)
				gross_total += parseFloat((Math.round((gross_val_temp + 0.00001) * 100) / 100).toFixed(2));
			else {
				var gross_val_temp_abs = Math.abs(gross_val_temp);
				gross_total -= parseFloat((Math.round((gross_val_temp_abs + 0.00001) * 100) / 100).toFixed(2));
			}
		}
		
		if(!isNaN(gross_total)) {
			if(gross_total >= 0) {
				$("#gross_total").val(addCommas((Math.round(gross_total * 100) / 100).toFixed(2)));
				$("#analysis_gross_total").val(addCommas((Math.round(gross_total * 100) / 100).toFixed(2)));
			} else {
				var gross_total_abs_val = Math.abs(gross_total);
				$("#gross_total").val(addCommas('-' + (Math.round(gross_total_abs_val * 100) / 100).toFixed(2)));
				$("#analysis_gross_total").val(addCommas('-' + (Math.round(gross_total_abs_val * 100) / 100).toFixed(2)));
			}
			//
			
			if(gross_total) {
				$('.div_invoice_total').slideDown("slow");
				$("#invoice_total").html('&euro;' + addCommas((Math.round(gross_total * 100) / 100).toFixed(2)));
			} else
				$('.simple_table_small').slideUp("slow");
			//			
		}
		
		if(tax_rate_first_part != 'exempt' && tax_rate_val == 0)
			analysis_goods_0 += goods_val;
		if(!isNaN(analysis_goods_0))
			$("#analysis_goods_0").val(addCommas((Math.round(analysis_goods_0 * 100) / 100).toFixed(2)));		
		
		if(tax_rate_val == 9)
			analysis_goods_9 += goods_val;
		if(!isNaN(analysis_goods_9))
			$("#analysis_goods_9").val(addCommas((Math.round(analysis_goods_9 * 100) / 100).toFixed(2)));

		if(tax_rate_val == 13.5)
			analysis_goods_13 += goods_val;
		if(!isNaN(analysis_goods_13))
			$("#analysis_goods_13").val(addCommas((Math.round(analysis_goods_13 * 100) / 100).toFixed(2)));

		if(tax_rate_val == 23)
			analysis_goods_23 += goods_val;
		if(!isNaN(analysis_goods_23))
			$("#analysis_goods_23").val(addCommas((Math.round(analysis_goods_23 * 100) / 100).toFixed(2)));
                    
                if(tax_rate_first_part == 'exempt' && tax_rate_last_part == 0) {
			analysis_goods_exempt += goods_val;
                        if(!isNaN(analysis_goods_exempt))
                            $("#analysis_goods_exempt").val(addCommas((Math.round(analysis_goods_exempt * 100) / 100).toFixed(2)));		
                }

		if(tax_rate_val == 0)
			analysis_vat_0 += vat_val;
		if(!isNaN(analysis_vat_0))
			$("#analysis_vat_0").val(addCommas((Math.round(analysis_vat_0 * 100) / 100).toFixed(2)));		
		
		if(tax_rate_val == 9)
			analysis_vat_9 += vat_val;
		if(!isNaN(analysis_vat_9))
			$("#analysis_vat_9").val(addCommas((Math.round(analysis_vat_9 * 100) / 100).toFixed(2)));		

		$("#analysis_vat_13").val('0.00');
		$("#analysis_gross_13").val('0.00');
		
		if(tax_rate_val == 13.5) {
			var analysis_vat_13_temp = vat_val;
			if(analysis_vat_13_temp >= 0)
				analysis_vat_13 += parseFloat((Math.round((analysis_vat_13_temp + 0.00001) * 100) / 100).toFixed(2));
			else {
				var analysis_vat_13_temp_abs = Math.abs(analysis_vat_13_temp);
				analysis_vat_13 -= parseFloat((Math.round((analysis_vat_13_temp_abs + 0.00001) * 100) / 100).toFixed(2));
			}
		}
		
		if(!isNaN(analysis_vat_13)) {
			if(analysis_vat_13 >= 0)
				$("#analysis_vat_13").val(addCommas((Math.round((analysis_vat_13 + 0.00001) * 100) / 100).toFixed(2)));
			else {
				var analysis_vat_13_abs = Math.abs(analysis_vat_13);
				$("#analysis_vat_13").val(addCommas('-' + (Math.round((analysis_vat_13_abs + 0.00001) * 100) / 100).toFixed(2)));
			}
		}

		if(tax_rate_val == 23)
			analysis_vat_23 += vat_val;
		if(!isNaN(analysis_vat_23))
			$("#analysis_vat_23").val(addCommas((Math.round(analysis_vat_23 * 100) / 100).toFixed(2)));
                    
                if(tax_rate_first_part == 'exempt' && tax_rate_last_part == 0) {
			$("#analysis_vat_exempt").val('0.00');		
                }

		if(tax_rate_first_part != 'exempt' && tax_rate_val == 0) {
                    if(tax_rate_val == 0)
                            analysis_gross_0 += parseFloat(gross_val);
                    if(!isNaN(analysis_gross_0))
                            $("#analysis_gross_0").val(addCommas((Math.round(analysis_gross_0 * 100) / 100).toFixed(2)));		
                }
		
		if(tax_rate_val == 9)
			analysis_gross_9 += parseFloat(gross_val);
		if(!isNaN(analysis_gross_9))
			$("#analysis_gross_9").val(addCommas((Math.round(analysis_gross_9 * 100) / 100).toFixed(2)));		

		if(tax_rate_val == 13.5) {
			var analysis_gross_13_temp = gross_val;
			if(analysis_gross_13_temp >= 0)
				analysis_gross_13 += parseFloat((Math.round((analysis_gross_13_temp + 0.00001) * 100) / 100).toFixed(2));
			else {
				var analysis_gross_13_temp_abs = Math.abs(analysis_gross_13_temp);
				analysis_gross_13 -= parseFloat((Math.round((analysis_gross_13_temp_abs + 0.00001) * 100) / 100).toFixed(2));
			}
		}
		
		if(!isNaN(analysis_gross_13)) {
			if(analysis_gross_13 >= 0)
				$("#analysis_gross_13").val(addCommas((Math.round((analysis_gross_13 + 0.00001) * 100) / 100).toFixed(2)));
			else {
				var analysis_gross_13_abs = Math.abs(analysis_gross_13);
				$("#analysis_gross_13").val(addCommas('-' + (Math.round((analysis_gross_13_abs + 0.00001) * 100) / 100).toFixed(2)));
			}
		}

		if(tax_rate_val == 23)
			analysis_gross_23 += parseFloat(gross_val);
		if(!isNaN(analysis_gross_23))
			$("#analysis_gross_23").val(addCommas((Math.round(analysis_gross_23 * 100) / 100).toFixed(2)));
                
                if(tax_rate_first_part == 'exempt' && tax_rate_last_part == 0) {
			analysis_gross_exempt += parseFloat(gross_val);
        		if(!isNaN(analysis_gross_exempt))
                            $("#analysis_gross_exempt").val(addCommas((Math.round(analysis_gross_exempt * 100) / 100).toFixed(2)));
                }

	}
	
}

window.onload = function() {
 	calculations();
}

// Net Ext Calculations [ Ends ]