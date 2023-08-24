<?php


class MICustomAcfValidation
{
    public function __construct()
    {
        add_filter("acf/validate_value/name=is_favorite", [$this,"is_favorite_validation"], 10, 4);
    }

    public function is_favorite_validation( $valid, $value, $field, $input_name )
    {
        $package = get_posts([
            "post_type" => "package",
            "post_status" => "publish",
            "numberposts" => -1,
            "meta_query" =>[
                [
                    "key" => "is_favorite",
                    "value" => 1,
                    "compare" => "="
                ],
            ]
        ]);

        reset($package);

        if(count($package) > 0 && (int) $_POST["post_ID"] != $package[0]->ID )
        {
            return __( 'Favorite product already set in ' . $package[0]->post_title, THEME_DOMAIN );
        }

        return $valid;
    }
}

new MICustomAcfValidation;