
/*!
 * Common js file
 * Functions, actions
 * 
 * @author Andi Huga
 * 
 */

$(function () {

	// Раскрасить строки сразу
	colorize();

	// Image Zoom init
	initFancybox();


	// Tooltips
	$(document).tooltip();


	// Выделить все
	$("#check_all").click(function () {
		$('.list input[type="checkbox"][name*="check"]').prop('checked', $(
			'.list input[type="checkbox"][name*="check"]:not(:checked)').length > 0);
	});

	// Удалить 
	$("#main_list form").on('click', 'a.delete', function () {
		$('.list input[type="checkbox"][name*="check"]').prop('checked', false);
		$(this).closest(".row").find('input[type="checkbox"][name*="check"]').prop('checked', true);
		$(this).closest("form").find('select[name="action"] option[value=delete]').prop('selected',
			true);
		$(this).closest("form").submit();
	});

	// Подтверждение удаления
	$("#main_list form").submit(function () {
		if ($('.list input[type="checkbox"][name*="check"]:checked').length > 0)
			if ($('select[name="action"]').val() == 'delete' && !confirm('Подтвердите удаление'))
				return false;
	});


	$("#right_menu .popup_menu_btn").fancybox({
		src: "#popup_menu_block",
		type: "inline",
		touch: false,
		closeExisting: true,
		afterClose: function () {
			$("#popup_menu_block").removeAttr('style');
		}
	});

});


// Зум картинок
function initFancybox() {
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
}


// Раскраска строк
function colorize() {
	$(".list div.row:even").addClass('even');
	$(".list div.row:odd").removeClass('even');
}


function generate_meta_title() {
	let name = $('input[name="name"]').val();
	return name;
}

function generate_meta_keywords() {
	let name = $('input[name="name"]').val();
	let result = name;
	let brand = $('select[name="brand_id"] option:selected').attr('brand_name');

	if (typeof (brand) == 'string' && brand != '')
		result += ', ' + brand;

	$('select[name="categories[]"]').each(function (index) {
		c = $(this).find('option:selected').attr('category_name');
		if (typeof (c) == 'string' && c != '')
			result += ', ' + c;
	});
	return result;
}

function generate_url() {
	let url = $('input[name="name"]').val();
	url = url.replace(/[\s]+/gi, '-');
	url = translit(url);
	url = url.replace(/[^0-9a-z_\-]+/gi, '').toLowerCase();
	return url;
}



// Form in Fancy
function asignFancyAjax() {

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


// Ajax icons
function ajax_icon(icon, entity, var_name, session) {

	icon.addClass('loading_icon');
	let line = icon.closest(".row");

	let id = line.find('input[name*="check"]').val();
	if (!id) {
		id = line.attr('item_id');
	}

	let state = line.hasClass(var_name + '_off') ? 1 : 0;

	$.ajax({
		type: 'POST',
		url: '/app/agmin/ajax/update_entity.php',
		data: { 'entity': entity, 'id': id, 'values': { [var_name]: state }, 'session_id': session },
		success: function (data) {
			icon.removeClass('loading_icon');
			if (state) {
				line.removeClass(var_name + '_off');
				line.addClass(var_name + '_on');
			} else {
				line.removeClass(var_name + '_on');
				line.addClass(var_name + '_off');
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr.status);
			console.log(thrownError);
		},
		dataType: 'json'
	});
}



// RU -> EN
function translit(str) {
	let ru = (
		"А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я"
	).split("-");
	let en = (
		"A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-I-i-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-С-с-CH-ch-SH-sh-SCH-sch-'-'-Y-y-'-'-E-e-YU-yu-YA-ya"
	).split("-");

	let res = '';
	for (let i = 0, l = str.length; i < l; i++) {
		let s = str.charAt(i),
			n = ru.indexOf(s);
		if (n >= 0) { res += en[n]; } else { res += s; }
	}
	return res;
}
