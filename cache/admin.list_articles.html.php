<?php $tpl->includeTpl('header.html', false, 0); ?>

<h2>Édito</h2>
<p class="extra">
    Le listing ci-dessous recense les articles publiés sur le site. Qu'ils soient visibles ou non, ils apparaissent tous ici.
</p>

<h2>Liste des articles</h2>
<p>
    <table width=70%>
        <tr style="font-weight:bold; text-align:center; ">
            <td>Modifier</td>
            <!-- <td>Supprimer</td> -->
            <td>Titre</td>
            <td>Auteur</td>
            <td>Date</td>
            <td>Nb de commentaires</td>
            <td>Visible</td>
        </tr>
        
        <?php if ($tpl->getBlock('article')) : foreach ($tpl->getBlock('article') as $__tpl_blocs['article']){ ?>
        <tr style="text-align:center;">
            <td><a href="./admin/articles/edit/<?php echo $__tpl_blocs['article']['AID']; ?>/"><img src='./logo/modifier.png'/></a></td>
            <!--<td><a href="index.php?page=admin_articles&del=<?php echo $__tpl_blocs['article']['AID']; ?>">Supprimer</a></td> -->
            <td><a href="./article/<?php echo $__tpl_blocs['article']['AID']; ?>/"><?php echo $__tpl_blocs['article']['TITRE']; ?></a></td>
            <td><?php echo $__tpl_blocs['article']['M_PSEUDO']; ?></td>
            <td><?php echo $__tpl_blocs['article']['F_DATE']; ?></td>
            <td><?php echo $__tpl_blocs['article']['NB_COMMENTS']; ?></td>
            <td><?php if ($__tpl_blocs['article']['VALIDE']) : ?>Oui<?php else : ?>Non<?php endif; ?></td>
        </tr>
        <?php } endif; ?>
    </table>
</p>

<h2>Liens rapides</h2>
<ul>
    <li><a href="./admin/articles/new/">Rédiger un article</a></li>
    <li><a href="./admin/commentaires/">Gérer les commentaires</a></li>
</ul>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
