<?php

namespace PostType;

use Vacancy\Vacancy;

defined("ABSPATH") or die("Direct access not allowed!");

class RssCPT extends RegisterCPT
{
    public $wpdb;

    public function __construct()
    {
        add_action('init', [$this, 'RegisterRSSCPT']);
        add_action('save_post', [$this, 'saveRSS'], 10, 3);
        add_action('add_meta_boxes', [$this, 'addRSSMetaboxes'], 10, 2);
        add_filter('manage_rss_posts_columns', [$this, 'rssColoumn'], 10, 1);
        add_action('manage_rss_posts_custom_column', [$this, 'rssCustomColoumn'], 10, 2);

        global $wpdb;
        $this->wpdb = $wpdb;
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
        $this->wpdb->query('START TRANSACTION');
        try {
            $rssModel = new \Model\Rss($post_id);

            // $endpoint = rest_url() . 'mi/v1/rss/vacancy/' . $post->post_name;
            $endpoint = '/rss/vacancy/' . $post->post_name;

            $updateMetaUrlRSS = $rssModel->setRssEndpointURL($endpoint);
            $updateMetaVacancies = $rssModel->setRssVacancies(isset($_POST['rv_rss_select_vacancy']) ? $_POST['rv_rss_select_vacancy'] : '');

            error_log('rss meta url - ' . $post_id . ' - ' . gettype($updateMetaUrlRSS));
            error_log('rss meta vacancy - ' . $post_id . ' - ' . gettype($updateMetaVacancies));

            $this->wpdb->query('COMMIT');
        } catch (\Exception $e) {
            $this->wpdb->query('ROLLBACK');
            error_log($e->getMessage());
        }
    }

    public function addRSSMetaBoxes($post_type, $post)
    {
        /** Select vacancies metabox */
        $this->rssSelectVacanciesMetabox($post);

        /** url metabox */
        $this->rssUrlMetaBox();
    }

    public function rssSelectVacanciesMetabox($post)
    {
        $rssModel = new \Model\Rss();
        add_meta_box(
            'rss_select_vacancies',
            'Select Vacancies',
            [$this, 'rssSelectVacanciesRenderMetabox'],
            'rss',
            'advanced',
            'default',
            ['post_id' => $post->ID, 'meta' => $rssModel->getRssVacancies()]
        );
    }

    public function rssSelectVacanciesRenderMetabox($post, $callback_args = [])
    {
        $rssModel = new \Model\Rss($post->ID);

        /** Declare var for vacancy option */
        $vacanciesOption = [];

        /** Check whether is post or edit screen */
        $screen = get_current_screen();
        if ($screen->parent_base == 'edit') {
            /** Get selected company */
            $selectedCompany = $rssModel->getRssCompany();

            if ($selectedCompany) {
                $vacancyModel = new Vacancy();

                /** Get vacancy by selected company */
                $vacancies = $vacancyModel->getVacancies([
                    'author' => $selectedCompany
                ]);

                /** Get selected vacancies */
                $selectedVacancies = $rssModel->getRssVacancies();

                if ($vacancies) {
                    if ($vacancies->found_posts > 0) {
                        foreach ($vacancies->posts as $vacancy) {
                            $vacanciesOption[] = [
                                'value' => $vacancy->ID,
                                'label' => $vacancy->post_title,
                                'selected' => in_array($vacancy->ID, $selectedVacancies)
                            ];
                        }
                    }
                }
            }
        }

        echo '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
        echo '<div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">';
        echo '<label style="display; block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">RSS URL</label>';
        echo '<select id="metabox-' . $rssModel->_meta_rss_vacancy . '" name="' . $rssModel->_meta_rss_vacancy . '[]" style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400;" multiple>';
        foreach ($vacanciesOption as $option) {
            echo '<option value="' . $option['value'] . '" ' . ($option['selected'] ? 'selected="selected"' : '') . '>' . $option['label'] . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';
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
        echo '<input style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400;" type="text" id="rss-url-endpoint" readonly disabled value="' . rest_url() . 'mi/v1' . $rssModel->getRssEndpointURL() . '"/>';
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
