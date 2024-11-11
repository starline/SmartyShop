/*!
 * Template grizlicnc
 * Common.js
 * @author Andi Huga
 * 
 */

$(function () {

    //  Автозаполнитель поиска
    $(".input_search").autocomplete({
        serviceUrl: '/app/public/ajax/search_products.php',
        minChars: 1,
        noCache: false,
        width: "480px", // Ширина списка
        zIndex: 9999, // z-index списка
        deferRequestBy: 400, // Задержка запроса (мсек), на случай, если мы не хотим слать миллион запросов, пока пользователь печатает. Я обычно ставлю 300.
        onSelect: function (suggestion) {
            $(".input_search").closest('form').submit();
        },
        formatResult: function (suggestion, currentValue) {
            var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
            var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
            if (suggestion.data) {
                return '<div>' + (suggestion.data.image ? "<img align=absmiddle src='" + suggestion.data.image + "'> " : '') + '<span class="product-name">' + suggestion.data.name.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + '</span></div>';
            } else {
                return '<div>' + suggestion.value + '</div>';
            }
        }
    });


    // Зум картинок
    $("a.zoom").fancybox({
        buttons: [
            'close'
        ],
        image: {
            preload: true
        },
        closeExisting: true,
        defaultType: "image"
    });

    asignFancyAjax();

    // Аяксовая корзина при нажатии на Купить
    $('form.variants').submit(function (e) {
        e.preventDefault();

        let button = $(this).find('input[type="submit"]');
        let amount = 1;

        if ($(this).find('input[name=amount]').val())
            amount = $(this).find('input[name=amount]').val();

        // Для страницы товара
        if ($(this).find('input[name=variant][type="radio"]').length > 0) {
            var variant = $(this).find('input[name=variant]:checked').val();
            amount = $(this).find('input[name=amount]').val();
        }

        // Для товаров в каталоге
        else if ($(this).find('input[name=variant]').length > 0)
            var variant = $(this).find('input[name=variant]').val();

        // Для выпадающего списка вариантов
        else if ($(this).find('select[name=variant]').length > 0)
            var variant = $(this).find('select').val();

        $.ajax({
            url: "/app/public/ajax/cart.php",
            data: {
                variant: variant,
                amount: amount
            },
            dataType: 'json',
            success: function (data) {
                $('#cart-informer').html(data);
                if (button.attr('data-result-text'))
                    button.val(button.attr('data-result-text'));
                // Popup
                $.fancybox.open({
                    type: 'ajax',
                    src: '/cart',
                    touch: false,
                    closeExisting: true,
                    afterShow: asignFancyAjax
                });
            }
        });
        return false;
    });


    // Form in Fancy
    function asignFancyAjax() {

        // Корзина информер Popup
        $("#cart-informer a").fancybox({
            type: 'ajax',
            src: '/cart',
            touch: false,
            closeExisting: true,
            afterShow: asignFancyAjax,
        });

        // Ajax ссылки
        $('.fancybox-inner a.ajax').click(function (e) {
            e.preventDefault();
            $.post($(this).attr('href'), function (response) {
                $.fancybox.open({
                    type: 'html',
                    src: response,
                    touch: false,
                    closeExisting: true,
                    afterShow: asignFancyAjax
                });
            });
        })

        // Ajax форм
        $('.fancybox-inner form').submit(function (e) {
            e.preventDefault();
            $(this).ajaxSubmit({
                dataType: "html",
                beforeSubmit: function (arr, form, options) {
                    // показать лоадер
                },
                success: function (response) {
                    response = $.parseResponseByType(response);
                    if (response.type == 'html') {
                        $.fancybox.open({
                            type: 'html',
                            src: response.data,
                            touch: false,
                            closeExisting: true,
                            afterShow: asignFancyAjax
                        });
                    } else {
                        data = response.data;
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }
                },
                error: function (xmlRequest, textStatus, errorThrown) {
                    alert(errorThrown);
                }
            });
        });
    }


    /*** Parse response ***/
    $.parseResponseByType = function (response) {
        let result = {};
        if (response.indexOf('{') == 0) { 	//it is JSON response
            result['data'] = $.parseJSON(response);
            result['type'] = 'json';
        } else {							//it is HTML response
            result['data'] = response;
            result['type'] = 'html';
        }
        return result;
    }


    /*** Back to Top button  ***/
    function trackScroll() {
        let scrolled = window.scrollY;
        let coords = document.documentElement.clientHeight;

        if (scrolled > coords) {
            goTopBtn.classList.add('back-to-top-show');
        }
        if (scrolled < coords) {
            goTopBtn.classList.remove('back-to-top-show');
        }
    }

    function backToTop() {
        if (window.scrollY > 0) {
            window.scrollBy(0, -80);
            setTimeout(backToTop, 0);
        }
    }

    let goTopBtn = document.querySelector('.back-to-top');
    if (goTopBtn) {
        window.addEventListener('scroll', trackScroll);
        goTopBtn.addEventListener('click', backToTop);
    }

    /*** Show menu in mobile version ***/
    $('#header_top .button').on('click', function () {
        if ($('#header_top').hasClass("show-mobile-menu")) {
            //$('#header_top').removeClass("show-mobile-menu");
        } else {
            // $('#header_top').addClass("show-mobile-menu");

        }
    });

    $("#header_top .menu_button").fancybox({
        src: "#menu",
        type: "inline",
        touch: false,
        closeExisting: true,
        afterClose: function () {
            $("#menu").removeAttr('style');
        }
    });

});