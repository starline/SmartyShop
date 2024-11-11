{if $fee_inside_amount > 0}
    <div>
        <span class="property_name">Комиссия сервиса {$payment_settings->fee_inside}%: </span>
        <b>-{$fee_inside_amount|convert} {$payment_currency->sign}</b>
    </div>
{/if}

{if $fee_fix_inside_amount > 0}
    <div>
        <span class="property_name">Платеж сервису за операцию: </span>
        <b>-{$fee_fix_inside_amount|convert} {$payment_currency->sign}</b>

    </div>
{/if}

{if $sum_inside}
    <div class="mt_20">
        <span class="property_name">Всего издержек {(($sum_inside/$order->payment_price)*100)|price_format:2}%: </span>
        <b class="color_red">-{$sum_inside|convert} {$payment_currency->sign}</b>
        <span class="who_pay"> - Платит продавец</span>
    </div>
{/if}