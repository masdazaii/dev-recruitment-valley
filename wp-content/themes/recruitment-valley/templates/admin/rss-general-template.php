<main id="admin-rss-general" class="admin-rss-general display">
    <div style="margin: 1rem 1.25rem 1.25rem 0.15rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <h1 style="font-size: 1.25rem; line-height: 1.75rem;"><?php echo ucwords(get_admin_page_title() ?? __("RSS General URL", THEME_DOMAIN)); ?></h1>
        <!-- <hr> -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">
                <label style="display: block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">RSS General URL</label>
                <input style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400; color: black; font-weight:400" type="text" id="rss-url-endpoint" readonly disabled value="<?php echo rest_url() . 'mi/v1/rss/vacancy'; ?>" />
            </div>
        </div>
    </div>
</main>