<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

$app['blog.dir'] = __DIR__.'/../../blog';
$app['ga'] = 'UA-210649-11';

$app->before(function () use ($app) {
    $app['twig']->addGlobal('ga', $app['ga']);
});
