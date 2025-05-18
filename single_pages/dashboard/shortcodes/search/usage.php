<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Bitter\Shortcodes\Entity\Shortcode;
use Concrete\Core\View\View;

/** @var Shortcode $shortcode */
/** @var array|null $pages */

?>

<div class="ccm-dashboard-header-buttons">
    <div class="btn-group">
        <a class="btn btn-danger" href="<?php echo $this->action('clear_usage', $shortcode->getId()); ?>">
            <?php echo t('Delete track records'); ?>
        </a>

        <?php /** @noinspection PhpUnhandledExceptionInspection */
        View::element("dashboard/help", [], "shortcodes"); ?>
    </div>
</div>

<?php /** @noinspection PhpUnhandledExceptionInspection */
View::element("dashboard/did_you_know", [], "shortcodes"); ?>

<div class="ccm-dashboard-content-inner">
    <?php if (!count($pages)) { ?>
        <?php echo t('This shortcode has not been tracked on any pages.'); ?>
    <?php } else { ?>
        <table class="table table-striped table-bordered" id="tbl-usage">
            <thead>
            <tr>
                <th>
                    <?php echo t('Page'); ?>
                </th>

                <th>
                    <?php echo t('Found at'); ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($pages as $page) { ?>
                <tr>
                    <td>
                        <a href="<?php echo h($page['page_link']); ?>" target="_blank">
                            <?php echo h($page['page_name']); ?>
                        </a>
                    </td>

                    <td>
                        <?php echo $page['found_at']; ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>

<div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <a class="btn btn-secondary" href="<?php echo $this->action(''); ?>">
            <?php echo t('Back to overview'); ?>
        </a>
    </div>
</div>


