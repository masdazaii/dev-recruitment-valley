<?php


class MICustomAcfValidation
{
    public function __construct()
    {
        add_filter("acf/validate_value/name=is_favorite", [$this, "is_favorite_validation"], 10, 4);
        add_filter("acf/validate_value/name=rv_email_approval_cc", [$this, 'validateUniqueCC'], 10, 4);
        add_filter("acf/validate_value/name=rv_email_approval_bcc", [$this, 'validateUniqueBCC'], 10, 4);
    }

    public function is_favorite_validation($valid, $value, $field, $input_name)
    {
        if ($value == 1) {
            $package = get_posts([
                "post_type" => "package",
                "post_status" => "publish",
                "numberposts" => -1,
                "meta_query" => [
                    [
                        "key" => "is_favorite",
                        "value" => 1,
                        "compare" => "="
                    ],
                ]
            ]);

            reset($package);

            if (count($package) > 0 && (int) $_POST["post_ID"] != $package[0]->ID) {
                return __('Favorite product already set in ' . $package[0]->post_title, THEME_DOMAIN);
            }
        }

        return $valid;
    }

    public function validateUniqueCC($valid, $value, $field, $input_name)
    {
        /** Get acf key */
        $acfCC          = acf_maybe_get_field('rv_email_approval_cc', false, false);
        $acfCCAddress   = acf_maybe_get_field('rv_email_approval_cc_address', false, false);

        /** Check if there is duplicate input */
        $tmpInputAddress = [];
        foreach ($_POST['acf'][$acfCC['key']] as $key => $values) {
            $tmpInputAddress[] = $values[$acfCCAddress['key']];
        }
        $tmpUniqueInputAddress = array_unique($tmpInputAddress);

        if (count($tmpInputAddress) != count($tmpUniqueInputAddress)) {
            return __('CC address is duplicate, address should be added one time only!', THEME_DOMAIN);
        } else {
            return $valid;
        }
    }

    public function validateUniqueBCC($valid, $value, $field, $input_name)
    {
        /** Get acf key */
        $acfCC          = acf_maybe_get_field('rv_email_approval_bcc', false, false);
        $acfCCAddress   = acf_maybe_get_field('rv_email_approval_bcc_address', false, false);

        /** Check if there is duplicate input */
        $tmpInputAddress = [];
        foreach ($_POST['acf'][$acfCC['key']] as $key => $values) {
            $tmpInputAddress[] = $values[$acfCCAddress['key']];
        }
        $tmpUniqueInputAddress = array_unique($tmpInputAddress);

        if (count($tmpInputAddress) != count($tmpUniqueInputAddress)) {
            return __('BCC address is duplicate, address should be added one time only!', THEME_DOMAIN);
        } else {
            return $valid;
        }
    }
}

new MICustomAcfValidation;
