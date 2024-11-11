{if $payment_settings->sms_text}
    <span class="icons {if $order->settings->payment_sms}sms_button{/if}">
        <a class="send_sms send_payment_sms" title="Отправить смс c информацией об оплате" href="#">Отправить смс с
            номером карты</a>
    </span>
    <div class="sms_template">
        SMS: {$payment_settings->sms_text}
    </div>
{/if}

<h3>Реквизиты оплаты</h3>
<div><span class='property_name'>Название Банка: </span>{$payment_settings->bank_name}</div>
<div><span class='property_name'>Номер карты: </span>{$payment_settings->card_number}</div>
<div><span class='property_name'>Владелец карты: </span>{$payment_settings->card_owner}</div>

<script>
    {literal}
        $(function() {

            // Отправить SMS c информацией об оплате
            $("a.send_payment_sms").on('click', function() {
                let icon = $(this);
                let line = icon.closest(".icons");
                let id = $('input[name="id"]').val();
                let state = line.hasClass('sms_button') ? 1 : 0;

                icon.addClass('loading_icon');

                $.ajax({
                    type: 'POST',
                    url: '/app/agmin/ajax/send_sms.php',
                    data: {'id': id, 'session_id': session_id, 'type': 'payment'},
                    success: function(data) {
                        icon.removeClass('loading_icon');
                        if (!state)
                            line.addClass('sms_button');
                    },
                    dataType: 'json'
                });
                return false;
            });
        });
    {/literal}
</script>