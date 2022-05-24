function toggleSidebar () {
    $.ajax({
        type: "POST",
        url: '/profile-settings/toggle-sidebar',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success: function () {
            if ($("#container").hasClass("sidebar-closed")) {
                $('#main-content').css({
                    'margin-left': '210px'
                });
                $('#sidebar > ul').show();
                $('#sidebar').css({
                    'margin-left': '0'
                });
                $("#container").removeClass("sidebar-closed");
                $('#toggle_sidebar input').prop('checked', true);
            } else {
                $('#main-content').css({
                    'margin-left': '0px'
                });
                $('#sidebar').css({
                    'margin-left': '-210px'
                });
                $('#sidebar > ul').hide();
                $("#container").addClass("sidebar-closed");
                $('#toggle_sidebar input').prop('checked', false);
            }
        },
    });
}

/*---LEFT BAR ACCORDION----*/
$(function() {
    $('#nav-accordion').dcAccordion({
        eventType: 'click',
        autoClose: true,
        saveState: true,
        disableLink: true,
        speed: 'slow',
        showCount: false,
        autoExpand: true,
//        cookie: 'dcjq-accordion-1',
        classExpand: 'dcjq-current-parent'
    });
});

// right slidebar
$(function(){
 $.slidebars();
});

var Script = function () {

//    sidebar dropdown menu auto scrolling

    jQuery('#sidebar .sub-menu > a').click(function () {
        var o = ($(this).offset());
        diff = 250 - o.top;
        if(diff>0)
            $("#sidebar").scrollTo("-="+Math.abs(diff),500);
        else
            $("#sidebar").scrollTo("+="+Math.abs(diff),500);
    });

//    sidebar toggle

    $(function() {
        function responsiveView() {
            var wSize = $(window).width();
            if (wSize <= 940) {
                // $('#container').addClass('sidebar-close');
                // $('#sidebar > ul').hide();
                $('#sidebar').addClass('mobile');
            }

            if (wSize > 940) {
                // $('#container').removeClass('sidebar-close');
                // $('#sidebar > ul').show();
                $('#sidebar').removeClass('mobile');
            }
        }
        $(window).on('load', responsiveView);
        $(window).on('resize', responsiveView);
    });

    $('.toggle-sidebar').click(function () {
        toggleSidebar();
    });

    $('.toggle-sidebar-mobile, .toggle-mobile-menu').click(function () {
        $('#sidebar').toggleClass('active');
    });
    
// custom scrollbar
    $("#sidebar").niceScroll({styler:"fb",cursorcolor:"#e8403f", cursorwidth: '3', cursorborderradius: '10px', background: '#404040', spacebarenabled:false, cursorborder: ''});

    $("html").niceScroll({styler:"fb",cursorcolor:"#e8403f", cursorwidth: '6', cursorborderradius: '10px', background: '#404040', spacebarenabled:false,  cursorborder: '', zindex: '1000'});

// widget tools

    jQuery('.panel .tools .fa-chevron-down').click(function () {
        var el = jQuery(this).parents(".panel").children(".panel-body");
        if (jQuery(this).hasClass("fa-chevron-down")) {
            jQuery(this).removeClass("fa-chevron-down").addClass("fa-chevron-up");
            el.slideUp(200);
        } else {
            jQuery(this).removeClass("fa-chevron-up").addClass("fa-chevron-down");
            el.slideDown(200);
        }
    });

    jQuery('.panel .tools .fa-times').click(function () {
        jQuery(this).parents(".panel").parent().remove();
    });


//    tool tips

    $('.tooltips').tooltip();

//    popovers

    $('.popovers').popover();



// custom bar chart

    if ($(".custom-bar-chart")) {
        $(".bar").each(function () {
            var i = $(this).find(".value").html();
            $(this).find(".value").html("");
            $(this).find(".value").animate({
                height: i
            }, 2000)
        })
    }






}();

function currentDate()
{
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!

    var yyyy = today.getFullYear();
    if(dd < 10)
    {
        dd = '0' + dd;
    }
    if(mm < 10)
    {
        mm = '0' + mm;
    }
    var today = dd + '_' + mm + '_' + yyyy;
    return today;
}

