<?php
defined('C5_EXECUTE') or die('Access Denied.');
?>

<div class="ccm-dashboard-header-buttons btn-group">

</div>

<div class="ccm-dashboard-content-inner">
    <form method="post" action="<?php echo $this->action('save'); ?>">
        <?php
        /** @var $token \Concrete\Core\Validation\CSRF\Token */
        echo $token->output('a3020.shortcodes.settings');
        ?>

        <div class="form-group">
            <label class="control-label launch-tooltip"
                   title="<?php echo t('If disabled, %s will be completely turned off.', t('Shortcodes')) ?>"
                   for="isEnabled">
                <?php
                /** @var bool $isEnabled */
                echo $form->checkbox('isEnabled', 1, $isEnabled);
                ?>
                <?php echo t('Enable %s', t('Shortcodes')); ?>
            </label>
        </div>

        <div class="form-group">
            <label class="control-label launch-tooltip"
                   title="<?php echo t("Keep track of the pages where shortcodes have been found. This is disabled by default because it adds some overhead. We don't recommend enabling this on high traffic websites.") ?>"
                   for="trackUsage">
                <?php
                /** @var bool $trackUsage */
                echo $form->checkbox('trackUsage', 1, $trackUsage);
                ?>
                <?php echo t('Track usage'); ?>
            </label>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <button class="pull-right btn btn-primary" type="submit"><?php echo t('Save') ?></button>
            </div>
        </div>
    </form>
</div>
