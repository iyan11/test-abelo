{extends file="../layout.tpl"}

{block name=content}
    <section class="page-intro">
        <div>
            <span class="section-kicker">Категория</span>
            <h1>{$category->name}</h1>
            <p>{$category->description}</p>
        </div>
        <div class="page-intro__badge">
            <span>Статей</span>
            <strong>{$total}</strong>
        </div>
    </section>

    <section class="toolbar">
        <div class="toolbar__label">Сортировка</div>
        <label class="select-wrap">
            <select onchange="window.location.href=this.value">
                <option value="?sort=date_desc" {if $sort == 'date_desc'}selected{/if}>Сначала новые</option>
                <option value="?sort=date_asc" {if $sort == 'date_asc'}selected{/if}>Сначала старые</option>
                <option value="?sort=views" {if $sort == 'views'}selected{/if}>По просмотрам</option>
            </select>
        </label>
    </section>

    {if empty($posts)}
        <section class="empty-state">
            <h2>В этой категории пока нет статей</h2>
            <p>Добавьте публикации и они появятся здесь с пагинацией и сортировкой.</p>
        </section>
    {else}
        <div class="post-list">
            {foreach $posts as $post}
                <article class="post-row">
                    <a href="{$post->getUrl()}" class="media-frame media-frame--row{if !$post->photo} is-placeholder{/if}">
                        {if $post->photo}
                            <img
                                src="{$post->photo}"
                                alt="{$post->title|escape}"
                                class="post-row__image"
                                loading="lazy"
                                onerror="this.closest('.media-frame').classList.add('is-placeholder'); this.remove();"
                            >
                        {/if}
                        <span class="media-placeholder">AB</span>
                    </a>

                    <div class="post-row__body">
                        <div class="post-meta">
                            <span>{$post->getFormattedDate()}</span>
                            <span>{$post->views} просмотров</span>
                            <span>{$post->getReadingTime()} мин чтения</span>
                        </div>

                        <h2><a href="{$post->getUrl()}">{$post->title}</a></h2>
                        <p>{$post->description|truncate:180}</p>
                        <a href="{$post->getUrl()}" class="text-link">Открыть статью</a>
                    </div>
                </article>
            {/foreach}
        </div>
    {/if}

    {if $totalPages > 1}
        <nav class="pagination" aria-label="Пагинация">
            {if $currentPage > 1}
                <a href="?page={$currentPage-1}&sort={$sort}" class="pagination__arrow">Назад</a>
            {/if}

            {for $page=1 to $totalPages}
                {if $page == $currentPage}
                    <strong class="pagination__current">{$page}</strong>
                {else}
                    <a href="?page={$page}&sort={$sort}">{$page}</a>
                {/if}
            {/for}

            {if $currentPage < $totalPages}
                <a href="?page={$currentPage+1}&sort={$sort}" class="pagination__arrow">Вперёд</a>
            {/if}
        </nav>
    {/if}
{/block}
