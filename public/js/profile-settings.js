function addLink() {
    clearError();

    if ($('#link').val() == '') {
        $('#link')
            .after(
                $('<span />')
                    .addClass('error_message')
                    .text('Please select menu item')
            );
        
        return false;
    }
    
    $.ajax({
        type: "POST",
        url: '/profile-settings/user-menu/add',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            link: $('#link').val(),
            position: $('#position').val()
        },
        success: function () {
            getLinks();
        },
        error: function (jqXHR) {
            $('#error_message').text(jqXHR.responseJSON.errorMessage);
            $('#error_message').removeClass('hidden');
        }
    });
};

function deleteLink(id) {
    clearError();

    $.ajax({
        type: "POST",
        url: '/profile-settings/user-menu/delete',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: id,
        },
        success: function () {
            getLinks();
        },
        error: function (jqXHR) {
            $('#error_message').text(jqXHR.responseJSON.errorMessage);
            $('#error_message').removeClass('hidden');
        }
    });
};

function clearError() {
    $('.error_message').remove();
    $('#error_message').addClass('hidden').text('');
};

function editPosition(id, currentPosition) {
    $('#link_id').val(id);
    $('#new_position').val(currentPosition);
    
    $('#edit_position_modal').modal('show');
}

function savePosition() {
    clearError();

    $.ajax({
        type: "POST",
        url: '/profile-settings/user-menu/change-position',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: $('#link_id').val(),
            position: $('#new_position').val()
        },
        success: function () {
            $('#edit_position_modal').modal('hide');
            
            getLinks();
        },
        error: function (jqXHR) {
            $('#error_message').text(jqXHR.responseJSON.errorMessage);
            $('#error_message').removeClass('hidden');
        }
    });
};

$(document).ready(function () {
    getLinks();

    $('#link, #position').on('change', function () {
        clearError();
    });

    $('#add_link_frm').on('submit', function (e) {
        e.preventDefault();

        addLink();
    });
    
    $(document).on('click', '.delete-link', function() {
        var id = $(this).parents('li').attr('id');
        
        deleteLink(id);
    });
    
    $(document).on('click', '.link-position', function () {
        var position = parseInt($(this).text());
        var id = $(this).parents('li').attr('id');

        editPosition(id, position);
    });
    
    $('#save_position').on('click', function() {
        savePosition();
    });
    
    $('#toggle_sidebar').on('change', function (e) {
        e.preventDefault();
        
       toggleSidebar(); 
    });
});
