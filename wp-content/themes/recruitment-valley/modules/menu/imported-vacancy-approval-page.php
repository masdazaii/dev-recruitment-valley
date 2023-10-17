<?php

use Vacancy\Vacancy;

?>

<main id="admin-imported-vacancy-approval" class="admin-imported-vacancy-approval display">
    <div style="margin: 1rem 1.25rem 1.25rem 0.15rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <h1 style="font-size: 1.25rem; line-height: 1.75rem;"><?php echo get_admin_page_title() ?? __("Imported Vacancy Approval", THEME_DOMAIN); ?></h1>
        <!-- <hr> -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem">
            <div style="">
                Filter goes here
            </div>
            <table id="admin-imported-vacancy-approval-table">
                <thead style="text-align: left;">
                    <th>No.</th>
                    <th>Vacancy Title</th>
                    <th>Vacancy Status</th>
                    <th>Approval Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </thead>
            </table>
        </div>
    </div>
</main>