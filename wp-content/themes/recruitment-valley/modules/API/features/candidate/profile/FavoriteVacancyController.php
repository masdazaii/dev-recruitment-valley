<?php

namespace Candidate\Profile;

use Constant\Message;
use WP_Query;

class FavoriteVacancyController
{
    protected $_message;
    private $_posttype = 'vacancy';

    public function __construct()
    {
        $this->_message = new Message();
    }

    public function store($request)
    {
        $vacancyID = sanitize_text_field($request['vacancyId']);

        $favorites = get_user_meta($request['user_id'], 'favorite_vacancy', true) ?? [];

        if (is_array($favorites) && count($favorites) > 0) {
            if (in_array($vacancyID, $favorites, false)) {
                return [
                    "message" => $this->_message->get('candidate.favorite.already_exists'),
                    "status" => 400,
                ];
            }
            array_push($favorites, $vacancyID);
        } else {
            $favorites = [$vacancyID];
        }

        $storeFav = update_user_meta($request['user_id'], 'favorite_vacancy', array_unique($favorites));

        if (!$storeFav) {
            return [
                "message" => $this->_message->get('candidate.favorite.add_failed'),
                "status" => 500,
            ];
        }

        return [
            "message" => $this->_message->get('candidate.favorite.add_success'),
            "status" => 200,
        ];
    }

    public function list($request)
    {
        $favorites = get_user_meta($request['user_id'], 'favorite_vacancy', true) ?? [];
        if (empty($favorites)) {
            return [
                "message"   => $this->_message->get('candidate.favorite.get_success'),
                "data"      => [],
                "meta"      => [
                    "currentPage" => isset($request['page']) ? intval(sanitize_text_field($request['page'])) : 0,
                    "totalPage" => 0
                ],
                "status"    => 200,
            ];
        }

        $filters = [
            'page' => $request['page'] ? sanitize_text_field($request['page']) : 1,
            'postPerPage' => $request['perPage'] ? sanitize_text_field($request['perPage']) : 0,
        ];

        if ($request['sort']) {
            switch (sanitize_text_field($request['sort'])) {
                case 'recent':
                    $filters['order'] = 'post_date';
                    $filters['orderBy'] = 'DESC';
                    break;
                default:
                    $filters['order'] = 'ID';
                    $filters['orderBy'] = 'ASC';
            }
        }

        $offset = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']) + 1);

        $args = [
            "post_type" => $this->_posttype,
            "posts_per_page" => $filters['postPerPage'],
            "offset" => $offset,
            "order" => "ASC",
            "post_status" => "publish",
            "post__in" => $favorites
        ];

        $vacancies = new WP_Query($args);

        if (!is_wp_error($vacancies)) {
            return [
                "message"   => $this->_message->get('candidate.favorite.get_success'),
                "data"    => $vacancies->posts ?? [],
                "meta"    => [
                    "currentPage" => intval($filters['page']),
                    "totalPage" => $vacancies->max_num_pages
                ],
                "status"  => 200
            ];
        } else {
            return [
                "message"   => $this->_message->get('system.overall_failed'),
                "status"  => 500
            ];
        }
    }

    public function destroy($request)
    {
        $vacancyID = sanitize_text_field($request['vacancyId']);

        $favorites = get_user_meta($request['user_id'], 'favorite_vacancy', true) ?? [];

        if (empty($favorites)) {
            return [
                "message" => $this->_message->get('candidate.favorite.empty'),
                "status" => 400,
            ];
        }

        // If the vacancy id is multiple, use array_diff instead
        $key = array_search($vacancyID, $favorites);
        if ($key !== false) {
            unset($favorites[$key]);
        }

        $storeFav = update_user_meta($request['user_id'], 'favorite_vacancy', array_unique($favorites));

        if (!$storeFav) {
            return [
                "message" => $this->_message->get('candidate.favorite.delete_failed'),
                "status" => 500,
            ];
        }

        return [
            "message" => $this->_message->get('candidate.favorite.delete_success'),
            "status" => 200,
        ];
    }
}
