<?php

defined('ABSPATH') || die("Can't access directly");

class CandidateRole
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'addCandidateRole']);

        // When theme is deactived
        add_action('switch_theme', [$this, 'themeDeactivated'], 10, 3);
    }

    public function addCandidateRole()
    {
        remove_role('candidate');
        add_role('candidate', 'Candidate', []);
    }

    /**
     * This function will run once when theme deactivated
     *
     * @param  string   $new_name the new name.
     * @param  WP_Theme $new_theme the new theme.
     * @param  WP_Theme $old_theme the old theme.
     * @return void
     */
    public function theme_deactivated($new_name, $new_theme, $old_theme)
    {
        remove_role('candidate');
    }
}

new CandidateRole();
