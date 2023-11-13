<?php

namespace Model;

defined("ABSPATH") or die("Direct Access not allowed!");

class Rss
{
    private $_postType = 'rss';
    private $_post;
    private $_rssID;

    private $_acf_rss_company   = 'rv_rss_select_company';
    private $_acf_rss_language  = 'rv_rss_select_language';

    public $_meta_rss_vacancy = 'rv_rss_select_vacancy';

    private $_meta_rss_endpoint_url = 'rv_rss_endpoint_url';

    private $_property = [];

    public function __construct($rssID = null, $getAllProperty = false)
    {
        if ($rssID) {
            $this->_rssID   = $rssID;
            $rssPost        = get_post($rssID);

            if ($rssPost) {
                $this->_post = $rssPost;

                if ($getAllProperty) {
                    $this->_property = get_fields($rssID, true);
                }
            } else {
                throw new \Exception('RSS Not found!');
            }
        }
    }

    public function getKey()
    {
        return [
            'acf' => [
                'rss_company' => $this->_acf_rss_company,
                'rss_vacancy' => $this->_meta_rss_vacancy,
            ],
            'meta' => [
                'endpoint_url' => $this->_meta_rss_endpoint_url,
            ]
        ];
    }

    public function getter($key, $format = true, $type = 'acf')
    {
        if (array_key_exists($key, $this->_property)) {
            return $this->_property[$key];
        } else {
            switch ($type) {
                case 'meta':
                    return get_post_meta($this->_rssID, $key, $format);
                    break;
                default:
                    return get_field($key, $this->_rssID, $format);
                    break;
            }
        }
    }

    public function setter($key, $value, $type = 'acf')
    {
        switch ($type) {
            case 'meta':
                return update_post_meta($this->_rssID, $key, $value);
                break;
            default:
                return update_field($key, $value, $this->_rssID);
                break;
        }
    }

    public function getRssBySlug($slug, $result = 'object')
    {
        if ($slug) {
            $post = get_page_by_path($slug, OBJECT, $this->_postType);
            if ($post && $post instanceof \WP_Post) {
                $this->_post    = $post;
                $this->_rssID   = $post->ID;

                if ($result == 'id') {
                    return $this->_rssID;
                } else {
                    return $this->_post;
                }
            }

            return false;
        } else {
            throw new \Exception('Please specify the slug!');
        }
    }

    public function getRssVacancies()
    {
        return $this->getter($this->_meta_rss_vacancy, true, 'meta');
        // $metaValue = $this->getter($this->_meta_rss_vacancy, true, 'meta');
        // error_log('getRssVacancies before maybe_unserialize : '.json_encode($metaValue));
        // $afterSerialize = maybe_unserialize();
        // error_log('getRssVacancies after maybe_unserialize : '.json_encode($afterSerialize));
        // return $afterSerialize;
    }

    public function setRssVacancies($value)
    {
        return $this->setter($this->_meta_rss_vacancy, $value, 'meta');
    }

    public function setRssEndpointURL($value)
    {
        return $this->setter($this->_meta_rss_endpoint_url, $value, 'meta');
    }

    public function getRssCompany()
    {
        return $this->getter($this->_acf_rss_company, true, 'acf');
    }

    public function getRssEndpointURL()
    {
        return $this->getter($this->_meta_rss_endpoint_url, true, 'meta');
    }

    public function getRssLanguage()
    {
        return $this->getter($this->_acf_rss_language, true, 'acf');
    }

    public function setRssLanguage($value)
    {
        return $this->setter($this->_acf_rss_language, $value, 'acf');
    }
}
