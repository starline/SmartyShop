{include file='content/content_menu_part.tpl'}

{if $comment->id}
    {if $comment->type == 'product'}
        {$meta_title = 'Комментарий к товару $comment->entity->name' scope=global}
    {elseif $comment->type == 'blog'}
        {$meta_title = 'Комментарий к статье $comment->entity->name' scope=global}
    {/if}
{/if}

{if $message_success}
    <div class="message message_success">
        <span class="text">{if $message_success == 'updated'}Запись обновлена{/if}</span>
    </div>
{/if}

<form method=post class=form_css enctype="multipart/form-data">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input name=id type="hidden" value="{$comment->id|escape}" />


    <div class="columns">
        <div class="block_flex w100">

            <div class="over_name">
                {if $comment->type == 'product'}
                    <a class="out_link" target="_bkank" href="../product/{$comment->entity->id}">Открыть товар на сайте</a>
                {elseif $comment->type == 'blog'}
                    <a class="out_link" target="_bkank" href="../blog/{$comment->entity->url}">Открыть статью на
                        сайте</a>
                {/if}
            </div>

            <div class="name_row">
                <h1>{$comment->entity->name}</h1>
            </div>
        </div>

        <div class="block_flex">
            <ul class="property_block">
                <li>
                    <label for="date" class="property_name">Дата комментария</label>
                    <div class="with_unit">
                        <input type="text" name="date" id="date" class="small_inp" value="{$comment->date|date}" />
                        <span class="label_unit">в {$comment->date|time}</span>
                        <input type="hidden" name="time" value="{$comment->date|time}">
                    </div>
                </li>
                <li>
                    <label for="name" class="property_name">Имя</label>
                    <input id="name" name="name" type="text" autocomplete="given-name"
                        value="{$comment->name|escape}" />
                </li>
                <li>
                    <label for="text" class="property_name">Комментарий</label>
                    <textarea id="text" name="text">{$comment->text|escape}</textarea>
                </li>
            </ul>

            <div class="btn_row">
                <input class="button_green" type="submit" name="" value="Сохранить" />
            </div>
        </div>

    </div>
</form>

<script src="/{$config->templates_subdir}js/jquery/datepicker/jquery.ui.datepicker-ru.js"></script>
<script>
    {literal}
        $(function() {
            $('input[name="date"]').datepicker({
                regional: 'ru'
            });
        });
    {/literal}
</script>