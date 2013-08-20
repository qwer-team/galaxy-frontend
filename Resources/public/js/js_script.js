$(document).ready(function() {
    if ($('.tab').length) {
        $('.prizes-carusel .tab:first').css('display', 'block');
    }
    $('.prizes-carusel .row:first .hex:first').addClass('active');
    var slider_container = $('.prizes-carusel .row:first');
    var sliders_count = $('.prizes-carusel .row:first .hex').length;
    if (sliders_count <= 5) {
        $('.prizes-carusel .left-arrow').hide();
        $('.prizes-carusel .right-arrow').hide();
    } else {
        $(slider_container).find('.hex').hide();
        for (var i = 0; i < 5; i++) {
            $('.hex', slider_container).eq(i).show().addClass('visible');
        }
    }

    $('.prizes-carusel .left-arrow').click(function() {
        $('.hex', slider_container).eq(-1).clone().insertBefore($(slider_container).find('.hex:first'));
        $('.hex', slider_container).eq(-1).remove();
        var cur_min_index = $('.hex.visible:first').index();
        var cur_max_index = $('.hex.visible:last').index();
        $('.hex.visible.first').removeClass('first');
        $('.hex', slider_container).eq(cur_min_index - 1).show().addClass('visible');
        $('.hex', slider_container).eq(cur_max_index).hide().removeClass('visible');
        $('.hex.visible:first').addClass('first');
        if (!$('.hex.active').hasClass('visible')) {
            $('.hex.active').removeClass('active');
            $('.prizes-carusel .tab').hide();
            $('.prizes-carusel').find('#tab-empty').show();
        }
        return false;
    });
    $('.prizes-carusel .right-arrow').click(function() {
        var cur_min_index = $('.hex.visible:first').index();
        var cur_max_index = $('.hex.visible:last').index();
        $('.hex', slider_container).eq(cur_min_index).hide().removeClass('visible').removeClass('first');
        $('.hex', slider_container).eq(cur_max_index + 1).show().addClass('visible');
        $('.hex.visible:first').addClass('first');
        $('.hex', slider_container).eq(cur_min_index).clone().insertAfter($(slider_container).find('.hex:last'));
        $('.hex', slider_container).eq(cur_min_index).remove();
        if (!$('.hex.active').hasClass('visible')) {
            $('.hex.active').removeClass('active');
            $('.prizes-carusel .tab').hide();
            $('.prizes-carusel').find('#tab-empty').show();
        }
        return false;
    });
    $('.prizes-carusel .row:first a').on('click', function() {
        $('.prizes-carusel .row:first .hex.active').removeClass('active');
        $(this).parent('.hex').addClass('active');
        $('.prizes-carusel .tab').hide();
        $('.prizes-carusel').find($(this).attr('href')).show();
        return false;
    });
    $('#show-helps').on('click', function() {
        $('body').toggleClass('helps-on');
        return false;
    });
    $('.hex.sub-active').on('click', function() {
        $('.hex.sub-active.active').removeClass('active');
        $(this).addClass('active');
        return false;
    });

    /* new */
    $('body > .fliper').css('min-height', $(window).height());

    if ($('#draggable').length)
        $('#draggable').draggable();

    $('.coordinat input').on('focus', function() {
        $(this).parent().addClass('focus');
    });
    $('.coordinat input').on('blur', function() {
        $(this).parent().removeClass('focus');
    });
    $('.coordinat-submit').on('click', function() {
        $(this).parent('.coordinats-selection').toggleClass('active');
        return false;
    });
    $('#slider').slider({
        change: function(event, ui) {
            var c_val = $('#slider').slider("value");
            $('.mark.bank .transfer-count').html('+' + c_val);
            $('.mark.bank .transfer-count').attr("value", c_val);
        },
        slide: function(event, ui) {
            var c_val = $('#slider').slider("value");
            $('.mark.bank .transfer-count').html('+' + c_val);
            $('.mark.bank .transfer-count').attr("value", c_val);
        }
    });
    $('.mark.price span').on('click', function() {
        if (!$(this).parent().hasClass("active")) {
            $('#slider').slider( "value", 0 );
            var offs_left = $(this).offset().left;
            $('.price-slider').css('left', $(this).width() + 10 + offs_left);
            $('#slider').slider("option", "min", 0);
            $('#slider').slider("option", "max", parseInt(del_spaces($(this).html())));
            $(this).parent().addClass('active');
            $('.mark.bank .transfer-count').css('left', $('.mark.bank span').offset().left + $('.mark.bank span').width() + 18);
        } else {
            $(this).parent().removeClass('active');
            var value = $('.mark.bank .transfer-count').attr("value");
            var data = {
                value: parseInt(value),
                from: 1,
                to: 5
            };
            if (parseInt(value) > 0) {
                $.ajax({
                    type: "POST",
                    url: "/store/transfer_funds",
                    data: data,
                    dataType: "json",
                    success: function(data) {
                        $("#active").html(data.user.fundsInfo.active);
                        $('.mark.bank .transfer-count').html('+' + value);
                        $('.mark.bank .transfer-count').fadeToggle(300).delay(500).fadeToggle(300);
                    }
                });
            } else
                alert("Значение должно быть > 0");
        }
        $('.price-slider').fadeToggle(300);
        $('.mark.bank .transfer-count').fadeToggle(300);
        return false;
    });

    /* shop */
    $('.shop-item input[type="submit"]').mousedown(function() {
        $(this).prev('.num-fl').addClass('active');
    });
    $('.shop-item input[type="submit"]').mouseup(function() {
        $(this).prev('.num-fl').removeClass('active');
    });
    $('.shop-item .count-min').click(function() {
        var cur = parseInt(del_spaces($(this).parent().find('span').html())) - 1;
        if (cur >= 0)
            $(this).parent().find('span').html(cur);
        return false;
    });
    $('.shop-item .counter-plus').click(function() {
        var cur = parseInt(del_spaces($(this).parent().find('span').html())) + 1;
        $(this).parent().find('span').html(cur);
        return false;
    });
});
function del_spaces(str)
{
    str = str.replace(/\s/g, '');
    return str;
}