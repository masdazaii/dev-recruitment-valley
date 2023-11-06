<?php

use Vacancy\Vacancy;

?>

<main id="admin-imported-vacancy-approval" class="admin-imported-vacancy-approval display">
    <div style="margin: 1rem 1.25rem 1.25rem 0.15rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <h1 style="font-size: 1.25rem; line-height: 1.75rem;"><?php echo ucwords(get_admin_page_title() ?? __("Imported Vacancy Approval", THEME_DOMAIN)); ?></h1>
        <!-- <hr> -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem; background-color: white; padding: 1rem 1rem 1rem 1rem; border: 0.5px solid rgba(165,165,165,0.5);">
            <table id="admin-imported-vacancy-approval-table" class="cell-border hover" style="background-color: white;">
                <thead style="text-align: left;">
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
    </div>
</main>