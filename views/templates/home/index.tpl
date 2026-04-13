{extends file="../layout.tpl"}

{block name=content}
    <section class="hero-panel">
        <div class="hero-panel__copy">
            <span class="section-kicker">Подборка материалов</span>
            <h1>Свежие статьи</h1>
            <p>
                На главной собраны только категории, в которых уже есть публикации.
                Для каждой показываются три последних материала.
            </p>
        </div>
        <div class="hero-panel__stats">
            <div class="stat-card">
                <span>Категорий</span>
                <strong>{$categories|count}</strong>
            </div>
            <div class="stat-card">
                <span>Формат</span>
                <strong>PHP + Smarty</strong>
            </div>
        </div>
    </section>

    {if !empty($message)}
        <section class="empty-state">
            <h2>Пока нет опубликованных статей</h2>
            <p>{$message}</p>
        </section>
    {elseif empty($categories)}
        <section class="empty-state">
            <h2>Категории пока пусты</h2>
            <p>Запустите сиды.</p>
        </section>
    {else}
        <div class="category-stack">
            {foreach $categories as $category}
                <section class="category-section">
                    <div class="section-heading">
                        <div>
                            <span class="section-kicker">Категория</span>
                            <h2>{$category->name}</h2>
                            <p>{$category->description}</p>
                        </div>
                        <a href="{$category->getUrl()}" class="btn btn-primary">Все статьи</a>
                    </div>

                    <div class="posts-grid">
                        {foreach $category->recentPosts as $post}
                            <article class="post-card">
                                <a href="{$post->getUrl()}" class="media-frame{if !$post->photo} is-placeholder{/if}">
                                    {if $post->photo}
                                        <img
                                            src="{$post->photo}"
                                            alt="{$post->title|escape}"
                                            class="post-card__image"
                                            loading="lazy"
                                            onerror="this.closest('.media-frame').classList.add('is-placeholder'); this.remove();"
                                        >
                                    {/if}
                                    <span class="media-placeholder">AB</span>
                                </a>

                                <div class="post-card__body">
                                    <div class="post-meta">
                                        <span>{$post->getFormattedDate()}</span>
                                        <span>{$post->views} просмотров</span>
                                        <span>{$post->getReadingTime()} мин чтения</span>
                                    </div>

                                    <h3><a href="{$post->getUrl()}">{$post->title}</a></h3>
                                    <p>{$post->description|truncate:135}</p>

                                    <a href="{$post->getUrl()}" class="text-link">Читать статью</a>
                                </div>
                            </article>
                        {/foreach}
                    </div>
                </section>
            {/foreach}
        </div>
    {/if}
{/block}
