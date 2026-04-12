<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title|default:$app_name}</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        nav { background: #333; color: white; padding: 1rem; }
        nav .container { max-width: 1200px; margin: 0 auto; }
        nav a { color: white; text-decoration: none; margin-right: 1rem; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .flash-message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<nav>
    <div class="container">
        <a href="/">Главная страница</a>
    </div>
</nav>

<div class="container">
    {if isset($flash_message)}
        <div class="flash-message flash-{$flash_type|default:'success'}">
            {$flash_message}
        </div>
    {/if}

    {block name=content}{/block}
</div>

<script src="/assets/js/main.js"></script>
</body>
</html>