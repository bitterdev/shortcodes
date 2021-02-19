<?php

defined('C5_EXECUTE') or die('Access Denied.');

/** @var array $shortcodes */
/** @var bool $trackUsage */
?>

<div class="ccm-dashboard-header-buttons btn-group">
    <a class="btn btn-success" href="<?php echo $this->action('add'); ?>">
        <?php echo t('Add shortcode'); ?>
    </a>
</div>

<div class="ccm-dashboard-content-inner">
    <?php
    if (!count($shortcodes)) {
        echo t('No shortcodes have been added yet.');
    } else {
        ?>
        <table class="table table-striped table-bordered" id="tbl-shortcodes">
            <thead>
                <tr>
                    <th><?php echo t('Shortcode'); ?></th>
                    <th><?php echo t('Value'); ?></th>
                    <th>
                        <?php echo t('Is active'); ?>

                        <i class="text-muted launch-tooltip fa fa-question-circle" data-placement="bottom"
                           title="<?php echo t('Disabled shortcodes will not be replaced.') ?>">
                        </i>
                    </th>

                    <?php
                    if ($trackUsage) {
                        ?>
                        <th>
                            <?php echo t('Found on'); ?>

                            <i class="text-muted launch-tooltip fa fa-question-circle" data-placement="bottom"
                               title="<?php echo t('Usage tracking can be enabled via Settings. The number of pages is based on the track history and might not be accurate.') ?>">
                            </i>
                        </th>
                        <?php
                    }
                    ?>
                    <th>
                        <?php echo t('Updated at'); ?>

                        <i class="text-muted launch-tooltip fa fa-question-circle" data-placement="bottom"
                           title="<?php echo t('This is the last time the shortcode was edited.') ?>">
                        </i>
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($shortcodes as $shortcode) {
                    ?>
                    <tr>
                        <td>
                            <?php
                            if ($shortcode['can_edit']) {
                                ?>
                                <a href="<?php echo $this->action('edit', $shortcode['id']); ?>">
                                <?php
                            }

                            echo h($shortcode['shortcode']);

                            if ($shortcode['can_edit']) {
                                ?>
                                </a>
                                <?php
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo h($shortcode['value']); ?>
                        </td>
                        <td>
                            <?php
                            if ($shortcode['is_active']) {
                                echo '<i class="fa fa-check"></i>';
                            } else {
                                echo '<i class="fa fa-close"></i>';
                            }
                            ?>
                        </td>

                        <?php
                        if ($trackUsage) {
                            ?>
                            <td>
                                <?php
                                if ($shortcode['usage']) {
                                    ?>
                                    <a href="<?php echo $this->action('usage', $shortcode['id']) ?>">
                                        <?php
                                        echo t2('%s page', '%s pages', $shortcode['usage']);
                                        ?>
                                    </a>
                                    <?php
                                } else {
                                    echo t2('%s page', '%s pages', 0);
                                }
                                ?>
                            </td>
                            <?php
                        }
                        ?>

                        <td>
                            <?php echo $shortcode['updated_at']; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <script>
        $(document).ready(function() {
            var table = $('#tbl-shortcodes');

            // This is because the number of columns can differ
            var index = table.find('th:last').index();

            table.DataTable({
                pageLength: 20,
                order: [[ index, "desc" ]]
            });
        });
        </script>
        <?php
    }
    ?>
</div>
