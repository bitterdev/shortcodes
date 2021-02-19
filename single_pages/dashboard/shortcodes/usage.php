<?php
defined('C5_EXECUTE') or die('Access Denied.');

/** @var \A3020\Shortcodes\Entity\Shortcode $shortcode */
?>

<div class="ccm-dashboard-header-buttons">
    <a class="btn btn-default" href="<?php echo $this->action(''); ?>">
        <?php echo t('Back to overview'); ?>
    </a>

    <a class="btn btn-warning" href="<?php echo $this->action('clearUsage', $shortcode->getId()); ?>">
        <?php echo t('Delete track records'); ?>
    </a>
</div>

<div class="ccm-dashboard-content-inner">
    <?php
    /** @var array $pages */
    if (!count($pages)) {
        echo t('This shortcode has not been tracked on any pages.');
    } else {
        ?>
        <table class="table table-striped table-bordered" id="tbl-usage">
            <thead>
                <tr>
                    <th><?php echo t('Page'); ?></th>
                    <th>
                        <?php echo t('Found at'); ?>
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($pages as $page) {
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $page['page_link']; ?>" target="_blank">
                                <?php
                                echo h($page['page_name']);
                                ?>
                            </a>
                        </td>
                        <td>
                            <?php echo $page['found_at']; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <script>
        $(document).ready(function() {
            $('#tbl-usage').DataTable({
                pageLength: 50,
                order: [[ 1, "desc" ]]
            });
        });
        </script>
        <?php
    }
    ?>
</div>
