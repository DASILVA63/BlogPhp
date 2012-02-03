<?php $tpl->includeTpl('header.html', false, 0); ?>

<h2>Édito</h2>
<p class="extra">
    Le listing ci-dessous recense les commentaires postés sur le site. Qu'ils soient visibles ou non, ils apparaissent tous ici.
</p>

<h2>Liste des commentaires</h2>
<p>
    <table width=70%>
        <tr style="font-weight:bold; text-align:center; ">
            <td>Modifier</td>
            <!-- <td>Supprimer</td> -->
            <td>Titre</td>
            <td>Auteur</td>
            <td>Date</td>
            <td>Visible</td>
        </tr>
        
        <?php if ($tpl->getBlock('com')) : foreach ($tpl->getBlock('com') as $__tpl_blocs['com']){ ?>
        <tr style="text-align:center;">
            <td><a href="./admin/commentaires/edit/<?php echo $__tpl_blocs['com']['RID']; ?>/"><img src='./logo/modifier.png'/></a></td>
            <!--<td><a href="index.php?page=admin_coms&del=<?php echo $__tpl_blocs['com']['RID']; ?>">Supprimer</a></td> -->
            <td><a href="./article/<?php echo $__tpl_blocs['com']['A_ID']; ?>/"><?php echo $__tpl_blocs['com']['TITRE']; ?></a></td>
            <td><?php echo $__tpl_blocs['com']['AUTEUR']; ?></td>
            <td><?php echo $__tpl_blocs['com']['F_DATE']; ?></td>
            <td><?php if ($__tpl_blocs['com']['VALIDE']) : ?>Oui<?php else : ?>Non<?php endif; ?></td>
        </tr>
        <?php } endif; ?>
    </table>
</p>

<h2>Liens rapides</h2>
<ul>
    <li><a href="./admin/articles/new/">Rédiger un article</a></li>
    <li><a href="./admin/articles/">Gérer les articles</a></li>
</ul>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
