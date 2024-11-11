{include file='products/products_menu_part.tpl'}

{* Meta Title *}
{if $category}
	{$meta_title=$category->name scope=global}
{else}
	{$meta_title='Товары' scope=global}
{/if}

<div class="two_columns_list">

	<!-- Заголовок -->
	<div id="header">
		<div class="header_top">

			{if $products_count}
				{if $category->name || $brand->name}
					<h1>{$category->name} {$brand->name}
						<span class="sum_total">{$products_count}
							{$products_count|plural:'товар':'товаров':'товара'}</span>
					</h1>
				{elseif $keyword}
					<h1>{$products_count|plural:'Найден':'Найдено':'Найдено'}
						<span class="sum_total">{$products_count}
							{$products_count|plural:'товар':'товаров':'товара'}</span>
					</h1>
				{else}
					<h1>Все товары <span class="sum_total">{$products_count}
							{$products_count|plural:'товар':'товаров':'товара'}</span></h1>
				{/if}
			{else}
				<h1>Нет товаров</h1>
			{/if}

			<div class="btns_wrap">
				{if $user|user_access:products_price}
					<a class="add" href="{url view=ProductAdmin}">Добавить товар</a>
				{/if}

				{if $user|user_access:products_import AND $products_count > 0}
					<a class="export_btn" href="{url view=ExportEntityAdmin entity=products}">
						<img src="/{$config->templates_subdir}images/export_excel.png" name="export"
							title="Экспортировать товары" />
					</a>

					<a class="export_btn" href="{url view=ProductsImportAdmin entity=products}">
						<img src="/{$config->templates_subdir}images/import_excel.png" name="import"
							title="Импортировать товары" />
					</a>
				{/if}
			</div>

			{* Search *}
			<div id="search">
				<form method="get">
					<input type="hidden" name="view" value="ProductsAdmin">
					<input class="search" type="text" name="keyword" value="{$keyword|escape}"
						placeholder="Название, артикул" />
					<input class="search_button" type="submit" value="" />
				</form>
			</div>
		</div>

		{if $category->id}
			<a class="out_link" target="_self" href="../{$category->url}">Открыть категорию на сайте</a>
		{/if}
	</div>


	<!-- Меню -->
	<div id="right_menu">
		<div class="popup_menu_btn">
			<span class="popup_btn_sign">
				<li></li>
				<li></li>
				<li></li>
			</span>
			<span class="popup_btn_text">Фильтр</span>
		</div>
		<div id="popup_menu_block">

			<!-- Категории товаров -->
			{include file='parts/categories_tree_part.tpl'}

			<!-- Фильтры -->
			<ul class="menu_list layer">
				<li class="{if !$filter}selected{/if}">
					<a href="{url page=null filter=null date_from=null}">Все товары</a>
				</li>
				<li class="{if $filter == 'sale'}selected{/if}">
					<a href="{url page=null filter='sale' date_from=null}">Акция</a>
				</li>
				<li class="{if $filter == 'featured'}selected{/if}">
					<a href="{url page=null filter='featured' date_from=null}">Рекомендуемые</a>
				</li>
				<li {if $filter == 'discounted'}class="selected" {/if}>
					<a href="{url page=null filter='discounted' date_from=null}">Со скидкой</a>
				</li>
				<li {if $filter == 'visible'}class="selected" {/if}>
					<a href="{url page=null filter='visible' date_from=null}">Активные</a>
				</li>
				<li {if $filter == 'hidden'}class="selected" {/if}>
					<a href="{url page=null filter='hidden' date_from=null}">Неактивные</a>
				</li>
				<li {if $filter == 'outofstock'}class="selected" {/if}>
					<a href="{url page=null filter='outofstock' date_from=null}">Нет в наличии</a>
				</li>
				<li {if $filter == 'instock'}class="selected" {/if}>
					<a href="{url page=null filter='instock' date_from=null}">В наличии</a>
				</li>

				{if $user|user_access:products_price}
					<li {if $filter == 'stagnation'}class="selected" {/if}>
						<a href="{url keyword=null page=null filter='stagnation' date_from=null}">Застой склада</a>
					</li>

					<li {if $filter == 'purchase'}class="selected" {/if}>
						<a
							href="{url keyword=null page=null filter='purchase' date_from='-60 days'|strtotime|date_format:'%Y-%m-%d'}">Необходимо
							закупить</a>
					</li>

					<li {if $filter == 'top' AND $date_from == '-30 days'|strtotime|date_format:'%Y-%m-%d'}class="selected"
						{/if}>
						<a
							href="{url keyword=null page=null filter='top' date_from='-30 days'|strtotime|date_format:'%Y-%m-%d'}">Лучшие
							продажи за 30 дней</a>
					</li>
					<li {if $filter == 'top' AND $date_from == '-90 days'|strtotime|date_format:'%Y-%m-%d'}class="selected"
						{/if}>
						<a
							href="{url keyword=null page=null filter='top' date_from='-90 days'|strtotime|date_format:'%Y-%m-%d'}">Лучшие
							продажи за 90 дней</a>
					</li>
				{/if}
			</ul>

			{if $brands}
				<ul class="menu_list layer">
					<li {if !$brand->id}class="selected" {/if}><a href="{url brand_id=null}">Все бренды</a></li>
					{foreach $brands as $b}
						<li brand_id="{$b->id}" class="{if $brand->id == $b->id}selected{else}droppable brand{/if}"><a
								href="{url keyword=null page=null brand_id=$b->id}">{$b->name}</a></li>
					{/foreach}
				</ul>
			{/if}

		</div>
	</div>


	<!-- Список товаров -->
	<div id="main_list">
		{if $products}

			<!-- Статистика продажи товара-->
			{if $user|user_access:stats AND $category->id}
				<div class="product_stats">
					<div id='product_stats'></div>
				</div>
			{/if}

			<div class='list_top_row'>
				{include file='parts/pagination.tpl'}

				<div id="expand">
					<span class="dash_link" id="expand_all">Развернуть все варианты ↓</span>
					<span class="dash_link" id="roll_up_all" style="display:none;">Свернуть все варианты ↑</span>
				</div>
			</div>

			<!--  Основная форма -->
			<form id="list_form" method="post">
				<input type="hidden" name="session_id" value="{$smarty.session.id}">

				<div class="list">
					{foreach $products as $product}
						<div class="row {if !$product->visible}visible_off{/if} {if $product->disable}disable{/if} {if !$product->featured}featured_off{/if} {if !$product->sale}sale_off{/if}"
							item_id="{$product->id}">

							<input type="hidden" name="positions[{$product->id}]" value="{$product->position}">

							{if $user|user_access:products_price AND !$product->order_date AND !$product->never_ordered AND !$product->profit AND !$product->need}
								<div class="move">
									<div class="move_zone"></div>
								</div>
							{/if}

							{if $user|user_access:products_price}
								<div class="checkbox">
									<input type="checkbox" name="check[]" value="{$product->id}" />
								</div>
							{/if}

							<div class="image">
								<img
									src="{if $product->image_filename}{$product->image_filename|escape|resize:50:50}{else}{$config->templates_subdir}images/cargo.png{/if}" />
							</div>

							<div class="name product_name">
								{if $user|user_access:products_content}
									<a
										href="{url view=ProductAdmin id=$product->id return=$smarty.server.REQUEST_URI}">{$product->name|escape}</a>
								{else}
									{$product->name|escape}
								{/if}

								<div class="icons">
									<a class="external_link" title="Предпросмотр в новом окне" href="../product/{$product->id}"
										target="_blank"></a>
								</div>

								{if $product->order_date}
									<div class="additional_notice" title="Дата последнего заказа">
										Последний заказ: <span>{$product->order_date|date}</span>
										прошло <span>{(($smarty.now - $product->order_date|strtotime)/60/60/24)|round}</span> дней
									</div>
								{elseif $product->never_ordered}
									<div class="additional_notice">Ни разу не был заказан</div>
								{elseif $product->profit}
									<div class="additional_notice">
										Прибыль: <span>{$product->profit|convert} {$currency->sign}</span>
										продано <span>{$product->sold} {$settings->units}</span>
									</div>
								{elseif $product->need}
									<div class="additional_notice">
										Нужнно закупить: <span>{$product->need} {$settings->units}</span>
										Продано <span>{$product->sold} {$settings->units}</span>
									</div>
								{/if}
							</div>

							<div class="variants">
								<ul>
									{foreach $product->variants as $variant}
										<li {if !$variant@first}class="variant" style="display:none;" {/if}>

											{if $variant->movements_amount}
												<span class="wmovements"
													title="{foreach $variant->movements as $mov}Поставка №{$mov->movement_id} | {$mov->awaiting_date|date} | +{$mov->amount}&#013;{/foreach}">+{$variant->movements_amount}</span>
											{/if}

											{if $variant->name}
												<i
													title="{$variant->name|escape}">{$variant->name|escape|truncate:20:'…':true:false}</i>
											{/if}

											{if $variant->sku}
												<div class="sku">{$variant->sku}</div>
											{/if}

											<span class="price">
												{if $user|user_access:products_price}
													<a {if $variant->cost_price > 0}
															title="Оптовая цена &mdash; {$variant->cost_price|convert:null:false} {$currency->sign}&#013;Доход &mdash; {$variant->profit_price|convert:null:false} {$currency->sign}&#013;&#013;Старая цена  &mdash; {$variant->old_price|convert:null:false} {$currency->sign}"
														{/if}
														href="{url view=ProductPriceAdmin id=$product->id return=$smarty.server.REQUEST_URI}">{$variant->price|convert}
														{$currency->sign}</a>
												{else}
													{$variant->price|convert} {$currency->sign}
												{/if}
											</span>

											<span class="stock">{if $variant->infinity}∞
												{else}{$variant->stock}
												{$settings->units}{/if}</span>
											<input type="hidden" name="variant" value="{$variant->id}" />
										</li>
									{/foreach}
								</ul>

								{if $product->variants AND $product->variants|count > 1}
									{$variants_num = $product->variants|count}
									<div class="expand_variant_links">
										<a class="expand_variant" href="#">{$variants_num}
											{$variants_num|plural:'вариант':'вариантов':'варианта'} ↓</a>
										<a class="roll_up_variant" style="display:none;" href="#">{$variants_num}
											{$variants_num|plural:'вариант':'вариантов':'варианта'} ↑</a>
									</div>
								{/if}
							</div>

							<div class="icons">
								{if $user|user_access:products_price}
									<a class="show_chart" title="Показать график продаж"></a>
								{/if}

								<a class="featured {if $user|user_access:products_price}edit{/if}" title="Рекомендуемый"></a>
								<a class="sale {if $user|user_access:products_price}edit{/if}" title="Акция"></a>
								<a class="enable {if $user|user_access:products_price}edit{/if}" title="Активен"></a>
								{if $user|user_access:products_price}
									<a class="duplicate" title="Дублировать"></a>
								{/if}
							</div>

						</div>
					{/foreach}
				</div>


				{if $user|user_access:products_price}
					<div id="action">
						<span id="check_all" class="dash_link">Выбрать все</span>

						<span id="select">
							<select name="action">
								<option value="">Выбрать действие</option>
								<option value="enable">Сделать видимыми</option>
								<option value="disable">Сделать невидимыми</option>
								<option value="set_featured">Сделать рекомендуемым</option>
								<option value="unset_featured">Отменить рекомендуемый</option>
								<option value="set_sale">Сделать Акцию</option>
								<option value="unset_sale">Отменить Акцию</option>
								<option value="duplicate">Создать дубликат</option>
								{if $pages_count>1}
									<option value="move_to_page">Переместить на страницу</option>
								{/if}
								{if $categories|count>1}
									<option value="move_to_category">Переместить в категорию</option>
								{/if}
								{if $brands|count>0}
									<option value="move_to_brand">Указать бренд</option>
								{/if}
								<option value="delete">Удалить</option>
							</select>
						</span>

						<span id="move_to_page">
							<select name="target_page">
								{section target_page $pages_count}
									<option value="{$smarty.section.target_page.index+1}">{$smarty.section.target_page.index+1}
									</option>
								{/section}
							</select>
						</span>

						<span id="move_to_category">
							<select name="target_category">
								{function name=category_select level=0}
									{foreach $categories as $category}
										<option value='{$category->id}'>
											{section sp $level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name|escape}</option>
										{category_select categories=$category->subcategories selected_id=$selected_id level=$level+1}
									{/foreach}
								{/function}
								{category_select categories=$categories}
							</select>
						</span>

						<span id="move_to_brand">
							<select name="target_brand">
								<option value="0">Не указан</option>
								{foreach $all_brands as $b}
									<option value="{$b->id}">{$b->name}</option>
								{/foreach}
							</select>
						</span>

						<input id="apply_action" class="button_green" type="submit" value="Применить">
					</div>
				{/if}

			</form>

			{include file='parts/pagination.tpl'}
		{/if}
	</div>
