<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Support\Facade\Url;
?>
<p><?php echo t('Congratulations, the add-on has been installed!'); ?></p>
<br>

<p>
    <?php
    echo t('To manage your shortcodes, go to %s.', t('Shortcodes'));
    ?>
</p><br>

<a class="btn btn-primary" href="<?php echo Url::to('/dashboard/shortcodes/search') ?>">
    <?php
    echo t('View shortcodes');
    ?>
</a>
