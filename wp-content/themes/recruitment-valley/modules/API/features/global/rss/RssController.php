<?php

namespace Global\Rss;

use DOMCdataSection;
use DOMElement;
use Error;
use Exception;
use Helper\StringHelper;
use SimpleXMLElement;
use Vacancy\Vacancy;
use Vacancy\VacancyResponse;
use Model\Rss;
use WP_Post;

class RssController
{
    private $xml = "";

    public function __construct()
    {
        $this->xml = "<?xml version='1.0' encoding='" . get_option('blog_charset') . "'?>
        <rss
            version='2.0'
            xmlns:content='http://purl.org/rss/1.0/modules/content/'
            xmlns:wfw='http://wellformedweb.org/CommentAPI/'
            xmlns:dc='http://purl.org/dc/elements/1.1/'
            xmlns:atom='http://www.w3.org/2005/Atom'
            xmlns:sy='http://purl.org/rss/1.0/modules/syndication/'
            xmlns:slash='http://purl.org/rss/1.0/modules/slash/'
            " . do_action('rss2_ns') . ">
                <channel>
                    <title>Vacancy RSS</title>
                    <link>https://recruitmentvalley.com</link>
                    <description>Vacancy RSS for recruitment valley</description>
                </channel>
        </rss>";
    }

    public function convert($vacancies)
    {
        // print('<pre>' . print_r($vacancies, true) . '</pre>');
        // die;
        try {
            $vacanciesResult = new SimpleXMLElement($this->xml);

            foreach ($vacancies["data"] as $key => $vacancy) {
                $vacancy = new Vacancy($vacancy->ID);
                $vacancyElement = $vacanciesResult->channel->addChild('item');

                $vacancyElement->addChild('title', htmlspecialchars($vacancy->getTitle()));
                $vacancyElement->addChild('link', FRONTEND_URL . '/vacatures/' . $vacancy->getSlug());
                $descriptionCData = new DOMCdataSection($vacancy->getDescription());
                $vacancyElement->addChild('description', StringHelper::shortenString($descriptionCData->wholeText, 0, 300));
                // $vacancyElement->addChild( 'pubDate', $vacancy->getPublishDate("D, d F Y H:i:s"));
            }

            header('Content-Type: application/rss+xml; charset=utf-8');

            return $vacanciesResult->asXML();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return "error";
        }
    }

    public function show($request)
    {
        $rssModel = new Rss();

        /** get rss  */
        $rss = $rssModel->getRssBySlug($request['rss'], 'object');
        $rssVacancies = $rssModel->getRssVacancies();
        $language = $rssModel->getRssLanguage();
        $company = $rssModel->getRssCompany();

        $vacancyModel = new Vacancy();
        $filters = [
            'meta' => [
                "relation" => "AND",
                [
                    'key' => 'expired_at',
                    'value' => date("Y-m-d H:i:s"),
                    'compare' => '>',
                    'type' => "DATE"
                ],
            ],
            'taxonomy' => [
                "relation" => "AND",
                [
                    'taxonomy' => 'status',
                    'field'    => 'slug',
                    'terms'    => 'open',
                    'compare'  => 'IN'
                ],
            ]
        ];

        if (isset($rssVacancies) && is_array($rssVacancies)) {
            $filters['in'] = array_values($rssVacancies);
        }

        if (isset($language) && $language) {
            $filters['meta'][] = [
                'key' => 'rv_vacancy_language',
                'value' => $language,
                'compare' => '='
            ];
        }

        if (isset($company) && $company) {
            if (is_array($company)) {
                $filters['author'] = $company;
            } else {
                $filters['author'] = [$company];
            }
        }

        /** Get vacancies data */
        $vacancies = $vacancyModel->getVacancies($filters);

        $response = [
            'data' => []
        ];

        if ($vacancies && $vacancies->found_posts > 0) {
            $response['data'] = $vacancies->posts;
        }

        /** Convert to xml */
        echo $this->convert($response);
    }
}
