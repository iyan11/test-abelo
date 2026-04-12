{extends file="layout.tpl"}

{block name=content}
    <h1>Добро пожаловать на {$app_name}</h1>
    <p>Страница со Smarty.</p>

    <h2>Server Information:</h2>
    <ul>
        <li>Current Time: {$smarty.now|date_format:"%Y-%m-%d %H:%M:%S"}</li>
        <li>PHP Version: {$smarty.const.PHP_VERSION}</li>
        <li>Smarty Version: {$smarty.version}</li>
    </ul>

    {if $user}
        <div class="user-info">
            <h3>Welcome back, {$user.name}!</h3>
            <p>Email: {$user.email}</p>
        </div>
    {/if}
{/block}