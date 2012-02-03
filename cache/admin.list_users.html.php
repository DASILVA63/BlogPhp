<?php $tpl->includeTpl('header.html', false, 0); ?>

<h2>Édito</h2>
<p class="extra">
    Le listing ci-dessous recense les utilisateurs enregistrés sur le site. Qu'ils soient actifs ou non, ils apparaissent tous ici.
</p>

<h2>Liste des utilisateurs</h2>
<p>
    <table width=90%>
        <tr style="font-weight:bold; text-align:center; " >
            <td>Modifier  </td>
            <td>Groupe  </td>
            <td>Pseudo  </td>
            <td>Nom  </td>
            <td>Mail  </td>
            <td>Date d'inscription  </td>
            <td>Activé  </td>
        </tr>
        
        <?php if ($tpl->getBlock('user')) : foreach ($tpl->getBlock('user') as $__tpl_blocs['user']){ ?>
        <tr style="text-align:center;">
            <td><a href="./admin/users/edit/<?php echo $__tpl_blocs['user']['MID']; ?>/"><img src='./logo/modifier.png'/></a></td>
            <td><?php echo $__tpl_blocs['user']['G_TITLE']; ?></a></td>
            <td><?php echo $__tpl_blocs['user']['M_PSEUDO']; ?></td>
            <td><?php echo $__tpl_blocs['user']['M_PRENOM']; ?> <?php echo $__tpl_blocs['user']['M_NOM']; ?></td>
            <td><?php echo $__tpl_blocs['user']['M_MAIL']; ?></td>
            <td><?php echo $__tpl_blocs['user']['F_DATE']; ?></td>
            <td><?php if ($__tpl_blocs['user']['M_ACTIVATED']) : ?>Oui<?php else : ?>Non<?php endif; ?></td>
        </tr>
        <?php } endif; ?>
    </table>
</p>

<h2>Liens rapides</h2>
<ul>
    <li><a href="./admin/users/new/">Ajouter un utilisateur</a></li>
</ul>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
