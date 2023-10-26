<?php

namespace Global\FAQ;

defined('ABSPATH') or die('Direct access not allowed!');

use Constant\Message;
use Model\Faq;
use Helper\StringHelper;

class FaqController
{
    protected $_message;

    public function __construct()
    {
        $this->_message = new Message();
    }

    public function list($request)
    {
        try {
            $faqModel   = new Faq();

            $filters = [
                'page'          => isset($request['page']) ? (int)$request['page'] : 1,
                'postPerPage'   => isset($request['perPage']) ? (int)$request['perPage'] : -1,
            ];

            if (isset($request['orderBy'])) {
                $filters['orderBy']   = $request['orderBy'];
            }

            if (isset($request['sort'])) {
                $filters['order']     = $request['sort'];
            }

            $filters['offset'] = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

            /** Filter faq by what's question from / answer for */
            if (isset($request['type']) && in_array($request['type'], ['company', 'candidate'])) {
                $filters['meta'] = [
                    'relation'  => 'AND',
                    [
                        'key'   => $faqModel->acf_faq_type,
                        'value' => $request['type'],
                        'compare'   => '='
                    ]
                ];
            }

            $faqs       = $faqModel->getFaqs($filters, []);
            $faqsResponse = [
                'data'  => [],
                'meta'  => [
                    'max_posts' => 0,
                    'max_pages' => 0,
                ]
            ];

            if ($faqs) {
                $faqsResponse['meta']['max_posts']  = (int)$faqs->found_posts;
                $faqsResponse['meta']['max_pages']  = (int)$faqs->max_num_pages;

                if ($faqs->found_posts > 0) {
                    foreach ($faqs->posts as $faq) {
                        $faqsResponse['data'][] = [
                            'id'    => $faq->ID,
                            'slug'  => $faq->post_name,
                            'question'  => $faq->post_title,
                            'answer'    => StringHelper::shortenString($faq->post_content, 0, -1, '')
                        ];
                    }
                }
            }

            return [
                "message"   => $this->_message->get('faq.list_success'),
                "data"      => $faqsResponse['data'],
                "meta"      => [
                    'page'      => (int)$filters['page'],
                    'perPage'   => $filters['postPerPage'] == -1 ? 'all' : (int)$filters['postPerPage'],
                    'totalPage' => $faqsResponse['meta']['max_pages'],
                    'totalData' => $faqsResponse['meta']['max_posts']
                ],
                "a" => $faqs,
                "status"    => 200
            ];
        } catch (\WP_Error $err) {
            error_log($err->get_error_message() . ' - loged by FaqController::list');

            return [
                "message"   => $this->_message->get('system.overall_failed'),
                "status"    => 500
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage() . ' - loged by FaqController::list');

            return [
                "message"   => $this->_message->get('system.overall_failed'),
                "status"    => 500
            ];
        } catch (\Throwable $th) {
            error_log($th->getMessage() . ' - loged by FaqController::list');

            return [
                "message"   => $this->_message->get('system.overall_failed'),
                "status"    => 500
            ];
        }
    }
}
