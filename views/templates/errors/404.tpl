{extends file="../layout.tpl"}

{block name=content}
    <section class="empty-state empty-state--error">
        <span class="section-kicker">Ошибка 404</span>
        <h1>{$title|default:'Страница не найдена'}</h1>
        <p>Похоже, ссылка устарела или материал был перемещён. Вернитесь на главную и выберите актуальную публикацию.</p>
        <a href="/" class="btn btn-primary">На главную</a>
    </section>
{/block}
