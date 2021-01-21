<div id="user">
    <?php if ($app->getAppUser() && $app->getAppUser()['isAdmin']):?>
        <a class="button" href="?page=admin" title="Beheerders pagina"><i class="fas fa-user-lock"></i></i><span> Admin</a>
    <?php endif ?>
    <?php if ($app->getAppUser()): ?>
        <a class="button" href="?page=profiel" title="Profiel aanpassen"><i class="far fa-id-card"></i><span> Profiel</span></a>
        <a class="button" href="?page=logout" title="Afmelden"><i class="fas fa-sign-out-alt"></i> <span>Afmelden</span></a>
    <?php else: ?>
        <a class="button" href="?page=inloggen" title="Aanmelden"><i class="fas fa-sign-in-alt"></i> <span>Aanmelden</span></a>
    <?php endif?>
    <?php if ($countShoppingCart = $app->countShoppingCart()) :?>
        <a class="button" href="?page=winkelwagen" title="Inhoud van uw winkelwagen"><i class="fas fa-shopping-cart"></i> <span>Winkelwagen </span> (<?php echo $countShoppingCart?>)</a>
    <?php endif?>
</div>
