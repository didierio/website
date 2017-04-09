<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

$app->get('/', function () use ($app) {
    $blog = new Blog($app['blog.dir']);

    return $app['twig']->render('index.html.twig', array(
        'posts' => $blog->all(),
    ));
})->bind('homepage');

$app->get('/post/{slug}', function (Request $request, $slug) use ($app) {
    $blog = new Blog($app['blog.dir']);

    try {
        $post = $blog->find($slug);
    } catch (\LogicException $e) {
        throw new NotFoundHttpException(sprintf('Post "%s" not found', $slug));
    }

    return $app['twig']->render('blog_post.html.twig', array(
        'post' => $post,
    ));
})->bind('blog_post');

$app->get('sitemap.xml', function (Request $reqest) use ($app) {
    $router = $app['url_generator'];
    $now = new \DateTime();

    $urls[] = [
        'url' => $router->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
        'lastmod' => $now->format('r'),
        'priority' => 1,
    ];

    $blog = new Blog($app['blog.dir']);

    foreach ($blog->all() as $post) {
        $urls[] = [
            'url' => $router->generate('blog_post', ['slug' => $post['slug']], UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => $post['updated_at']->format('r'),
            'priority' => 1,
        ];
    }

    return $app['twig']->render('sitemap.xml.twig', array(
        'urls' => $urls,
    ));
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
