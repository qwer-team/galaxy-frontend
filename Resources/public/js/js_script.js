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



    $('#sliderSafe').slider({
        slide: function(event, ui) {
            var c_val = ui.value;
            var transSafe = $('.mark.price .transfer-count').attr("transActive");
            var newTrans = parseInt(transSafe) + parseInt(c_val);
            var safe = parseInt($("#safe").attr("safe"));
            $('.mark.price .transfer-count').html('+' + newTrans);
            $('.mark.price .transfer-count').attr("transValue", c_val);
            $('#safe').html(safe - c_val);
        }
    });
    $('.mark.bank span').on('click', function(event) {
        if (!$(this).parent().hasClass("active") && $('.mark.price span').parent().hasClass("active"))
        {
            return event.stopPropagation();
        }
        $('.mark.bank .transfer-count:visible').fadeToggle(300);
        $('.mark.price .transfer-count:hidden').fadeToggle(300);
        $('#sliderSafe').slider("value", 0);
        if (!$(this).parent().hasClass("active")) {
            $("#disableActions").show();
            var offs_left = $(this).offset().left;
            $('#bank-slider').css('left', $(this).width() + 28 + offs_left);
            $('#sliderSafe').slider("option", "min", 0);
            $('#sliderSafe').slider("option", "max", parseInt(del_spaces($("#safe").attr("safe"))));
            $(this).parent().addClass('active');
            $('.mark.price .transfer-count').css('left', $('.mark.price span').offset().left + $('.mark.price span').width() + 18 + 55);
        } else {
            $("#disableActions").hide();
            $(this).parent().removeClass('active');
            var transValue = $('.mark.price .transfer-count').attr("transValue");
            var transSafe = $('.mark.price .transfer-count').attr("transActive");
            var newTrans = parseInt(transSafe) + parseInt(transValue);
            $('.mark.price .transfer-count').html('+' + newTrans);
            $('.mark.price .transfer-count').attr("transValue", "0");
            $('.mark.price .transfer-count').attr("transActive", newTrans);
            if ($('.mark.price .transfer-count').attr("transActive") > 0 && !$('.mark.price span').parent().hasClass("active")) {
                $('.mark.price .transfer-count:hidden').fadeToggle(300);
            } else {
                $('.mark.price .transfer-count:visible').fadeToggle(300);
            }
            if ($('.mark.bank .transfer-count').attr("transSafe") > 0) {
                $('.mark.bank .transfer-count:hidden').fadeToggle(300);
            } else {
                $('.mark.bank .transfer-count:visible').fadeToggle(300);
            }
            var data = {
                value: parseInt(transValue),
                from: 2,
                to: 4
            };
            if (parseInt(transValue) > 0) {
                $.ajax({
                    type: "POST",
                    url: "/store/transfer_funds",
                    data: data,
                    dataType: "json",
                    success: function(data) {
                        $("#safe").html(data.user.fundsInfo.safe);
                        $("#safe").attr("safe", data.user.fundsInfo.safe);
                    }
                });
            }
        }
        $('#bank-slider').fadeToggle(300);
        return false;
    });

    $('#sliderActive').slider({
        slide: function(event, ui) {
            var c_val = ui.value;
            var transSafe = $('.mark.bank .transfer-count').attr("transSafe");
            var newTrans = parseInt(transSafe) + parseInt(c_val);
            var active = parseInt($("#active").attr("active"));
            $('.mark.bank .transfer-count').html('+' + newTrans);
            $('.mark.bank .transfer-count').attr("transValue", c_val);
            $('#active').html(active - c_val);
        }
    });
    $('.mark.price span').on('click', function(event) {
        if (!$(this).parent().hasClass("active") && $('.mark.bank span').parent().hasClass("active"))
        {
            return event.stopPropagation();
        }
        $('.mark.price .transfer-count:visible').fadeToggle(300);
        $('.mark.bank .transfer-count:hidden').fadeToggle(300);
        $('#sliderActive').slider("value", 0);
        if (!$(this).parent().hasClass("active")) {
            $("#disableActions").show();
            var offs_left = $(this).offset().left;
            $('#price-slider').css('left', $(this).width() + 28 + offs_left);
            $('#sliderActive').slider("option", "min", 0);
            $('#sliderActive').slider("option", "max", parseInt(del_spaces($("#active").attr("active"))));
            $(this).parent().addClass('active');
            $('.mark.bank .transfer-count').css('left', $('.mark.bank span').offset().left + $('.mark.bank span').width() + 18);
        } else {
            $("#disableActions").hide();
            $(this).parent().removeClass('active');
            var transValue = $('.mark.bank .transfer-count').attr("transValue");
            var transSafe = $('.mark.bank .transfer-count').attr("transSafe");
            var newTrans = parseInt(transSafe) + parseInt(transValue);
            $('.mark.bank .transfer-count').html('+' + newTrans);
            $('.mark.bank .transfer-count').attr("transValue", "0");
            $('.mark.bank .transfer-count').attr("transSafe", newTrans);
            if ($('.mark.bank .transfer-count').attr("transSafe") > 0 && !$('.mark.bank span').parent().hasClass("active")) {
                $('.mark.bank .transfer-count:hidden').fadeToggle(300);
            } else {
                $('.mark.bank .transfer-count:visible').fadeToggle(300);
            }
            if ($('.mark.price .transfer-count').attr("transActive") > 0) {
                $('.mark.price .transfer-count:hidden').fadeToggle(300);
            } else {
                $('.mark.price .transfer-count:visible').fadeToggle(300);
            }
            var data = {
                value: parseInt(transValue),
                from: 1,
                to: 5
            };
            if (parseInt(transValue) > 0) {
                $.ajax({
                    type: "POST",
                    url: "/store/transfer_funds",
                    data: data,
                    dataType: "json",
                    success: function(data) {
                        $("#active").html(data.user.fundsInfo.active);
                        $("#active").attr("active", data.user.fundsInfo.active);
                    }
                });
            }
        }
        $('#price-slider').fadeToggle(300);
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
    /* new */
    $('.slide-bt .slide a').click(function() {
        if (!$(this).parents('.slide-bt').hasClass('vertical')) {
            if ($(this).hasClass('on')) {
                $(this).animate({left: 4}, 300);
            } else {
                $(this).animate({left: 38}, 300);
            }
        } else {
            if ($(this).hasClass('on')) {
                $(this).animate({top: 4}, 300);
            } else {
                $(this).animate({top: 38}, 300);
            }
        }
        $(this).toggleClass('on');
        return false;
    });
    $('.message-form input, .message-form textarea').focus(function() {
        $(this).parent().toggleClass('focus');
    });
    $('.message-form input, .message-form textarea').blur(function() {
        $(this).parent().toggleClass('focus');
    });

});

