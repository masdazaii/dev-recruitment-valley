<?php

use Integration\ActiveCampaign\ActiveCampaign;

defined('ABSPATH') || die('Direct access not allowed');

class ActiveCampaignSettings
{
    public function __construct()
    {
        $this->ActiveCampaignPage();
        add_filter('acf/load_field/name=active_campaign_tags', [$this, "loadTagsOnActiveCampaign"]);
    }

    private function ActiveCampaignPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'    => 'Active Campaign Settings',
                'menu_title'    => 'Active Campaign Settings',
                'menu_slug'     => 'active-campaign-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ]);
        }
    }

    public function loadTagsOnActiveCampaign( $field  )
    {
        $field['choices'] = [];

        $activeCampaign = new ActiveCampaign;
        $tags = $activeCampaign->getTags();

        if(is_array($tags))
        {
            foreach ($tags as $tag) {
                if($tag->tagType != "contact") continue;
                $field["choices"][$tag->id] = $tag->tag;
            }
        }

        return $field;
    }
}

// Initiate
new ActiveCampaignSettings;
