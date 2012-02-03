<?php $tpl->includeTpl('header.html', false, 0); ?>

<?php if ($tpl->getBlock('article')) : foreach ($tpl->getBlock('article') as $__tpl_blocs['article']){ ?>
<h1><a href="article/<?php echo $__tpl_blocs['article']['AID']; ?>/" class="article_title"><?php echo $__tpl_blocs['article']['TITRE']; ?></a></h1>
<div class="descr">
    Le <?php echo $__tpl_blocs['article']['F_DATE']; ?> par <strong><?php echo $__tpl_blocs['article']['M_PSEUDO']; ?></strong> dans 
    « <a href="<?php echo $__tpl_blocs['article']['C_SLUG']; ?>/"><?php echo $__tpl_blocs['article']['C_NOM']; ?></a> » -
    <a href="article/<?php echo $__tpl_blocs['article']['AID']; ?>/"><?php echo $__tpl_blocs['article']['NB_COMMENTS']; ?> commentaire(s)</a>
</div>
<div class="article">
<?php echo $__tpl_blocs['article']['F_CONTENU']; ?>
</div>
<hr />
<?php } endif; ?>

<?php if (isset($tpl->vars['NB_COMS']) AND $tpl->vars['NB_COMS'] != 0) : ?>
<h1>Commentaires</h1>

<?php if ($tpl->getBlock('com')) : foreach ($tpl->getBlock('com') as $__tpl_blocs['com']){ ?>
<div class="descr">Le <?php echo $__tpl_blocs['com']['F_DATE']; ?> par <strong><?php echo $__tpl_blocs['com']['F_AUTEUR']; ?></strong></div>
<p>
    <?php echo $__tpl_blocs['com']['F_COMMENTAIRE']; ?>
</p>
<?php } endif; ?>
<hr />
<?php endif; ?>

<?php if (isset($tpl->vars['SINGLE_ARTICLE']) AND $tpl->vars['SINGLE_ARTICLE']) : ?>
<?php if (isset($tpl->vars['ERREUR']) AND $tpl->vars['ERREUR']) : ?>
<h3>Erreur</h3>
<ul>
    <?php if ($tpl->getBlock('message')) : foreach ($tpl->getBlock('message') as $__tpl_blocs['message']){ ?>
        <li><strong><?php echo $__tpl_blocs['message']['FIELD_LABEL']; ?></strong> <?php echo $__tpl_blocs['message']['ERREUR']; ?></li>
    <?php } endif; ?>
</ul>
<?php endif; ?>

<h1>Écrire un commentaire</h1>

<?php echo (isset($tpl->vars['FORM'])) ? $tpl->vars['FORM'] : ""; ?>
<?php else : ?>
<p>Pages : 
<?php if ($tpl->getBlock('pagination')) : foreach ($tpl->getBlock('pagination') as $__tpl_blocs['pagination']){ ?>
<?php if ($__tpl_blocs['pagination']['PAGE_NUM'] == '...') : ?>
...
<?php else : ?>
<a href="index/page/<?php echo $__tpl_blocs['pagination']['PAGE_NUM']; ?>/" title="Voir la page <?php echo $__tpl_blocs['pagination']['PAGE_NUM']; ?>"><?php echo $__tpl_blocs['pagination']['PAGE_NUM']; ?></a>
<?php endif; ?>
<?php } endif; ?>
</p>
<?php endif; ?>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
