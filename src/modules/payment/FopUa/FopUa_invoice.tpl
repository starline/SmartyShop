{if $form_type=='invoice'}

    {$alert="Оплата цього рахунку означає погодження з умовами поставки товарів. Повідомлення про оплату є
    обов'язковим, в іншому випадку не гарантується наявність товарів на складі. Товар відпускається за фактом
    надходження коштів на p/p Постачальника. Самовивозом, за наявності довіреності та паспорта відправлення
    транспортною компанією."}

    <table cellspacing="0" cellpadding="6" border="1">
        <tr>
            <td style="font-size: 8;">
                <b>Увага!</b> <span>{$alert}</span>
            </td>
        </tr>
    </table>

    <br />
    <br />
{/if}



{if $form_type=='invoice'}
    <div style="font-size: 16">Рахунок на оплату <b>№ {$order->id}</b> від {$order->date_spellput}</div>
{elseif $form_type=='packing_list'}
    <div style="font-size: 16">Видаткова накладна <b>№ {$order->id}</b> від {$order->date_spellput}</div>
{/if}

<div style="border-top: 1px solid black;"></div>


<table cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td width="20%">
            <div>
                <b>Постачальник:</b>
            </div>
        </td>
        <td style="text-align: left;" width="80%">
            <div style="font-size: 12;">{$payment_settings->recipient}</div><br />
            P/p {$payment_settings->account},<br />
            Банк {$payment_settings->bank}, МФО {$payment_settings->mfo},<br />
            Юр. адреса: {$payment_settings->recipient_adress},<br />
            код за ЄДРПОУ {$payment_settings->edrpou}, ІПН {$payment_settings->ipn}<br />
            <div style="font-size: 8;">He є платником податку на прибуток на загальних засадах</div>
        </td>
    </tr>
    <tr>
        <td width="20%">
            <div>
                <b>Покупець:</b>
            </div>
        </td>
        <td style="text-align: left;" width="80%">
            <div style="font-size: 12;">{$order->name}</div>
        </td>
    </tr>
    <tr>
        <td width="20%">
            <div>
                <b>Договір:</b>
            </div>
        </td>
        <td style="text-align: left;" width="80%">
            <div>Основний договір</div>
        </td>
    </tr>
</table>

<br />
<br />

<table cellspacing="0" cellpadding="6" border="1" style="font-size: 8;">
    <tr>
        <td width="4%"><b>№</b></td>
        <td width="8%"><b>Арт.</b></td>
        <td width="59%"><b>Товар</b></td>
        <td width="9%" style="text-align: center;"><b>К-сть</b></td>
        <td width="10%" style="text-align: center;"><b>Ціна</b></td>
        <td width="10%" style="text-align: center;"><b>Сума</b></td>
    </tr>
    {foreach $purchases as $key=>$purch}
        <tr>
            <td>{$key+1}</td>
            <td>{$purch->sku}</td>
            <td>{$purch->product_name}{if !empty($purch->variant_name)} - {$purch->variant_name}{/if}</td>
            <td style="text-align: right;">{$purch->amount}</td>
            <td style="text-align: right;">{$purch->price|convert:$payment_method->currency_id}</td>
            <td style="text-align: right;">
                {($purch->price|number_format:2:".":"" * $purch->amount)|convert:$payment_method->currency_id}</td>
        </tr>
    {/foreach}
</table>

<br />

<div>Всього найменувань <b>{$purchases|count}</b>, на суму <b>{$order->payment_price_spellout_int}</b>
    {$payment_method->currency->sign}.
    {if !$order->payment_price_spellout_dec|empty}
        <b>{$order->payment_price_spellout_dec}</b> {$payment_method->settings->pense}
    {/if}
</div>

{if !empty($order->coupon_discount) and $order->coupon_discount > 0}
    <div>Нельзя применять купоны при оплате на счет</div>
{/if}

<div style="text-align: right; font-size: 12;">Разом:
    <b>{$order->payment_price|convert:$payment_method->currency_id}</b>
    {$payment_method->currency->sign}
</div>

<br />
<br />

{if $form_type=='invoice'}
    <table cellspacing="0" cellpadding="6" border="0" style="font-size: 8;">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <div style="text-align: left;">
                    <div style="border-top: 1px solid black;">Виписав(ла)</div>
                </div>
            </td>
        </tr>
    </table>
{elseif $form_type=='packing_list'}
    <table cellspacing="0" cellpadding="6" border="0" style="font-size: 8">
        <tr>
            <td width="40%">
                <div style="text-align: left;">
                    <div style="border-top: 1px solid black;">Відвантажив(ла)</div>
                </div>
            </td>
            <td width="20%"></td>
            <td width="40%">
                <div style="text-align: left;">
                    <div style="border-top: 1px solid black;">Отримав(ла)</div>
                </div>
            </td>
        </tr>
    </table>
{/if}

<br />
<br />