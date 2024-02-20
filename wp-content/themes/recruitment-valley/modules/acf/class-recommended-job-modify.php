<?php

namespace ACF;

use Helper\DateHelper;
use Vacancy\Vacancy;

class RecommendedJobModify
{
    public function __construct()
    {
        add_filter( "acf/fields/post_object/result/name=rv_recommended_jobs", [$this, 'filter_field'], 10, 4 );
    }

    public function filter_field( $text, $post, $field, $post_id )
    {
        if(is_admin())
        {
            $vacancyModel = new Vacancy($post_id);
            $text .= "( Post Date : " . DateHelper::doLocale($post->post_date_gmt, 'nl_NL') . ") - ( Company : ". $vacancyModel->getCompanyName() .")";
        }

        return $text;
    }

}

new RecommendedJobModify;