<?php

namespace Model;

use Exception;
use WP_Query;

class Faq
{
    public $slug = 'faq';
    public $faq_id;
    public $faq;

    public $acf_faq_type    = 'rv_faq_type';
    public $property = [
        'acf' => [],
        'meta' => []
    ];

    public function __construct($faq_id = false, $get_all = false)
    {
        if ($faq_id) {
            $this->faq_id = $faq_id;
            $this->faq = get_post($faq_id);
            if (!$this->faq) {
                throw new Exception("FAQ not found!");
            }

            if ($get_all) {
                $this->property['acf'] = get_fields($faq_id);
                $this->property['meta'] = get_post_meta($faq_id);
            }
        }
    }

    public function getter($key, $format, $type = 'acf')
    {
        if (array_key_exists($key, $this->property[$type])) {
            return $this->property[$type][$key];
        } else {
            if ($this->faq_id) {
                if ($type == 'meta') {
                    return get_post_meta($this->faq_id, $key, $format);
                } else {
                    return get_field($key, $this->faq_id, $format);
                }
            } else {
                throw new Exception('Please specify faq!');
            }
        }
    }

    public function getFaqs($filters = [], $args = [])
    {
        $args = $this->_setArguments($args, $filters);
        $faqs = new WP_Query($args);

        return $faqs;
    }

    private function _setArguments($args = [], $filters = [])
    {
        if (empty($args)) {
            $args = [
                "post_type"         => $this->slug,
                "posts_per_page"    => $filters['postPerPage'] ?? -1,
                "offset"            => $filters['offset'] ?? 0,
                "orderby"           => $filters['orderBy'] ?? "date",
                "order"             => $filters['order'] ?? 'ASC',
                "post_status"       => $filters['post_status'] ?? "publish",
            ];
        }

        if (!empty($filters)) {
            if (array_key_exists('meta', $filters)) {
                $args['meta_query'] = $filters['meta'];
            }

            if (array_key_exists('taxonomy', $filters)) {
                $args['tax_query'] = $filters['taxonomy'];
            }

            if (array_key_exists('postPerPage', $filters)) {
                $args['posts_per_page'] = $filters['postPerPage'];
            }

            if (array_key_exists('offset', $filters)) {
                $args['offset'] = $filters['offset'];
            }

            if (array_key_exists('orderBy', $filters)) {
                /** Other orderby value please look at WP_Query docs. */
                switch ($filters['orderBy']) {
                    case 'ID':
                    case 'id':
                        $args['orderby'] = 'ID';
                        break;
                    default:
                        $args['orderby'] = $filters['orderBy'];
                        break;
                }
            }

            if (array_key_exists('order', $filters)) {
                switch ($filters['order']) {
                    case 'DESC':
                    case 'desc':
                    case 'descending':
                        $args['order'] = 'DESC';
                        break;
                    case 'ASC':
                    case 'asc':
                    case 'ascending':
                    default:
                        $args['order'] = 'ASC';
                        break;
                }
            }

            if (array_key_exists('post_status', $filters)) {
                $args['post_status'] = $filters['post_status'];
            }

            if (array_key_exists('author', $filters)) {
                $args['author '] = $filters['author'];
            }

            if (array_key_exists('in', $filters)) {
                $args['post__in'] = $filters['in'];
            }
        }

        return $args;
    }

    public function getType()
    {
        return $this->getter($this->acf_faq_type, true);
    }
}