function del_spaces(str)
{
    str = str.replace(/\s/g, '');
    return str;
}
$(window).load(function() {
    $(".bt-horisontal .slide .slider-l").slider({
        min: 0,
        max: 100,
        stop: function(event, ui) {
            var obj = $(this);
            var sval = $(this).slider("value");
            console.log(sval);
            if (sval == 100) {
                $(this).parents('.slide-bt').find('.del').removeClass('active');
                $(this).parents('.slide-bt').find('.loader').addClass('active');
            } else if (sval == 0) {
                $(this).parents('.slide-bt').find('.loader').removeClass('active');
                $(this).parents('.slide-bt').find('.del').addClass('active');
            } else if (sval >= 30) {
                $(this).parents('.slide-bt').find('.del').removeClass('active');
                $(this).parents('.slide-bt').find('.loader').addClass('active');
                $(this).find('.ui-slider-handle').animate({'left': '100%'}, 200, function() {
                    $(obj).slider("value", 100);
                });
            } else {
                $(this).find('.ui-slider-handle').animate({'left': 0}, 200, function() {
                    $(obj).slider("value", 0);
                });
            }
        }
    });
    $(".bt-vertical .slide .slider-l").slider({
        orientation: "vertical",
        min: 0,
        max: 100,
        stop: function(event, ui) {
            var obj = $(this);
            var sval = $(this).slider("value");
            console.log(sval);
            if (sval == 0) {
                $(this).parents('.slide-bt').find('.del').removeClass('active');
                $(this).parents('.slide-bt').find('.loader').addClass('active');
            } else if (sval == 100) {
                $(this).parents('.slide-bt').find('.loader').removeClass('active');
                $(this).parents('.slide-bt').find('.del').addClass('active');
            } else if (sval >= 30) {
                $(this).parents('.slide-bt').find('.loader').removeClass('active');
                $(this).parents('.slide-bt').find('.del').addClass('active');
                $(this).find('.ui-slider-handle').animate({'bottom': '100%'}, 200, function() {
                    $(obj).slider("value", 100);
                });
            } else {

                $(this).find('.ui-slider-handle').animate({'bottom': '0'}, 200, function() {
                    $(obj).slider("value", 0);
                });
            }
        }
    });
    $(".infol-block").mCustomScrollbar({
        autoDraggerLength: false
    });
    /* begin 24.09.13 dlia skrolinga shop jurnal*/
    $(".shop-jurnal").mCustomScrollbar({
        autoDraggerLength: false
    });
    /* end */


    /* 16.09 */
    textareaSlide($(".info-fl textarea"));
    textareaSlide($(".question-fl textarea"));
    $('.question-fl textarea').focus(function() {
        $(this).parents('.question-fl').toggleClass('focus');
    });
    $('.question-fl textarea').blur(function() {
        $(this).parents('.question-fl').toggleClass('focus');
    });
});
function textareaSlide(txarea) {
    var textArea = txarea;//$(".info-fl textarea");
    textArea.wrap("<div class='textarea-wrapper' />");
    var textAreaWrapper = textArea.parent(".textarea-wrapper");
    textAreaWrapper.mCustomScrollbar({
        scrollInertia: 0,
        advanced: {autoScrollOnFocus: false}
    });
    var hiddenDiv = $(document.createElement("div")),
            content = null;
    if (txarea.parents('.question-fl').length) {
        hiddenDiv.addClass("hiddendiv-q");
    } else {
        hiddenDiv.addClass("hiddendiv");
    }
    $("body").prepend(hiddenDiv);
    textArea.bind("keyup", function(e) {
        content = $(this).val();
        var clength = content.length;
        var cursorPosition = textArea.getCursorPosition();
        content = "<span>" + content.substr(0, cursorPosition) + "</span>" + content.substr(cursorPosition, content.length);
        content = content.replace(/\n/g, "<br />");
        hiddenDiv.html(content + "<br />");
        $(this).css("height", hiddenDiv.height());
        textAreaWrapper.mCustomScrollbar("update");
        var hiddenDivSpan = hiddenDiv.children("span"),
                hiddenDivSpanOffset = 0,
                viewLimitBottom = (parseInt(hiddenDiv.css("min-height"))) - hiddenDivSpanOffset,
                viewLimitTop = hiddenDivSpanOffset,
                viewRatio = Math.round(hiddenDivSpan.height() + textAreaWrapper.find(".mCSB_container").position().top);
        if (viewRatio > viewLimitBottom || viewRatio < viewLimitTop) {
            if ((hiddenDivSpan.height() - hiddenDivSpanOffset) > 0) {
                textAreaWrapper.mCustomScrollbar("scrollTo", hiddenDivSpan.height() - hiddenDivSpanOffset);
            } else {
                textAreaWrapper.mCustomScrollbar("scrollTo", "top");
            }
        }
    });
    $.fn.getCursorPosition = function() {
        var el = $(this).get(0),
                pos = 0;
        if ("selectionStart" in el) {
            pos = el.selectionStart;
        } else if ("selection" in document) {
            el.focus();
            var sel = document.selection.createRange(),
                    selLength = document.selection.createRange().text.length;
            sel.moveStart("character", -el.value.length);
            pos = sel.text.length - selLength;
        }
        return pos;
    }
}