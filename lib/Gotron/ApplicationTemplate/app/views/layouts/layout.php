<!doctype html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
<head>
    <?= meta_tag(['http-equiv' => "Content-Type", 'content' => "text/html; charset=utf-8"]) ?>
    <?= meta_includes($includes) ?>
    <?= css_tag('screen') ?>
    <?= css_includes($includes) ?>
    <?= javascript_includes($includes) ?>
    <title><?= isset($title) ? $title : "@app_namespace" ?></title>
</head>
    <body>
        <?= $yield ?>
    </body>
</html>