$(document).ready(function() {
    //When checkboxes checked/unchecked, toggle background color
    $('.form-group').on('click','input[type=checkbox]',function() {
        $(this).closest('.checkbox-inline, .checkbox').toggleClass('checked');
    });
});

function isTextSelected(input){
   var startPos = input.selectionStart;
   var endPos = input.selectionEnd;
   var doc = document.selection;

   if(doc && doc.createRange().text.length != 0){
      return true;
   }else if (!doc && input.value.substring(startPos,endPos).length != 0){
      return true;
   }
   return false;
}

$(document).ready(function () {
    let favJson = $('#favourites-menu-json').attr('favourites')
    favJson = JSON.parse(favJson)
    getSidebarFavs(favJson);
    $('.fav-sidebar-icon').on('click', function () {
        let thisV = $(this)
        let link = $(this).attr('link');
        let data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            link: link,
            position: 1
        }
        let url = '/profile-settings/user-menu/add';
        let success = function () {
            thisV.addClass('active')
            getLinks()
        }
        if($(this).hasClass('active')){
            url = '/profile-settings/user-menu/delete';
            data.id = $(this).attr('favId');
            success = function () {
                thisV.removeClass('active')
                getLinks()
            }
        }
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: success,
            error: function (jqXHR) {
                $('#error_message').text(jqXHR.responseJSON.errorMessage);
                $('#error_message').removeClass('hidden');
            }
        });
    });
})

function getLinks() {
    clearError();

    $.ajax({
        type: "GET",
        url: '/profile-settings/user-menu/',
        data: {},
        success: function (data) {
            $('#link').val('');

            // Update menu links list
            $('#user_menu').empty();
            $('.dropdown-menu.favourite-menu').empty();
            $('#link option').prop('disabled', false).removeClass('added');

            $.each(data, function (index, menuLink) {
                $('#link').find('option[value="' + menuLink.link + '"]').prop('disabled', true).addClass('added');

                $('#user_menu')
                    .append(
                        $('<li />')
                            .addClass('list-group-item')
                            .attr('id', menuLink.id)
                            .append(
                                $('<span />')
                                    .addClass('label label-success link-position')
                                    .text(menuLink.position)
                            )
                            .append(
                                $('<span />')
                                    .addClass('link-title')
                                    .text(menuLink.title)
                            )
                            .append(
                                $('<i />').addClass('fa fa-trash delete-link')
                            )
                    )

                $('.dropdown-menu.favourite-menu')
                    .append(
                        $('<li />')
                            .append(
                                $('<a />')
                                    .attr('href', menuLink.link)
                                    .text(menuLink.title)
                            )
                    )
            });

            // Update positions dropdown
            $('#position').empty();

            for (var i = 1; i <= data.length + 1; i++) {
                $('#position')
                    .append(
                        $('<option />')
                            .val(i)
                            .text(i)
                    )
            }

            $('#position').val(i - 1);

            // Update new positions dropdown
            $('#new_position').empty();

            for (var i = 1; i <= data.length; i++) {
                $('#new_position')
                    .append(
                        $('<option />')
                            .val(i)
                            .text(i)
                    )
            }
            console.log(data);
            getSidebarFavs(data);
        },
        error: function (jqXHR) {
            $('#error_message').text(jqXHR.responseJSON.errorMessage);
            $('#error_message').removeClass('hidden');
        }
    });
};

function getSidebarFavs(menuLinks) {
    let baseUrl = window.location.protocol + "//" + window.location.host + "/";
    $('.sidebar-menu .sub-menu .sub a').each(function () {
        let href = $(this).attr('href')
        let link = "/" + href.split(baseUrl)[1]
        let favId = '';
        let active = '';
        menuLinks.forEach(function (item) {
            if (item.link === link) {
                active = 'active';
                favId = item.id
            }
        })
        if ($(this).prev().hasClass('fav-sidebar-icon')) {
            if (active === '') {
                $(this).prev().removeClass('active')
            } else {

                $(this).prev().addClass('active')
                $(this).prev().attr('favId', favId)
            }
        } else {
            $(this).parent().prepend('<i class="fa fa-star fav-sidebar-icon ' + active + '" link="' + link + '" favId="' + favId + '"></i>')
        }

    })
}
