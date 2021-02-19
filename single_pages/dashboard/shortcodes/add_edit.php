<?php

defined('C5_EXECUTE') or die('Access Denied.');

use A3020\Shortcodes\Entity\Shortcode;

/** @var \A3020\Shortcodes\Entity\Shortcode $shortcode */

?>

<div class="ccm-dashboard-header-buttons btn-group">
    <?php
    if ($shortcode->exists()) {
        ?>
        <a class="btn btn-danger" href="<?php echo $this->action('delete', $shortcode->getId()); ?>">
            <?php echo t('Delete shortcode'); ?>
        </a>
        <?php
    }
    ?>
</div>

<div class="ccm-dashboard-content-inner">
    <form method="post" action="<?php echo $this->action('save'); ?>">
        <?php
        /** @var $token \Concrete\Core\Validation\CSRF\Token */
        echo $token->output('a3020.shortcodes.add_edit');

        if ($shortcode->exists()) {
            echo $form->hidden('id', $shortcode->getId());
        }
        ?>

        <div class="form-group">
            <label class="control-label launch-tooltip"
                   title="<?php echo t('If inactive, the shortcode will not be replaced.') ?>"
                   for="isActive">
                <?php
                echo $form->checkbox('isActive', 1, $shortcode->isActive());
                ?>
                <?php echo t('Is active'); ?>
            </label>
        </div>

        <div class="form-group">
            <label class="control-label launch-tooltip"
                   title="<?php echo t("This text that will be replaced. For example you can add 'year' as shortcode and replace it with %s. We recommend to use lowercase only and to use underscores instead of spaces.", date('Y')); ?>"
                   for="shortcode">
                <?php
                echo t('Shortcode') . ' *';
                ?>
            </label>

            <?php
            echo $form->text('shortcode', $shortcode->getShortcode(), [
                'required' => 'required',
            ]);
            ?>
        </div>

        <div class="form-group">
            <label class="control-label launch-tooltip"
                   title="<?php echo t('Choose how the shortcode should be replaced.') ?>"
                   for="shortcode">
                <?php
                echo t('Replace the shortcode with') . ' *';
                ?>
            </label>

            <?php
            /** @var array $resolveStrategyOptions */
            echo $form->select('resolveStrategy', $resolveStrategyOptions, $shortcode->getResolveStrategy());
            ?>
        </div>

        <div class="form-group" id="stringValueWrap" <?php echo $shortcode->shouldResolveByString() ? '' : 'style="display: none;"' ?>>
            <label class="control-label launch-tooltip"
                   title="<?php echo t('The shortcode will be replaced with this value.') ?>"
                   for="stringValue">
                <?php
                echo t('Value');
                ?>
            </label>

            <?php
            echo $form->textarea('stringValue', $shortcode->getValue(), [
                'style' => 'min-height: 200px;',
            ]);
            ?>
        </div>

        <div class="form-group" id="phpValueWrap" <?php echo $shortcode->shouldResolveByPhp() ? '' : 'style="display: none;"' ?>>
            <p class="alert alert-danger">
                <?php
                echo t("Please note that this feature is for <strong>developers</strong> only. Changing the input below might break your website!") . ' ';
                echo t("As a rule of thumb, keep it simple; if you need a lot of PHP code, it is better to hook into the '%s' event.", t('on_shortcode_resolve')) . ' ';
                echo t("The PHP code is wrapped in a try / catch, so code with syntax errors will be ignored. But if, for example, a die() statement is entered, execution will stop.");
                ?>
            </p>

            <label class="control-label launch-tooltip"
                   title="<?php echo t("Do not use the PHP opening or closing tags. Output buffering is used to get the string, so you can echo the value. Example: %s", t("echo date('Y');")) ?>"
                   for="phpValue">
                <?php
                echo t('PHP script');
                ?>
            </label>

            <?php
            echo $form->textarea('phpValue', $shortcode->getValue(), [
                'style' => 'min-height: 200px;',
                'placeholder' => "echo 'hello world';",
            ]);
            ?>
        </div>

        <div id="eventExampleWrap" <?php echo $shortcode->shouldResolveByEvent() ? '' : 'style="display: none;"' ?>>
            <?php
            echo $form->label(null, t('Example PHP code to hook into the event'));
            ?>
            <textarea class="form-control" style="min-height: 145px;" readonly autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a class="btn btn-default" href="<?php echo $this->action(''); ?>">
                    <?php echo t('Cancel') ?>
                </a>
                <button class="pull-right btn btn-primary" type="submit">
                    <?php echo $shortcode->exists() ? t('Save') : t('Add') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    function toggleValueFields() {
        var strategy = $('#resolveStrategy').val();

        $('#stringValueWrap').toggle(
            strategy === '<?php echo Shortcode::RESOLVE_BY_STRING ?>'
        );

        $('#phpValueWrap').toggle(
            strategy === '<?php echo Shortcode::RESOLVE_BY_PHP ?>'
        );

        $('#eventExampleWrap').toggle(
            strategy === '<?php echo Shortcode::RESOLVE_BY_EVENT ?>'
        );
    }

    $('#resolveStrategy').change(function() {
        toggleValueFields();
    });

    // If the shortcode changes, update the code
    // that can be used to hook into the event
    $('#shortcode').keyup(function() {
        var name = $(this).val();

        $('#eventExampleWrap textarea').val("\\Events::addListener('on_shortcode_resolve', function ($event) {\n" +
            "    if ($event->getShortcode()->getShortcode() === '" + name + "') {\n" +
            "        $event->setReplacement('your replacement');\n" +
            "    }\n" +
            "}");
    })
        .trigger('keyup')
        .focus();

    toggleValueFields();
});
</script>
