<html>
    <head>
        <title>Test Application</title>
        <?= javascript_tag("jquery") ?>
        <?= css_tag("application") ?>
        <?= javascript_includes($includes) ?>
        <?= css_includes($includes) ?>
    </head>
    <body>
        <header>
            <h1>Test Application</h1>
        </header>
        <div>
            <?= $yield ?>
        </div>
        <footer>This is the footer</footer>
    </body>
</html>