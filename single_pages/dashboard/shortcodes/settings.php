<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;

/** @var $isEnabled bool */
/** @var $trackUsage bool */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var $token Token */
/** @noinspection PhpUnhandledExceptionInspection */
$token = $app->make(Token::class);
?>

<div class="ccm-dashboard-header-buttons btn-group">
    <?php /** @noinspection PhpUnhandledExceptionInspection */
    View::element("dashboard/help", [], "shortcodes"); ?>
</div>

<?php /** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/did_you_know", [], "shortcodes"); ?>

<div class="ccm-dashboard-content-inner">
    <form method="post" action="<?php echo Url::to("/dashboard/shortcodes/settings")?>">
        <?php echo $token->output('update_settings'); ?>

        <div class="form-group">
            <div class="form-check">
                <?php echo $form->checkbox('isEnabled', 1, $isEnabled, ["class" => "form-check-input"]); ?>
                <?php echo $form->label('isEnabled', t('Enable %s', t('Shortcodes')), ["class" => "launch-tooltip form-check-label", "title" => t('If disabled, %s will be completely turned off.', t('Shortcodes'))]); ?>
            </div>

            <div class="form-check">
                <?php echo $form->checkbox('trackUsage', 1, $trackUsage, ["class" => "form-check-input"]); ?>
                <?php echo $form->label('trackUsage', t('Track usage'), ["class" => "launch-tooltip form-check-label", "title" => t("Keep track of the pages where shortcodes have been found. This is disabled by default because it adds some overhead. We don't recommend enabling this on high traffic websites.")]); ?>
            </div>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <button class="float-end btn btn-primary" type="submit">
                    <?php echo t('Save') ?>
                </button>
            </div>
        </div>
    </form>
</div>
