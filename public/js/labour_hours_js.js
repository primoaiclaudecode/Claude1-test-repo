$(document).ready(function() {
  $("#labour_hours_form").on("submit", function () {
    return validation();
  });

  $("#unit_name").on("change", function () {
    if($("#unit_name").val() != '') {
      $("#unit_name_span.error_message").html("");
    }
  });
});

function validation() {
  var validation_success = false;
  var d = new Date();
  d.setMonth(d.getMonth() - 12);
  var e = new Date();

  var unit_name_val = $("#unit_name").val();

  if(unit_name_val == '' || unit_name_val == '0' || unit_name_val == null) {
    $("#unit_name").focus();
    $("#unit_name_span.error_message").html("Please select a Unit.");
  }
  else
    validation_success = true;

  return validation_success;
}

//
function addRow()
{
  var tableId = document.getElementById('dataTable');
  var count = parseInt($("#rows_counter").val());
  var noOfRows = tableId.rows.length;
  var ptrRef = noOfRows;

  var tableRow = tableId.insertRow(noOfRows);
  count++;
  $("#rows_counter").val(count);

  // Checkbox
  var chkBoxCell = tableRow.insertCell(0);
  var chkBoxColElem = document.createElement('input');
  chkBoxColElem.type = 'checkbox';
  chkBoxColElem.id = 'chkbox_' + ptrRef;
  chkBoxColElem.className='margin-top-10';
  chkBoxCell.appendChild(chkBoxColElem);

  //Hours text box
  var secondCell = tableRow.insertCell(1);
  var secondColElem = document.createElement('input');
   secondColElem.type = 'text';
   secondColElem.name = 'hours_' + ptrRef;
   secondColElem.id = 'hours_' + ptrRef;
   secondColElem.className='form-control';
   secondCell.appendChild(secondColElem);

  //Dates text box
  var today = new Date();
  var dd = today.getDate();
  var mm = today.getMonth()+1; //January is 0!
  var yyyy = today.getFullYear();
  if(dd < 10) {
	dd = '0' + dd;
  }

  if( mm < 10) {
	mm = '0' + mm;
  }
  var secondCell = tableRow.insertCell(2);
  var secondColElem = document.createElement('input');
   secondColElem.type = 'text';
   secondColElem.name = 'date_' + ptrRef;
   secondColElem.id = 'date_' + ptrRef;
   secondColElem.value = dd + '-' + mm + '-' + yyyy;
   secondColElem.className='form-control datepick';
   secondColElem.readOnly = true;
   secondCell.appendChild(secondColElem);

   //Labour type text box
   var thirdCell = tableRow.insertCell(3);
   var thirdColElem = document.createElement('select');
   thirdColElem.name = 'labour_type_' + ptrRef;
   thirdColElem.id = 'labour_type_' + ptrRef;
   thirdColElem.className = 'form-control';
   thirdCell.appendChild(thirdColElem);
   //
   var hidden_labour_type = document.getElementById('labour_type_hidden');
   document.getElementById('labour_type_' + ptrRef).innerHTML = hidden_labour_type.innerHTML;
   //

   // tabing
   //alert(ptrRef + 1);
   $("#hours_"+parseInt(ptrRef)).focus();
   $("#hours_"+parseInt(ptrRef)).attr('tabindex', (3 * (ptrRef) + 4));
   $("#date_"+parseInt(ptrRef)).attr('tabindex', (3 * (ptrRef) + 5));
   $("#labour_type_"+parseInt(ptrRef)).attr('tabindex', (3 * (ptrRef) + 6));
   $("#add_line").attr('tabindex', (3 * (ptrRef) + 7));
   $("#del_line").attr('tabindex', (3 * (ptrRef) + 8));
   $("#submit_btn").attr('tabindex', (3 * (ptrRef) + 9));
   $("#cancel_btn").attr('tabindex', (3 * (ptrRef) + 10));
}

function setTabbing(ptrRef)
{
   //$("#hours_"+parseInt(ptrRef)).focus();
   $("#hours_"+parseInt(ptrRef)).attr('tabindex', (3 * (ptrRef) + 4));
   $("#date_"+parseInt(ptrRef)).attr('tabindex', (3 * (ptrRef) + 5));
   $("#labour_type_"+parseInt(ptrRef)).attr('tabindex', (3 * (ptrRef) + 6));
   $("#add_line").attr('tabindex', (3 * (ptrRef) + 7));
   $("#del_line").attr('tabindex', (3 * (ptrRef) + 8));
   $("#submit_btn").attr('tabindex', (3 * (ptrRef) + 9));
   $("#cancel_btn").attr('tabindex', (3 * (ptrRef) + 10));
}

function deleteRow()
{
  var i, n, cb, h, d, lt, len, tbl;

  var count = parseInt($("#rows_counter").val());
  tbl = document.getElementById('dataTable');
  len = tbl.rows.length;
  n = 0; // assumes ID numbers start at zero
  for (i = 0; i < len; i++) {
    cb = document.getElementById("chkbox_" + i);
    h = document.getElementById("hours_" + i);
    d = document.getElementById("date_" + i);
	lt = document.getElementById("labour_type_" + i);
    if (cb && h && d && lt) {
      if (cb.checked) {
		if(count<=1) {
			alert("Cannot delete all the rows.");
			break;
		}
        tbl.deleteRow(n);
        count--;
      }
      else {
        cb.id = "chkbox_" + n;
		cb.name = "chkbox_" + n;
        h.id = "hours_" + n;
		h.name = "hours_" + n;
		h.className = "form-control";
        d.id = "date_" + n;
		d.name = "date_" + n;
		d.className = "form-control datepick";
		lt.id = "labour_type_" + n;
		lt.name = "labour_type_" + n;
		lt.className = "form-control";
        ++n;
      }
    }
  }
  $("#rows_counter").val(count);
}

window.onload = function() {
  $('#hidden_unit_name').val($('#unit_name').find(':selected').text());
};