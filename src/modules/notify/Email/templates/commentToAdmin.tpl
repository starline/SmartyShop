{if $comment->approved}
  {$subject="Новый комментарий от `$comment->name|escape`" scope=global}
{else}
  {$subject="Комментарий от `$comment->name|escape` ожидает одобрения" scope=global}
{/if}

{if $comment->approved}
  <h1 style="font-weight:normal;"><a href="{$config->root_url}/agmin?view=CommentAdmin&id={$comment->id}">Новый
      комментарий</a> от {$comment->name|escape}
  </h1>
{else}
  <h1 style="font-weight:normal;"><a href="{$config->root_url}/agmin?view=CommentAdmin&id={$comment->id}">Комментарий</a>
    от {$comment->name|escape} ожидает одобрения</h1>
{/if}

<table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
  <tr>
    <td style="padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;">
      Имя
    </td>
    <td style="padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;">
      {$comment->name|escape}
    </td>
  </tr>
  <tr>
    <td style="padding:6px; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;">
      Комментарий
    </td>
    <td style="padding:6px; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;">
      {$comment->text|escape|nl2br}
    </td>
  </tr>
  <tr>
    <td style="padding:6px; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;">
      Время
    </td>
    <td style="padding:6px; width:170; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;">
      {$comment->date|date} {$comment->date|time}
    </td>
  </tr>
  <tr>
    <td style="padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;">
      Статус
    </td>
    <td style="padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;">
      {if $comment->approved}
        Одобрен
      {else}
        Ожидает одобрения
      {/if}
    </td>
  </tr>
  <tr>
    <td style="padding:6px; width:170; background-color:#f0f0f0; border:1px solid #e0e0e0;font-family:arial;">
      {if $comment->type == 'product'}К товару{/if}
      {if $comment->type == 'blog'}К записи{/if}
    </td>
    <td style="padding:6px; width:330; background-color:#ffffff; border:1px solid #e0e0e0;font-family:arial;">
      {if $comment->type == 'product'}<a target="_blank"
        href="{$config->root_url}/tovar-{$comment->product->url}#comment_{$comment->id}">{$comment->product->name}</a>{/if}
      {if $comment->type == 'blog'}<a target="_blank"
        href="{$config->root_url}/blog/{$comment->post->url}#comment_{$comment->id}">{$comment->post->name}</a>{/if}
    </td>
  </tr>
</table>