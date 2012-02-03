<?php $tpl->includeTpl('header.html', false, 0); ?>

<?php if ($tpl->getBlock('album')) : foreach ($tpl->getBlock('album') as $__tpl_blocs['album']){ ?>
<div style="float:left; width:50%; padding-bottom:15px;">
    <h1><a href="album/<?php echo $__tpl_blocs['album']['ALID']; ?>/" class="article_title"><?php echo $__tpl_blocs['album']['TITRE']; ?></a></h1>
    <div class="descr">
        <?php echo $__tpl_blocs['album']['F_DESCRIPTION']; ?>
    </div>
</div>
<?php } endif; ?>

<div class="clearer">&nbsp;</div>

<p>Pages : 
<?php if ($tpl->getBlock('pagination')) : foreach ($tpl->getBlock('pagination') as $__tpl_blocs['pagination']){ ?>
<?php if ($__tpl_blocs['pagination']['PAGE_NUM'] == '...') : ?>
...
<?php else : ?>
<a href="photos/page/<?php echo $__tpl_blocs['pagination']['PAGE_NUM']; ?>/" title="Voir la page <?php echo $__tpl_blocs['pagination']['PAGE_NUM']; ?>"><?php echo $__tpl_blocs['pagination']['PAGE_NUM']; ?></a>
<?php endif; ?>
<?php } endif; ?>
</p>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
