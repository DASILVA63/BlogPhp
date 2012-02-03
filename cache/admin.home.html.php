<?php $tpl->includeTpl('header.html', false, 0); ?>

<h1>Articles</h1>
<ul>
    <li><a href="./admin/articles/new/">Rédiger un article</a></li>
    <li><a href="./admin/articles/">Gérer les articles</a> (<?php echo (isset($tpl->vars['NB_ARTICLES'])) ? $tpl->vars['NB_ARTICLES'] : ""; ?>)</li>
    <li><a href="./admin/commentaires/">Gérer les commentaires</a> (<?php echo (isset($tpl->vars['NB_COMS'])) ? $tpl->vars['NB_COMS'] : ""; ?>)</li>
</ul>

<h1>Utilisateurs</h1>
<ul>
    <li><a href="./admin/users/new/">Ajouter un utilisateur</a></li>
    <li><a href="./admin/users/">Gérer les utilisateurs</a> (<?php echo (isset($tpl->vars['NB_USERS'])) ? $tpl->vars['NB_USERS'] : ""; ?>)</li>
</ul>

<?php $tpl->includeTpl('footer.html', false, 0); ?>
