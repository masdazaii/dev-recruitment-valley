<?php

namespace Integration\ActiveCampaign;

use WP_Async_Request;

class ActiveCampaign 
{
    // start asynchronous property
    // protected $prefix = 'active_campaign';
    // protected $action = 'create_contact';
    // end asynchronous property

    protected $url = "https://madeindonesia1696926017.api-us1.com/api/";
    protected $version = 3;
    protected $apiKey = "2f362b1834e07a953daa399975ebf7e23a328c9ce44082fedb088d085cf48f69f02e18bd";

    public $first_name;
    public $last_name;
    public $email;
    public $telephone;

    public function __construct( )
    {
        $this->url = get_field('active_campaign_api_url', 'option');
        $this->version = get_field('active_campaign_api_version', 'option');
        $this->apiKey = get_field('active_campaign_api_key', 'option');
    }

    // public function handle()
    // {
    //     error_log(json_encode($_POST));
    // }
    
    /**
     * createContact
     * acccepting array associative that contain first_name, last_name, email, telephone 
     * 
     * @param  mixed $data
     * @return void
     */
    public function createContact($data, $function = false )
    {
        error_log($function);

        $path = "/contacts";
        $method = "POST";
        $payload = [
            "contact" => [
                "email" => $data["email"],
                "firstName" => $data["first_name"],
                "lastName" => $data["last_name"],
                "phone" => $data["telephone"],
            ]
        ];

        error_log(json_encode($payload));

        // Convert the data array to a JSON string
        $json_data = json_encode($payload);

        // cURL initialization
        $ch = curl_init($this->url.$this->version.$path);

        // Set cURL options
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // Set the request method to POST
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data); // Set the JSON data as the request body
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json', // Set the content type to JSON
            'accept: application/json',
            'Api-Token:'. $this->apiKey
        ]);

        // Set other cURL options if needed, such as headers or authentication

        // Execute the cURL request and capture the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        error_log(json_encode($response));

        $decodedResponse = json_decode($response);

        $tags = get_field('active_campaign_tags', 'options');

        error_log("selected tags : ". json_encode($tags));

        foreach ($tags as $tagId) {
            $this->addTagToContact($decodedResponse->contact->id, $tagId);
        }

        // Output the response
        return $response;
    }
    
    /**
     * getTags
     * get all tags from active campaign
     * 
     * @param  int $limit
     * @param  int $offset
     * @return void
     */
    public function getTags( int $limit = 0, int $offset = 0 )
    {
        $path = "/tags";
        $method = "GET";

        $queryParams = "?limit=".$limit."&offset=".$offset;

        error_log("start getting data from ". $this->url.$this->version.$path.$queryParams);

        $ch = curl_init($this->url.$this->version.$path);

        // Set cURL options
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // Set the request method to POST
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json', // Set the content type to JSON
            'accept: application/json',
            'Api-Token:'. $this->apiKey
        ]);

        // Set other cURL options if needed, such as headers or authentication

        // Execute the cURL request and capture the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        error_log("Tags from active campaign ". json_encode($response));

        // Output the response
        return json_decode($response)->tags;
    }

    public function addTagToContact( $contactId, $tagId )
    {
        $path = "/contactTags";
        $method = "POST";

        $payload = [
            'contactTag' => [
                "contact" => $contactId,
                "tag"     => $tagId,
            ],
        ];

        $payload = json_encode($payload);

        error_log("start add tags to contact on url : ". $this->url.$this->version.$path );
        $ch = curl_init($this->url.$this->version.$path);

        // Set cURL options
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // Set the request method to POST
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json', // Set the content type to JSON
            'accept: application/json',
            'Api-Token:'. $this->apiKey
        ]);

        // Execute the cURL request and capture the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        error_log("add tag to contact response ". json_encode($response));

        // Output the response
        return json_decode($response)->tags;
    }
}