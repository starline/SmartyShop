{include file='products/products_menu_part.tpl'}

{$meta_title='Импорт товаров' scope=global}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">
			{if $message_error == 'no_permission'}Установите права на запись в папку {$import_files_dir}
			{elseif $message_error == 'convert_error'}Не получилось сконвертировать файл в кодировку UTF8
			{elseif $message_error == 'locale_error'}На сервере не установлена локаль {$locale}, импорт может работать
				некорректно
			{elseif $message_error == 'type_error'}Выберите тип прайса для импорта цен
			{else}{$message_error}
			{/if}
		</span>
	</div>
	<!-- Системное сообщение (The End)-->
{/if}

{if $message_error != 'no_permission'}

	{if $filename}
		<h1 class="mb_15">Импорт {$filename|escape}</h1>
		<div class="price-type">Тип прайса: {$price_types[$price_type]}</div>
		<div id='progressbar'></div>
		<ul id='import_result'></ul>
	{else}
		<h1 class="mb_15">Импорт товаров</h1>
		<form method=post id=product enctype="multipart/form-data">
			<div class="block_flex">
				<input type=hidden name="session_id" value="{$smarty.session.id}">
				<input name="file" class="import_file" type="file" value="" />
				<input class="button_green" type="submit" name="" value="Загрузить" />
				<p>
					(максимальный размер файла &mdash;
					{if $config->max_upload_filesize>1024*1024}{$config->max_upload_filesize/1024/1024|round:'2'}
					МБ{else}{$config->max_upload_filesize/1024|round:'2'} КБ
					{/if})
				</p>
			</div>

			<div class="block_flex price-type">
				<select name="price_type">
					<option value="">Выбрать тип прайса</option>
					{foreach $price_types as $key=>$name}
						<option value="{$key}">{$name}</option>
					{/foreach}
				</select>
			</div>
		</form>

		<div class="block_help">
			<p>Создайте бекап на случай неудачного импорта.</p>
			<p>Сохраните таблицу в формате <b>CSV</b></p>
			<p>
				В первой строке таблицы должны быть указаны названия колонок в таком формате:
			</p>
			<ul>
				<li><span>Товар</span> название товара</li>
				<li><span>Категория</span> категория товара</li>
				<li><span>Бренд</span> бренд товара</li>
				<li><span>Вариант</span> название варианта</li>
				<li><span>Цена</span> цена товара</li>
				<li><span>Оптовая цена</span> оптовая цена товара</li>
				<li><span>Старая цена</span> старая цена товара</li>
				<li><span>Склад</span> количество товара на складе</li>
				<li><span>Вес</span> вес товара в кг</li>
				<li><span>Артикул</span> артикул товара</li>
				<li><span>Видим</span> отображение товара на сайте (0 или 1)</li>
				<li><span>Рекомендуемый</span> является ли товар рекомендуемым (0 или 1)</li>
				<li><span>Аннотация</span> краткое описание товара</li>
				<li><span>Адрес</span> адрес страницы товара</li>
				<li><span>Описание</span> полное описание товара</li>
				<li><span>Изображения</span> имена локальных файлов или url изображений в интернете, через запятую</li>
				<li><span>Заголовок страницы</span> заголовок страницы товара (Meta title)</li>
				<li><span>Ключевые слова</span> ключевые слова (Meta keywords)</li>
				<li><span>Описание страницы</span> описание страницы товара (Meta description)</li>
			</ul>
			<p>Любое другое название колонки трактуется как название свойства товара</p>
			<p>
				<a href='../files/imports/example.csv'><b>Скачать пример файла</b></a>
			</p>
		</div>
	{/if}
{/if}


<script src="/{$config->templates_subdir}js/piecon/piecon.js"></script>

<script>
	{if $filename}

		var price_type = "{$price_type}";
		var in_process = false;
		var count = 1;

		{literal}

			// On document load
			$(function() {
				Piecon.setOptions({fallback: 'force'});
				Piecon.setProgress(0);
				$("#progressbar").progressbar({ value: 1 });
				in_process = true;
				do_import();
			});

			// Порционный импорт товаров
			function do_import(from) {
				from = typeof(from) != 'undefined' ? from : 0;
				$.ajax({
					url: "/app/agmin/ajax/import_" + price_type + ".php",
					data: {from:from},
					dataType: 'json',
					success: function(data) {

						for (var key in data.items) {
							html1 = '<li><span class=count>' + count + '</span> <span title=' + data.items[key]
								.status + ' class="status ' + data.items[key].status + '"></span>';

							if (!data.items[key].error) {
								html2 = '<a target=_blank href="?view=ProductPriceAdmin&id=' + data.items[key]
									.product.id + '">' + data.items[key].product.name + '</a>';
								if (data.items[key].variant.name) {
									html2 += ' - ' + data.items[key].variant.name + ' - ';
								}
								html2 += ' <span class="new_price">' + data.items[key].variant.price + data.items[key]
									.currency.sign + '</span> <span class="old_price">' + data.items[key].variant
									.prev_price + data.items[key].currency.sign +
									'</span> <span class="wholesale_price">(Опт: ' + data.items[key].variant
									.cost_price + data.items[key].currency.sign + ')</span>';
							} else {
								html2 = data.items[key].error;
							}

							if (data.items[key].synonym) {
								html2 += ' ' + data.items[key].synonym;
							}

							html3 = '</li>';

							$('ul#import_result').prepend(html1 + html2 + html3);
							count++;
						}

						Piecon.setProgress(Math.round(100 * data.from / data.totalsize));
						$("#progressbar").progressbar({ value: 100 * data.from / data.totalsize });

						if (data != false && !data.end) {
							do_import(data.from);
						} else {
							Piecon.setProgress(100);
							$("#progressbar").hide('fast');
							in_process = false;
						}
					},
					error: function(xhr, status, errorThrown) {
						alert(errorThrown + '\n' + xhr.responseText);
					}
				});
			}
		{/literal}

	{/if}
</script>

<style>
	.ui-progressbar-value {
		background-color: #b4defc;
		background-image: url(/{$config->templates_subdir}images/progress.gif);
		background-position: left;
		border-color: #009ae2;
	}

	#progressbar {
		clear: both;
		height: 29px;
	}

	#result {
		clear: both;
		width: 100%;
	}
</style>