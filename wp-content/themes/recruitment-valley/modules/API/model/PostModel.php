<?php

namespace Model;

use Log;

class PostModel extends BaseModel
{
    public function __construct(Mixed $post = null)
    {
        parent::__construct();
        $this->selector = '';
    }

    public function create(array $data = [], array $args = [])
    {
        $logData['args'] = $args;
        $logData['filter'] = $data;
        $args = self::setArgument($data, $args);
        $logData['arguments'] = $args;
        Log::info("Create REPORT.", json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . "_log_report_company_recruiter_vacancy", false);
        return wp_insert_post($args, true, true);
    }

    private function setArgument(array $data = [], array $args = []): array
    {
        if (empty($args)) {
            $args = [
                'post_content'  => '',
                'post_date'     => date('Y-m-d H:i:s', time()),
                'post_date_gmt' => gmdate('Y-m-d H:i:s', time()),
                'post_status'   => 'publish',
                'ping_status'   => 'closed',
                'post_parent'   => 0,
                'post_author'   => get_current_user_id(),
                'post_type'     => 'child-company',
                'page_template' => 'default',
                'comment_status'    => 'closed',
            ];

            if (array_key_exists('title', $data)) {
                $args['post_title']    = $data['title'];
            } else if (array_key_exists('post_title', $data)) {
                $args['post_title']    = $data['post_title'];
            }

            if (array_key_exists('slug', $data)) {
                $args['post_name']     = $data['slug'];
            } else if (array_key_exists('post_name', $data)) {
                $args['post_name']    = $data['post_name'];
            }

            if (array_key_exists('post_type', $data)) {
                $args['post_type']     = $data['post_type'];
            }
        }

        if (!empty($data)) {
            if (array_key_exists('title', $data)) {
                $args['post_title']    = $data['title'];
            } else if (array_key_exists('post_title', $data)) {
                $args['post_title']    = $data['post_title'];
            }

            if (array_key_exists('slug', $data)) {
                $args['post_name']     = $data['slug'];
            } else if (array_key_exists('post_name', $data)) {
                $args['post_name']    = $data['post_name'];
            }

            if (array_key_exists('content', $data)) {
                $args['post_content']   = $data['content'];
            }


            if (array_key_exists('date', $data)) {
                $args['post_date']      = $data['date'];
            }


            if (array_key_exists('gmt_date', $data)) {
                $args['post_date_gmt']  = $data['gmt_date'];
            }


            if (array_key_exists('status', $data)) {
                $args['post_status']    = $data['status'];
            }


            if (array_key_exists('ping_status', $data)) {
                $args['ping_status']    = $data['ping_status'];
            }


            if (array_key_exists('post_parent', $data)) {
                $args['post_parent']    = $data['post_parent'];
            }


            if (array_key_exists('author', $data)) {
                $args['post_author']    = $data['author'];
            } else if (array_key_exists('post_author', $data)) {
                $args['post_author']    = $data['post_author'];
            }


            if (array_key_exists('type', $data)) {
                $args['post_type']      = $data['type'];
            } else if (array_key_exists('page_type', $data)) {
                $args['post_type']      = $data['page_type'];
            }

            if (array_key_exists('template', $data)) {
                $args['page_template']  = $data['template'];
            } else if (array_key_exists('page_template', $data)) {
                $args['page_template']  = $data['page_template'];
            }

            if (array_key_exists('comment_status', $data)) {
                $args['comment_status']  = $data['comment_status'];
            }

            if (array_key_exists('post_type', $data)) {
                $args['post_type']     = $data['post_type'];
            }
        }

        return $args;
    }
}
