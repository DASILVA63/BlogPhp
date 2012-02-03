</div>

        <div class="sidenav">

            <h1>Recherche</h1>
            <div>
            <form action="" method="get">
                <p>
                    <br />
                    <input type="text" name="recherche" value="<?php echo (isset($tpl->vars['RECHERCHE'])) ? $tpl->vars['RECHERCHE'] : ""; ?>" class="styled" />
                    <input type="submit" value="Go !" class="button" />
                </p>
            </form>
            </div>
            
            <h1>Navigation</h1>
            <ul>
                <li><a href="index/">Accueil</a></li>   
                <?php if (isset($tpl->vars['IS_LOGGED_IN']) AND $tpl->vars['IS_LOGGED_IN']) : ?>
                    <?php if (isset($tpl->vars['IS_ADMIN']) AND $tpl->vars['IS_ADMIN']) : ?>
                        <li><a href="admin/">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="deconnexion/">Déconnexion</a></li>
                <?php else : ?>
                    <li><a href="connexion/">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="clearer"><span></span></div>
    </div>
</div>

<div class="footer">
    Valid <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> &amp; <a href="http://validator.w3.org/check?uri=referer">XHTML</a>. Design par <a href="http://arcsin.se">Arcsin</a> et site web par <b>Jonathan DA SILVA et Yoann VIEIRA DA SILVA<b>.
</div>
</body>
</html>
