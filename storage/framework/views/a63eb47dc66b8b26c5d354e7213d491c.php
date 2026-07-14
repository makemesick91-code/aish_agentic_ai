<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo e(config('app.name', 'Aish Agentic AI')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="antialiased">
    <main class="wrap">
        <section class="card" role="main">
            <p class="eyebrow">Aish Tech Solution</p>
            <h1>Aish Agentic AI</h1>
            <p class="lede">
                Agentic AI Customer Experience &amp; Reputation Operating Platform.
            </p>

            <p class="status" aria-label="Build status">
                <span class="dot" aria-hidden="true"></span>
                Runtime &amp; Repository Bootstrap — foundation only.
                <strong>Application implementation not started.</strong>
            </p>

            <ul class="notes">
                <li>This is a development bootstrap surface, not a production release.</li>
                <li>No tenant data, dashboards, or metrics are shown; none exist yet.</li>
                <li>Health probes: <code>/live</code> and <code>/ready</code>.</li>
            </ul>
        </section>
    </main>
</body>
</html>
<?php /**PATH /home/fikri/Projects/aish_agentic_ai/resources/views/welcome.blade.php ENDPATH**/ ?>