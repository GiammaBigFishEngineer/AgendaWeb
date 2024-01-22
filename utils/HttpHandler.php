<?php

use Sabre\HTTP\Client;
use Sabre\HTTP\Sapi;
use Sabre\HTTP\Response;
use Sabre\HTTP\ClientException;

class HttpHandler
{
    private $client;

    // public function __construct(string $baseUrl)
    // {
    //     $this->client = new Client($baseUrl);
    // }

    public function get(string $path): Response
    {
        return $this->request('GET', $path);
    }

    public function post(string $path, array $data): Response
    {
        return $this->request('POST', $path, $data);
    }

    public function handleRequest(): array
    {
        $request = Sapi::getRequest();
        $header = $request->getHeaders();

        if(str_contains($header["Content-Type"][0], "application/json")) {
            $data = json_decode($request->getBodyAsString(), JSON_OBJECT_AS_ARRAY); 
        } else if (str_contains($header["Content-Type"][0], "multipart/form-data") || str_contains($header["Content-Type"][0], "application/x-www-form-urlencoded")) {
            $data = $request->getPostdata();
        }

        if ($data) {
            // $types = [FILTER_VALIDATE_INT, FILTER_VALIDATE_FLOAT, FILTER_VALIDATE_BOOLEAN, FILTER_SANITIZE_STRING, FILTER_VALIDATE_REGEXP];

            // foreach ($data as $key => $value) {
            //     $type = $types[$key];
            //     $convertedValue = ($type !== null) ? filter_var($value, $type) : $value;
            //     $data[$key] = $convertedValue;
            // }

            return $data;
        }

        return [];
    }


    /**
     * Sends a response to the client.
     *
     * @param int $status The HTTP status code to send.
     * @param array $headers An array of headers to include in the response.
     * @param mixed $body The response body, gets converted to JSON format.
     * @return void
     */
    public function sendResponse(
        $body,
        int $status = 200,
        array $headers = ['Content-Type' => 'application/json; charset=utf-8'],
    ): void
    {

        $response = new Response();
    
        // If the body is an array, encode it as JSON
        if (is_array($body)) {
            $body = json_encode($body);
        }
    
    
        // Set the response status code
        $response->setStatus($status);
    
        // Set the response headers
        foreach ($headers as $key => $value) {
            $response->setHeader($key, $value);
        }
    
        // Set the response body
        $response->setBody($body);
    
        // Clean the output buffer before sending any response
        ob_clean();

        // Send the response using the Sapi class
        Sapi::sendResponse($response);
    }

}