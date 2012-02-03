<?php $tpl->includeTpl('header.html', false, 0); ?>

<?php if (isset($tpl->vars['ERREUR']) AND $tpl->vars['ERREUR']) : ?>
<h3>Erreur</h3>
<ul>
    <?php if ($tpl->getBlock('message')) : foreach ($tpl->getBlock('message') as $__tpl_blocs['message']){ ?>
        <li><strong><?php echo $__tpl_blocs['message']['FIELD_LABEL']; ?></strong> <?php echo $__tpl_blocs['message']['ERREUR']; ?></li>
    <?php } endif; ?>
</ul>
<?php endif; ?>

<?php if (isset($tpl->vars['MESSAGE']) AND $tpl->vars['MESSAGE'] != '') : ?>
<h3>Message</h3>
<p>
    <?php echo (isset($tpl->vars['MESSAGE'])) ? $tpl->vars['MESSAGE'] : ""; ?>
</p>
<?php endif; ?>

<h1><?php echo (isset($tpl->vars['PAGE_TITLE'])) ? $tpl->vars['PAGE_TITLE'] : ""; ?></h1>
<p>
    <?php echo (isset($tpl->vars['FORM'])) ? $tpl->vars['FORM'] : ""; ?>
</p>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
