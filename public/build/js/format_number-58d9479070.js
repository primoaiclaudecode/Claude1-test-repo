function addCommas(nStr) {
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function round(value, exp) {
    if (typeof exp === 'undefined' || +exp === 0)
    return Math.round(value);

    value = +value;
    exp = +exp;

    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0))
    return NaN;

    // Shift
    value = value.toString().split('e');
    value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp)));

    // Shift back
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp));
}

$(document).ready(function () {
    // Prevent input wrong characters for currency fields
    $(document).on('keydown', '.currencyFields', function(e) {
        var key = e.key;
        var value = e.target.value.substr(0, this.selectionStart) + e.target.value.substr(this.selectionEnd);

        // Prevent second -
        if (key === '-' && /[-]/.test(value)) {
            return false;
        }

        // Prevent - in the middle
        if (key === '-' && this.selectionStart !== 0) {
            return false;
        }

        // Prevent second .
        if (key === '.' && /[\.]/.test(value)) {
            return false;
        }

        // Key is only 1 symbol and key is digit/minus/dot
        var isAllowedCharacter = /[-, \., \d]/.test(key);

        if (key.length === 1 && !isAllowedCharacter) {
            return false;
        }

        return true;
    });
    
    // Change non numeric values to 0
    $(document).on('change', '.currencyFields', function(e) {
        var value = e.target.value;

        if (value === '-' || value === '.' || value === '-.' || value === 'NaN') {
            $(this).val('0.0')
        }
    });

    // Prevent cut/copy/past for currency fields
    $(document).on("cut copy paste", '.currencyFields', function (e) {
        e.preventDefault();
    });
});