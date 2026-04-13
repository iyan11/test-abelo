<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title|default:$app_name}</title>
    <meta name="description" content="Небольшой блог о технологиях, дизайне и цифровых продуктах.">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="page-shell">
    <header class="site-header">
        <div class="container header-row">
            <a href="/" class="brand-mark">
                <span class="brand-mark__eyebrow">Абело</span>
                <strong class="brand-mark__title">Блог о технологиях</strong>
            </a>

            <nav class="site-nav">
                <a href="/">Главная</a>
            </nav>
        </div>
    </header>

    <main class="container main-content">
        {if isset($flash_message)}
            <div class="flash-message flash-{$flash_type|default:'success'}">
                {$flash_message}
            </div>
        {/if}

        {block name=content}{/block}
    </main>

    <footer class="site-footer">
        <div class="container footer-row">
            <div>
                <strong>{$app_name|default:'Abelo'}</strong>
                <p>Чистый PHP + Smarty.</p>
            </div>
            <div class="footer-note">{$current_year}</div>
        </div>
    </footer>
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>
