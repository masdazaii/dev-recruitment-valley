<?php

namespace Sitemap;

use Vacancy\Vacancy;
use WP_Post;

class SitemapController
{
    public function vacancy()
    {
        $vacancy = new Vacancy;
        $vacancySlugs = $vacancy->allSlug();

        $vacancySlugs = array_map(function(WP_Post $vacancy){
            return $vacancy->post_name;
        }, $vacancySlugs);

        return [
            "status" => 200,
            "message" => "success get vacancy sitempa",
            "data" => $vacancySlugs
        ];
    }
}