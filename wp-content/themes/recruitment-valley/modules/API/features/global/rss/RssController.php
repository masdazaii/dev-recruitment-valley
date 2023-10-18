<?php

namespace Global\Rss;

use Error;
use Exception;
use Helper\StringHelper;
use SimpleXMLElement;
use Vacancy\Vacancy;
use Vacancy\VacancyResponse;
use WP_Post;

class RssController
{
    private $xml = "";

    public function __construct()
    {
        $this->xml = "<?xml version='1.0' encoding='".get_option('blog_charset')."'?>
        <rss 
            version='2.0'
            xmlns:content='http://purl.org/rss/1.0/modules/content/'
            xmlns:wfw='http://wellformedweb.org/CommentAPI/'
            xmlns:dc='http://purl.org/dc/elements/1.1/'
            xmlns:atom='http://www.w3.org/2005/Atom'
            xmlns:sy='http://purl.org/rss/1.0/modules/syndication/'
            xmlns:slash='http://purl.org/rss/1.0/modules/slash/'
            ".do_action('rss2_ns').">
                <channel>
                    <title>Vacancy RSS</title>
                    <link>https://recruitmentvalley.com</link>
                    <description>Vacancy RSS for recruitment valley</description>
                </channel>
        </rss>";
    }

    public function convert( $vacancies )
    {
        try {
            $vacanciesResult = new SimpleXMLElement($this->xml);

            foreach ($vacancies["data"] as $key => $vacancy) {
                $vacancy = new Vacancy($vacancy->ID);
                $vacancyElement = $vacanciesResult->channel->addChild('item');

                $vacancyElement->addChild('title', htmlspecialchars($vacancy->getTitle()));
                $vacancyElement->addChild('url', FRONTEND_URL . $vacancy->getSlug());
            }

            header('Content-Type: application/rss+xml; charset=utf-8');
            // echo '<pre>';
            // var_dump($vacanciesResult);
            // echo '</pre>';die;

            return $vacanciesResult->asXML();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return "error";
        }
    }
}