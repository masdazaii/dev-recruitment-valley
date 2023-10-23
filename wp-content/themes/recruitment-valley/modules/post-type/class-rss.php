<?php

namespace PostType;

defined("ABSPATH") or die("Direct access not allowed!");

class RssCPT extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterRSSCPT']);
        add_action('save_post', [$this, 'saveRSS'], 10, 3);
        add_action('add_meta_boxes', [$this, 'rssUrlMetaBox']);
        add_filter('manage_rss_posts_columns', [$this, 'rssColoumn'], 10, 1);
        add_action('manage_rss_posts_custom_column', [$this, 'rssCustomColoumn'], 10, 2);
    }

    public function RegisterRSSCPT()
    {
        $title = __('RSS', THEME_DOMAIN);
        $slug = 'rss';
        $args = [
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'author', 'thumbnail')
        ];

        $this->customPostType($title, $slug, $args);
    }

    public function saveRSS($post_id, $post, $update)
    {
        try {
            $rssModel = new \Model\Rss($post_id);

            $endpoint = rest_url() . 'mi/v1/rss/vacancy/' . $post->post_name;
            // $updateMeta = $rssModel->setRssEndpointURL($endpoint);
            $updateMeta = update_post_meta($post_id, 'qwerqweqwe', $endpoint);
            error_log('rss meta - ' . $post_id . ' - ' . gettype($updateMeta));
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function rssUrlMetaBox()
    {
        $rssModel = new \Model\Rss();
        add_meta_box(
            'rss_endpoint_url',
            'RSS Url',
            [$this, 'rssUrlRenderMetabox'],
            'rss',
            'advanced',
            'default',
            ['meta' => $rssModel->getRssEndpointURL()]
        );
    }

    public function rssUrlRenderMetabox($post, $callback_args = [])
    {
        $rssModel = new \Model\Rss($post->ID);
        echo '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
        echo '<div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">';
        echo '<label style="display; block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">RSS URL</label>';
        echo '<input style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400;" type="text" id="rss-url-endpoint" readonly disabled value="' . $rssModel->getRssEndpointURL() . '"/>';
        echo '</div>';
        echo '</div>';
    }

    public function rssColoumn($coloumn)
    {
        $coloumn['url'] = __('RSS Url');

        return $coloumn;
    }

    public function rssCustomColoumn($coloumn, $post_id)
    {
        $rssModel = new \Model\Rss($post_id);

        switch ($coloumn) {
            case 'url':
                echo $rssModel->getRssEndpointURL();
                break;
        }
    }
}

// Initialize
new RssCPT();
