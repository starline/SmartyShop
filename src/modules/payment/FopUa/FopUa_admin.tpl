{if $tax_amount}
    <div>
        <span class='property_name'>Налоги ФОП {$payment_settings->tax}%: </span>
        <b>{$tax_amount|convert}<span class="price_sign">{$payment_currency->sign}</span></b>
        <span class="who_pay"> - Платит покупатель</span>
    </div>
{/if}

{if $tax_inside_amount}
    <div>
        <span class='property_name'>Налоги ИП {$payment_settings->tax_inside}%: </span>
        <b class='color_red'>-{$tax_inside_amount|convert}<span class="price_sign">{$payment_currency->sign}</span></b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}

<h3>Квитанции</h3>

<div>
    <div class="form_line">
        <label for='payment_name'>Наименование плательщика: </label>
        <input id='payment_name' autocomplete='off' type='text' name="order_settings[payment_name]"
            value="{$order->settings->payment_name}" />
    </div>

    <div class="form_line">
        <label for='payment_checkdate'>Дата выставления счета: </label>
        <input id='payment_checkdate' autocomplete='off' type='text' name="order_settings[payment_checkdate]"
            value="{$order->settings->payment_checkdate ?? ''}" />
    </div>

    <div class="btn_row">
        <a class="button"
            href="/exchange/payment/callback.php?order_url={$order->url}&form_type=invoice&payment_name=FopUa"
            target="_blank" title="Сформировать Счет">Рахунок</a>
        <a class='button'
            href="/exchange/payment/callback.php?order_url={$order->url}&form_type=packing_list&payment_name=FopUa"
            target="_blank" title="Сформировать Расходную накладную">Видаткова</a>
    </div>
</div>

<script src="/{$config->templates_subdir}js/jquery/datepicker/jquery.ui.datepicker-ru.js"></script>
<script>
    {literal}

        $(function() {
            // Выбор даты
            $('input[name="order_settings[payment_checkdate]"]').datepicker({
                regional: 'ru'
            });
        });

    {/literal}
</script>