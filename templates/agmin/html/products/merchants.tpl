{include file='products/products_settings_menu_part.tpl'}

{$meta_title='Прайсы' scope=global}

<div id=header class=header_top>
    <h1>{$meta_title}</h1>
    <a class="add" href="{url view=MerchantAdmin}">Добавить прайс</a>
</div>

{if $merchants}
    <div id="main_list" class="finance">
        <form id="list_form" method="post">
            <input type="hidden" name="session_id" value="{$smarty.session.id}" />

            <div class="list">
                {foreach $merchants as $m}
                    <div class="row" item_id="{$m->id}">

                        <input type="hidden" name="positions[{$m->id}]" value="{$m->sort}">
                        <div class="move">
                            <div class="move_zone"></div>
                        </div>

                        <div class="checkbox">
                            <input type="checkbox" name="check[]" value="{$m->id}" />
                        </div>

                        <div class="user_name">
                            <a href="{url view=MerchantAdmin id=$m->id}">{$m->name}</a>
                            <div class="round_box">
                                id{$m->id}
                            </div>
                        </div>

                        <div class="icons">
                            <a class="delete" title="Удалить" href="#"></a>
                        </div>
                    </div>
                {/foreach}
            </div>

            <div id="action">
                <span id="check_all" class='dash_link'>Выбрать все</span>
                <span id="select">
                    <select name="action">
                        <option value="">Выбрать действие</option>
                        <option value="delete">Удалить</option>
                    </select>
                </span>
                <input id="apply_action" class="button_green" type="submit" value="Применить">
            </div>
        </form>
    </div>
{/if}


<script>
    {literal}
        $(function() {

            // Сортировка списка
            $(".list").sortable({
                items: ".row",
                handle: ".move_zone",
                tolerance: "pointer",
                opacity: 0.90,
                axis: 'y',
                update: function(event, ui) {
                    $("#list_form input[name*='check']").prop('checked', false);
                    $("#list_form").ajaxSubmit(function() {
                        colorize();
                    });
                }
            });

        });
    {/literal}
</script>