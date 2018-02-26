<!doctype <?php setcooki_document('doc.type'); ?>>
<!--[if IE 9]><html <?php setcooki_document('html.lang'); ?> class="lt-ie10"><![endif]-->
<!--[if IE 10]><html <?php language_attributes(); ?> class="ie10"><![endif]-->
<html <?php language_attributes(); ?> class="no-js">
<head>
    <title><?php setcooki_document('title'); ?></title>
    <?php setcooki_document('meta'); ?>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
   	<link rel="profile" href="http://gmpg.org/xfn/11">
   	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php setcooki_document('head'); ?>
    <?php setcooki_document('link'); ?>
    <?php setcooki_document('script'); ?>
    <?php setcooki_document('style'); ?>
    <link rel="stylesheet" href="/wp-content/themes/theme1/static/css/main.css" />
    <link rel="stylesheet" href="/wp-content/themes/theme1/static/css/test.css" />
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/what-input/4.2.0/what-input.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/foundation/6.3.1/js/foundation.min.js" integrity="sha256-Nd2xznOkrE9HkrAMi4xWy/hXkQraXioBg9iYsBrcFrs=" crossorigin="anonymous"></script>
    <script src="/wp-content/themes/theme1/static/js/main.js" crossorigin="anonymous"></script>
    <script src="/wp-content/themes/theme1/static/js/test.js" crossorigin="anonymous"></script>
    <?php setcooki_document('inline.style'); ?>
    <?php setcooki_document('inline.script'); ?>
</head>

<body class="<?php setcooki_document('body.class'); ?>">
    <header id="header" class="header">
        <div class="site-title"><span style="color:#6B8CCB">SET</span>COOKI<code>WP</code></div>
    </header>
    <?php echo setcooki_handle('Header::mainnav'); ?>
    <main id="main" class="main">