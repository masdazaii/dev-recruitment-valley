<main id="admin-rss-general" class="admin-rss-general display">
    <div style="margin: 1rem 1.25rem 1.25rem 0.15rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <h1 style="font-size: 1.25rem; line-height: 1.75rem;"><?php echo ucwords(get_admin_page_title() ?? __("Dev Only Page", THEME_DOMAIN)); ?></h1>
        <!-- <hr> -->
        <form action="<?php echo esc_url(admin_url('admin-post.php')) ?>" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="action" value="set_vacancy_type">
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">
                    <label style="display: block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="dev-select-source">Select Vacancy Source</label>
                    <select style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400; color: black; font-weight:400" type="text" id="dev-select-source" name="dev-select-source" required>
                        <option value="">-- Select Source --</option>
                        <!-- <option value="all">All</option> -->
                        <option value="flexfeed">Flexparency / Brightwave</option>
                        <option value="textkernel" disabled>Textkernel</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">
                    <label style="display: block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="dev-select-taxonomy">Select Taxonomy</label>
                    <select style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400; color: black; font-weight:400" type="text" id="dev-select-taxonomy" name="dev-select-taxonomy" required>
                        <option value="">-- Select Taxonomy --</option>
                        <?php
                        foreach ($taxonomies as $taxonomy) {
                            echo '<option value="' . $taxonomy . '">' . $taxonomy . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">
                    <label style="display: block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="dev-select-term">Select Terms</label>
                    <select style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400; color: black; font-weight:400" type="text" id="dev-select-term" name="dev-select-term" required>
                        <option value="">-- Select Term --</option>
                        <?php
                        foreach ($availableTerms as $taxonomy => $terms) {
                            echo '<option value="" style="background-color: gray; color: white; font-weight: bold;" disabled>' . strtoupper($taxonomy) . '</option>';
                            foreach ($terms as $term) {
                                echo '<option value="' . $term['slug'] . '">' . $term['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <button type="submit" style="width: fit-content; padding: .5rem 1rem .5rem 1rem; background-color: light-grey;">Submit</button>
            <?php
            if (isset($_SESSION['flash_message'])) {
                echo $_SESSION['flash_message']['message'];
            }
            ?>
        </form>
    </div>
</main>