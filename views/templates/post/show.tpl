{extends file="../layout.tpl"}

{block name=content}
    <article class="article-page">
        <section class="article-hero">
            <div class="article-hero__copy">
                <span class="section-kicker">Статья</span>
                <h1>{$post->title}</h1>
                <p class="article-lead">{$post->description}</p>

                <div class="post-meta">
                    <span>{$post->getFormattedDate()}</span>
                    <span>{$post->views} просмотров</span>
                    <span>{$post->getReadingTime()} мин чтения</span>
                </div>

                {if $categories|count > 0}
                    <div class="tag-list">
                        {foreach $categories as $cat}
                            <a href="{$cat->getUrl()}" class="tag-chip">{$cat->name}</a>
                        {/foreach}
                    </div>
                {/if}
            </div>

            <div class="media-frame media-frame--hero{if !$post->photo} is-placeholder{/if}">
                {if $post->photo}
                    <img
                        src="{$post->photo}"
                        alt="{$post->title|escape}"
                        class="article-hero__image"
                        loading="lazy"
                        onerror="this.closest('.media-frame').classList.add('is-placeholder'); this.remove();"
                    >
                {/if}
                <span class="media-placeholder">AB</span>
            </div>
        </section>

        <section class="article-content">
            {$post->content nofilter}
        </section>
    </article>

    {if $similarPosts|count > 0}
        <section class="similar-block">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Похожие материалы</span>
                    <h2>Что почитать дальше</h2>
                </div>
            </div>

            <div class="posts-grid">
                {foreach $similarPosts as $similar}
                    <article class="post-card post-card--compact">
                        <div class="post-card__body">
                            <div class="post-meta">
                                <span>{$similar->getFormattedDate()}</span>
                                <span>{$similar->views} просмотров</span>
                            </div>
                            <h3><a href="{$similar->getUrl()}">{$similar->title}</a></h3>
                            <p>{$similar->description|truncate:120}</p>
                            <a href="{$similar->getUrl()}" class="text-link">Перейти к статье</a>
                        </div>
                    </article>
                {/foreach}
            </div>
        </section>
    {/if}
{/block}
