<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
    <base href="<?php echo (isset($tpl->vars['SITE_URL'])) ? $tpl->vars['SITE_URL'] : ""; ?>" /> 
    <title><?php echo (isset($tpl->vars['PAGE_TITLE'])) ? $tpl->vars['PAGE_TITLE'] : ""; ?> - <?php echo (isset($tpl->vars['SITE_TITLE'])) ? $tpl->vars['SITE_TITLE'] : ""; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="<?php echo (isset($tpl->vars['SITE_URL'])) ? $tpl->vars['SITE_URL'] : "";  echo (isset($tpl->vars['TPL_DIR'])) ? $tpl->vars['TPL_DIR'] : ""; ?>default.css" media="screen"/>
    <?php echo (isset($tpl->vars['ADD_HEADER'])) ? $tpl->vars['ADD_HEADER'] : ""; ?>
    <?php echo (isset($tpl->vars['JAVASCRIPT'])) ? $tpl->vars['JAVASCRIPT'] : ""; ?>
</head>

<body>

<div class="container">
    <div class="header">
        <div class="title">
            <h1><a href="index/" title="Retour à la page d'accueil"><?php echo (isset($tpl->vars['PAGE_TITLE'])) ? $tpl->vars['PAGE_TITLE'] : ""; ?> - <?php echo (isset($tpl->vars['SITE_TITLE'])) ? $tpl->vars['SITE_TITLE'] : ""; ?></a></h1>
        </div>
        <div class="navigation">
            <a href="index/">Accueil</a>
            <?php if (isset($tpl->vars['IS_LOGGED_IN']) AND $tpl->vars['IS_LOGGED_IN']) : ?>
            <?php if (isset($tpl->vars['IS_ADMIN']) AND $tpl->vars['IS_ADMIN']) : ?><a href="admin/">Administration</a><?php endif; ?>
            <a href="deconnexion/">Déconnexion</a>
            <?php else : ?>
            <a href="connexion/">Connexion</a>
            <?php endif; ?>
            <div class="clearer"><span></span></div>
        </div>
    </div>

    <div class="main">
        <div class="content">
