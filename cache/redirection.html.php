<?php $tpl->includeTpl('header.html', false, 0); ?>

<h2>Message</h2>
<p class="extra">
    <?php echo (isset($tpl->vars['MESSAGE'])) ? $tpl->vars['MESSAGE'] : ""; ?>
    
    <p>
        <a href="<?php echo (isset($tpl->vars['URL'])) ? $tpl->vars['URL'] : ""; ?>">Cliquez ici pour ne pas attendre</a>
    </p>
</p>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
