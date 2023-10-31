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

            /** Filter faq by what's question from / answer for
             * query params "type" can be string or array
             */
            if (isset($request['type']) && is_array($request['type'])) {
                if (in_array('candidate', $request['type']) && in_array('company', $request['type'])) {
                    $filters['meta'] = [
                        'relation'  => 'AND',
                        [
                            'relation'  => 'OR',
                            [
                                'key'   => $faqModel->acf_faq_type,
                                'value' => $request['type'],
                                'compare'   => '='
                            ],
                            [
                                'key'   => $faqModel->acf_faq_type,
                                'value' => 'both',
                                'compare'   => '='
                            ]
                        ]
                    ];
                } else if (in_array('both', $request['type'])) {
                    $request['type'] = 'both';
                } else if (in_array('candidate', $request['type'])) {
                    $request['type'] = 'candidate';
                } else if (in_array('company', $request['type'])) {
                    $request['type'] = 'company';
                } else {
                    unset($request['type']);
                }
            }

            if (isset($request['type']) && in_array($request['type'], ['company', 'candidate', 'both'])) {
                if ($request['type'] == 'company' || $request['type'] == 'candidate') {
                    $filters['meta'] = [
                        'relation'  => 'AND',
                        [
                            'relation'  => 'OR',
                            [
                                'key'   => $faqModel->acf_faq_type,
                                'value' => $request['type'],
                                'compare'   => '='
                            ],
                            [
                                'key'   => $faqModel->acf_faq_type,
                                'value' => 'both',
                                'compare'   => '='
                            ]
                        ]
                    ];
                } else {
                    $filters['meta'] = [
                        'relation'  => 'AND',
                        [
                            'key'   => $faqModel->acf_faq_type,
                            'value' => $request['type'],
                            'compare'   => '='
                        ],
                    ];
                }
            }

            /** Filter faq by display */
            if (isset($request['display']) && in_array($request['display'], ['faq', 'contact', 'both'])) {
                if ($request['display'] == 'contact') {
                    if (isset($filters['meta'])) {
                        $filters['meta'][] = [
                            'key'       => $faqModel->acf_faq_display_on_contact,
                            'value'     => 1,
                            'compare'   => '='
                        ];
                    } else {
                        $filters['meta'] = [
                            'relation'  => 'AND',
                            [
                                'key'       => $faqModel->acf_faq_display_on_contact,
                                'value'     => 1,
                                'compare'   => '='
                            ]
                        ];
                    }
                }
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
                        try {
                            $faqModel   = new Faq($faq->ID);
                            $faqType    = $faqModel->getType()['value'];
                            if ($faqType == 'both') {
                                $faqType = $faqModel->faq_type_values;
                            } else {
                                $faqType = [$faqType];
                            }

                            $faqsResponse['data'][] = [
                                'id'    => $faq->ID,
                                'slug'  => $faq->post_name,
                                'question'  => $faq->post_title,
                                'answer'    => StringHelper::shortenString($faq->post_content, 0, -1, ''),
                                'type'      => $faqType,
                                'isFavourite' => $faqModel->getDisplayOnContact() ?? false
                            ];
                        } catch (\Exception $e) {
                            error_log($e->getMessage() . ' - ' . $faq->ID . ' - logged by FaqController::list');
                            continue;
                        }
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
