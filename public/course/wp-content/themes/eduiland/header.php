<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3">

    <?php if(class_exists('Edu') && eduiland()->user->environment === 'production'): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-179430737-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-179430737-2');
    </script>
    <?php endif; ?>

    <?php if (defined('ABSPATH')) { wp_head(); } ?>
</head>
<body <?php body_class();?>>
<header id="header" class="header">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand logo" href="/dashboard" title="<?php echo get_bloginfo(); ?> Home"><span class="sr"><?php echo get_bloginfo(); ?></span></a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">

            <?php
            if(class_exists('Edu')) {
                // User (teacher, student, and school admin) Menu
                get_template_part('templates/header/nav-user');


                // Utility Menu
                get_template_part('templates/header/nav-utility');
            }
            ?>
        </div>
    </nav>
</header>

<main class="main" role="main" id="main">
<div class="main-overlay"></div>
<?php get_template_part('templates/sections/masthead') ?>
<div class="container"><div class="row">
