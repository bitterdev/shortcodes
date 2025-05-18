<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Bitter\Shortcodes\Entity\Shortcode;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;
use Concrete\Package\Shortcodes\Controller\SinglePage\Dashboard\Shortcodes\Search;

/** @var Search $this */
/** @var Shortcode $shortcode */
/** @var array $resolveStrategyOptions */

$app = Application::getFacadeApplication();
/** @var Form $form */
/** @noinspection PhpUnhandledExceptionInspection */
$form = $app->make(Form::class);
/** @var $token Token */
/** @noinspection PhpUnhandledExceptionInspection */
$token = $app->make(Token::class);
?>

<div class="ccm-dashboard-header-buttons">
    <div class="btn-group">
        <?php if ($shortcode->exists()) { ?>
            <a class="btn btn-danger" href="<?php echo $this->action('delete', $shortcode->getId()); ?>">
                <?php echo t('Delete shortcode'); ?>
            </a>
        <?php } ?>

        <?php /** @noinspection PhpUnhandledExceptionInspection */
        View::element("dashboard/help", [], "shortcodes"); ?>
    </div>
</div>

<?php /** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/did_you_know", [], "shortcodes"); ?>

<div class="ccm-dashboard-content-inner">
    <form method="post" action="<?php echo $this->action('save'); ?>">
        <?php echo $token->output('save_shortcut'); ?>

        <?php if ($shortcode->exists()) {
            echo $form->hidden('id', $shortcode->getId());
        } ?>

        <div class="form-group">
            <div class="form-check">
                <?php echo $form->checkbox('isActive', 1, $shortcode->isActive(), ["class" => "form-check-input"]); ?>
                <?php echo $form->label('isActive', t('Is active'), ["class" => "form-check-label launch-tooltip", "title" => t('If inactive, the shortcode will not be replaced.')]); ?>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label("shortcode", t('Shortcode')); ?>
            <?php echo $form->text('shortcode', $shortcode->getShortcode(), ['required' => 'required']); ?>

            <div class="help-block small">
                <?php echo t("This text that will be replaced. For example you can add 'year' as shortcode and replace it with %s. We recommend to use lowercase only and to use underscores instead of spaces.", date('Y')); ?>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label("resolveStrategy", t('Replace the shortcode with')); ?>
            <?php echo $form->select('resolveStrategy', $resolveStrategyOptions, $shortcode->getResolveStrategy()); ?>

            <div class="help-block small">
                <?php echo t('Choose how the shortcode should be replaced.'); ?>
            </div>
        </div>

        <div class="form-group <?php echo $shortcode->shouldResolveByString() ? '' : ' d-none' ?>" id="stringValueWrap">
            <?php echo $form->label("stringValue", t('Value')); ?>
            <?php echo $form->textarea('stringValue', $shortcode->getValue(), ['style' => 'min-height: 200px;']); ?>

            <div class="help-block small">
                <?php echo t('The shortcode will be replaced with this value.'); ?>
            </div>
        </div>

        <div class="form-group <?php echo $shortcode->shouldResolveByPhp() ? '' : ' d-none' ?>" id="phpValueWrap">
            <p class="alert alert-danger">
                <?php
                echo t("Please note that this feature is for <strong>developers</strong> only. Changing the input below might break your website!") . ' ';
                echo t("As a rule of thumb, keep it simple; if you need a lot of PHP code, it is better to hook into the '%s' event.", t('on_shortcode_resolve')) . ' ';
                echo t("The PHP code is wrapped in a try / catch, so code with syntax errors will be ignored. But if, for example, a die() statement is entered, execution will stop.");
                ?>
            </p>

            <?php echo $form->label("phpValue", t('PHP script')); ?>
            <?php echo $form->textarea('phpValue', $shortcode->getValue(), ['style' => 'min-height: 200px;', 'placeholder' => "echo 'hello world';"]); ?>

            <div class="help-block small">
                <?php echo t("Do not use the PHP opening or closing tags. Output buffering is used to get the string, so you can echo the value. Example: %s", t("echo date('Y');")); ?>
            </div>
        </div>

        <div id="eventExampleWrap" class="<?php echo $shortcode->shouldResolveByEvent() ? '' : 'd-none' ?>">
            <?php echo $form->label(null, t('Example PHP code to hook into the event')); ?>
            <?php echo $form->textarea("", ["style" => "min-height: 145px;", "autocorrect" => "off", "autocapitalize" => "off", "readonly" => "true", "spellcheck" => "false"]); ?>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a class="btn btn-secondary" href="<?php echo $this->action(''); ?>">
                    <?php echo t('Cancel') ?>
                </a>

                <button class="float-end btn btn-primary" type="submit">
                    <?php echo $shortcode->exists() ? t('Save') : t('Add') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<!--suppress JSJQueryEfficiency -->
<script>
    $(document).ready(function () {
        $('#resolveStrategy').change(function () {
            let strategy = $('#resolveStrategy').val();

            $('#eventExampleWrap, #stringValueWrap, #phpValueWrap').addClass("d-none");

            if (strategy === <?php echo json_encode(Shortcode::RESOLVE_BY_EVENT) ?>) {
                $('#eventExampleWrap').removeClass("d-none");
            } else if (strategy === <?php echo json_encode(Shortcode::RESOLVE_BY_STRING) ?>) {
                $('#stringValueWrap').removeClass("d-none");
            } else {
                $('#phpValueWrap').removeClass("d-none");
            }
        }).trigger("change");

        $('#shortcode').keyup(function () {
            let name = $(this).val();

            $('#eventExampleWrap textarea').val("\\Events::addListener('on_shortcode_resolve', function ($event) {\n" +
                "    if ($event->getShortcode()->getShortcode() === '" + name + "') {\n" +
                "        $event->setReplacement('your replacement');\n" +
                "    }\n" +
                "}");
        }).trigger('keyup').focus();
    });
</script>
