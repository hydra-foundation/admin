<?php /** @var \Hydra\View\Template $this */ ?>
<?php /** @var \Hydra\Admin\ViewModels\ScreenViewModel $screen */ ?>
<nav class="admin-sidebar">
    <a class="admin-brand" href="/admin">Hydra</a>

    <?= $this->partial('admin/partials/nav', ['screen' => $screen, 'oob' => false]) ?>

    <form class="admin-signout" hx-post="/logout">
        <button class="btn btn-sm btn-outline-secondary w-100" type="submit">Sign out</button>
    </form>
</nav>
