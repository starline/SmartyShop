{if $tax_amount > 0}
    <div>
        <span class="property_name">Комиссия сервиса : </span>
        <span>{$payment_settings->tax} % ({$tax_amount|convert}
            {$payment_currency->sign})</span>
    </div>
{/if}

{if $tax_inside_amount > 0}
    <div>
        <span class="property_name">Комиссия сервиса внутреняя: </span>
        <span>{$payment_settings->tax_inside}% (<b class='color_red'>-{$tax_inside_amount|convert}
                {$payment_currency->sign}</b>)</span>
        <span class="who_pay"> - Оплачивает продавец</span>
    </div>
{/if}