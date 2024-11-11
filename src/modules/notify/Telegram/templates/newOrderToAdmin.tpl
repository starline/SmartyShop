Новый заказ <b><a href="{$config->root_url}/agmin?view=OrderAdmin&id={$order->id}">№{$order->id}</a></b>{PHP_EOL}
<b>Заказчик:</b>
{if !$order->name|empty}{$order->name|escape}{PHP_EOL}{/if}
{if !$order->phone|empty}{$order->phone|escape}{PHP_EOL}{/if}
{if !$order->email|empty}{$order->email|escape}{PHP_EOL}{/if}
{if !$order->address|empty}{$order->address|escape}{PHP_EOL}{/if}
{if !$order->comment|empty}{PHP_EOL}<blockquote>{PHP_EOL}<i>{$order->comment|escape}</i>{PHP_EOL}{PHP_EOL}</blockquote>{/if}{PHP_EOL}
{if !$purchases|empty}Товары:
{foreach $purchases as $purchase}<b>{$purchase@index + 1}. {$purchase->product_name|escape}</b> {$purchase->variant_name|escape}{if $purchase->sku} ({$purchase->sku|escape}){/if} - {$purchase->price|price_format} {$currency->sign} - {$purchase->amount} {$settings->units}{PHP_EOL}{/foreach}{PHP_EOL}{/if}
{if !$delivery_method->name|empty}<b>Доставка:</b> {$delivery_method->name|escape}{PHP_EOL}{PHP_EOL}{/if}
{if !$payment_method->name|empty}<b>Оплата:</b> {$payment_method->name|escape}{PHP_EOL}{PHP_EOL}{/if}
К оплате: <b>{$order->payment_price}</b> {$currency->sign}
{$url = "{$config->root_url}/agmin?view=OrderAdmin&id={$order->id}" scope=global}{$url_text = "Открыть заказ на сайте" scope=global}