<div class="categories_tree">

    {function name=categories_tree}
        {if $categories}
            <ul>
                {if $categories[0]->parent_id == 0}
                    <li class="cat_1 {if !$category->id}selected{/if}">
                        <a href="{url category_id=null}">Все категории</a>
                    </li>
                {/if}
                {foreach item=c from=$categories}
                    <li data-category="{$c->id}" class="
                        {if $c->subcategories AND $c->parent_id != 0} accordion {if $category->parent_id == $c->id || $category->id == $c->id} open {/if}{/if}
                        {if $c->parent_id == 0} cat_1 {/if} 
                        {if $category->id == $c->id} selected {/if}
                        {if !$c->visible} invisible  {/if}">
                        <div category_id="{$c->id}" class="link droppable category">
                            <a href="{url keyword=null category_id=$c->id}" {if $c->name|count_characters:true > 25}title="{$c->name|escape}"
                                {/if}>{$c->name|escape|truncate:25:' …':false:false}</a>
                        </div>

                        {categories_tree categories=$c->subcategories}
                    </li>
                {/foreach}
            </ul>
        {/if}
    {/function}

    {categories_tree categories=$categories}


    <script>
        {literal}
            $(function() {
                let b = function(c, e) {
                    this.el = c || {};
                    this.multiple = e || false;
                    let d = this.el.find(" .link");
                    d.on("click", {el: this.el, multiple: this.multiple}, this.dropdown);
                }
                b.prototype.dropdown = function(d) {
                    let c = d.data.el;
                    $this = $(this), $next = $this.next();
                    $next.slideToggle();
                    $this.parent().toggleClass("open");
                    if (!d.data.multiple) {
                        c.find(".submenu").not($next).slideUp().parent().removeClass("open");
                    }
                }
                let a = new b($(".accordion "), true);
            });
        {/literal}
    </script>
</div>