</div>

{include file='parts/charts_init.tpl'}

<script>
	const session = '{$smarty.session.id}';
	const php_category_id = '{$category->id}';
	const php_currency_name = '{$currency->name}';
	const php_currency_sign = '{$currency->sign}';

	{literal}
		$(function() {

			// Сортировка списка
			$(".list").sortable({
				items: ".row",
				handle: ".move_zone",
				tolerance: "pointer",
				opacity: 0.95,
				update: function(event, ui) {
					$("#list_form input[name*='check']").prop('checked', false);
					$("#list_form").ajaxSubmit(function() {
						colorize();
					});
				}
			});


			// Перенос товара на другую страницу
			$("#action select[name=action]").change(function() {
				if ($(this).val() == 'move_to_page')
					$("span#move_to_page").show();
				else
					$("span#move_to_page").hide();
			});
			$("#pagination a.droppable").droppable({
				activeClass: "drop_active",
				hoverClass: "drop_hover",
				tolerance: "pointer",
				drop: function(event, ui) {
					$(ui.helper).find('input[type="checkbox"][name*="check"]').prop('checked', true);
					$(ui.draggable).closest("form").find(
						'select[name="action"] option[value=move_to_page]').prop("selected",
						"selected");
					$(ui.draggable).closest("form").find('select[name=target_page] option[value=' + $(this)
						.html() + ']').prop("selected", "selected");
					$(ui.draggable).closest("form").submit();
					return false;
				}
			});


			// Перенос товара в другую категорию
			$("#action select[name=action]").change(function() {
				if ($(this).val() == 'move_to_category')
					$("span#move_to_category").show();
				else
					$("span#move_to_category").hide();
			});
			$("#right_menu .droppable.category").droppable({
				activeClass: "drop_active",
				hoverClass: "drop_hover",
				tolerance: "pointer",
				drop: function(event, ui) {
					$(ui.helper).find('input[type="checkbox"][name*="check"]').prop('checked', true);
					$(ui.draggable).closest("form").find(
						'select[name="action"] option[value=move_to_category]').prop("selected",
						"selected");
					$(ui.draggable).closest("form").find('select[name=target_category] option[value=' + $(
						this).attr('category_id') + ']').prop("selected", "selected");
					$(ui.draggable).closest("form").submit();
					return false;
				}
			});


			// Перенос товара в другой бренд
			$("#action select[name=action]").change(function() {
				if ($(this).val() == 'move_to_brand')
					$("span#move_to_brand").show();
				else
					$("span#move_to_brand").hide();
			});

			$("#right_menu .droppable.brand").droppable({
				activeClass: "drop_active",
				hoverClass: "drop_hover",
				tolerance: "pointer",
				drop: function(event, ui) {
					$(ui.helper).find('input[type="checkbox"][name*="check"]').prop('checked', true);
					$(ui.draggable).closest("form").find(
						'select[name="action"] option[value=move_to_brand]').prop("selected",
						"selected");
					$(ui.draggable).closest("form").find('select[name=target_brand] option[value=' + $(
						this).attr('brand_id') + ']').prop("selected", "selected");
					$(ui.draggable).closest("form").submit();
					return false;
				}
			});


			// Если есть варианты, отображать ссылку на их разворачивание
			if ($("li.variant").length > 0)
				$("#expand").show();


			// Показать все варианты
			$("#expand_all").click(function() {
				$("#expand_all").hide();
				$("#roll_up_all").show();
				$(".expand_variant").hide();
				$(".roll_up_variant").show();
				$(".variants ul li.variant").fadeIn('fast');
				return false;
			});


			// Свернуть все варианты
			$("#roll_up_all").click(function() {
				$("#roll_up_all").hide();
				$("#expand_all").show();
				$(".roll_up_variant").hide();
				$(".expand_variant").show();
				$(".variants ul li.variant").fadeOut('fast');
				return false;
			});


			// Показать вариант
			$("a.expand_variant").click(function() {
				$(this).closest("div.variants").find("li.variant").fadeIn('fast');
				$(this).closest("div.variants").find(".expand_variant").hide();
				$(this).closest("div.variants").find(".roll_up_variant").show();
				return false;
			});

			// Свернуть вариант
			$("a.roll_up_variant").click(function() {
				$(this).closest("div.variants").find("li.variant").fadeOut('fast');
				$(this).closest("div.variants").find(".roll_up_variant").hide();
				$(this).closest("div.variants").find(".expand_variant").show();
				return false;
			});

			// Дублировать товар
			$("a.duplicate").click(function() {
				$('.list input[type="checkbox"][name*="check"]').prop('checked', false);
				$(this).closest("div.row").find('input[type="checkbox"][name*="check"]').prop('checked', true);
				$(this).closest("form").find('select[name="action"] option[value=duplicate]').prop('selected',
					true);
				$(this).closest("form").submit();
			});


			// Скрыт/Видим
			$("a.enable.edit").click(function() {
				ajax_icon($(this), 'product', 'visible', session);
				return false;
			});

			// Сделать хитом
			$("a.featured.edit").click(function() {
				ajax_icon($(this), 'product', 'featured', session);
				return false;
			});

			// Сделать акционным
			$("a.sale.edit").click(function() {
				ajax_icon($(this), 'product', 'sale', session);
				return false;
			});


			// Статистика продаж
			$('.list .row .show_chart').click(function() {
				let row = $(this).closest('.row');
				let id = row.attr('item_id');
				let icon = $(this);

				if (!$("div").is('#chart_' + id)) {
					icon.addClass('loading_icon');
					row.after("<div id='chart_" + id + "'></div>");
					show_stat_graphic(
						'chart_' + id,
						{product_id: id, filter: 'byMonth'},
						['totalPrice', 'profitPrice', 'amount'],
						options,
						php_currency_sign,
						function(data) {

							// Устанавливаем высоту графика
							if (data)
								$("#chart_" + id).css("height", "200px");
							icon.removeClass('loading_icon');
						}
					);
				} else {
					$('#chart_' + id).remove();
				}
			});

			show_stat_graphic(
				'product_stats',
				{category_id: php_category_id, filter: 'byMonth'},
				['totalPrice', 'profitPrice', 'amount'],
				options,
				php_currency_sign,
				function(data) {
					if (data)
						$("#product_stats").css("height", "250px");
				}
			);
		});
	{/literal}
</script>