<?php

namespace Vacancy\Import\Xml;

class FlexFeedController
{

    private $sample_dir = THEME_URL . '/assets/sample/xml/vacancies.xml';

    public function __construct()
    {
        
    }

    public function parse( )
    {
        $vacancies = [];

        error_log($this->sample_dir);

        try {
            if(file_exists($this->sample_dir))
            {
                $vacancies = simplexml_load_file($this->sample_dir);
            }

            return [
                "status" => 200,
                "message" => "success get vacancies",
                "data" => $vacancies,
            ];
        } catch (\Throwable $th) {
            return [
                "status" => 400,
                "message" => $th->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                "status" => 400,
                "message" => $e->getMessage()
            ];
        } catch (\WP_Error $error) {
            return [
                "status" => 400,
                "message" => $error->get_error_message()
            ];
        }
    }

    public function validate()
    {

    }

    public function save()
    {

    }
}