<?php

use Vacancy\Vacancy;

?>

<main id="admin-imported-vacancy-approval" class="admin-imported-vacancy-approval display">
    <div style="margin: 1rem 1.25rem 1.25rem 0.15rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <h1 style="font-size: 1.25rem; line-height: 1.75rem;"><?php echo ucwords(get_admin_page_title() ?? __("Imported Vacancy Approval", THEME_DOMAIN)); ?></h1>
        <!-- <hr> -->
        <form method="POST" id="approval-list" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="handle_bulk_action">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('nonce_vacancy_approval'); ?>">
            <div style="display: flex; flex-direction: column; gap: 0.5rem; background-color: white; padding: 1rem 1rem 1rem 1rem; border: 0.5px solid rgba(165,165,165,0.5);">
                <div style="display: flex; flex-direction: row; gap: 0.125rem;">
                    <!-- <button type="submit">Bulk Approve</button>
                    <button type="submit">Bulk Reject</button> -->
                    <select id="import-vacancy-approval-status-option" name="inputBulkStatus" class="import-vacancy-approval-status-option">
                        <option value="">-- Select Approval Status --</option>
                        <option value="admin-approved">Approve Selected</option>
                        <option value="rejected">Reject Selected</option>
                        <option value="waiting">Waiting Approval</option>
                    </select>
                    <button name="submit" type="submit" value="bulk-status-approval">Set Approval Status</button>
                </div>
                <table id="admin-imported-vacancy-approval-table" class="cell-border hover" style="background-color: white;">
                    <thead style="text-align: left;">
                        <th>
                            <input type="checkbox" id="import-vacancy-aproval-input-bulk-checkbox-all" name="bulkActionAll" class="import-vacancy-aproval-input-bulk-checkbox-all" value="all">
                        </th>
                        <th>Vacancy Title</th>
                        <th>Vacancy Status</th>
                        <th>Approval Status</th>
                        <th>Paid / Free</th>
                        <th>Imported</th>
                        <th>Role</th>
                        <th>Sector</th>
                        <th>Published Date</th>
                        <th>Action</th>
                    </thead>
                </table>
            </div>
        </form>
    </div>
</main>