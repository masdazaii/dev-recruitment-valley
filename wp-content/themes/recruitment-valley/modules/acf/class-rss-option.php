<?php

namespace OptionPage;

defined('ABSPATH') or die('Direct access not allowed!');

class RssSetting
{
    public function __construct()
    {
        $this->rssSettingOptionPage();
        add_action('add_meta_boxes', [$this, 'showMetabox'], 10, 2);
    }

    private function rssSettingOptionPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'RSS Settings',
                'menu_title'    => 'RSS Settings',
                'menu_slug'     => 'rss-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }

    public function showMetabox()
    {
        add_meta_box(
            'rss_general_url',
            'RSS Genreal Url',
            [$this, 'rssGeneralUrlRenderMetabox'],
            '?page=rss-settings',
            'advanced',
            'default',
            ['meta' => 'a']
        );
    }

    public function rssGeneralUrlRenderMetabox($post, $callback_args = [])
    {
        $rssModel = new \Model\Rss($post->ID);
        echo '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
        echo '<div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">';
        echo '<label style="display: block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">RSS URL</label>';
        echo '<input style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400;" type="text" id="rss-url-endpoint" readonly disabled value="a"/>';
        echo '</div>';
        echo '</div>';
    }
}

// Initialize
new RssSetting();
