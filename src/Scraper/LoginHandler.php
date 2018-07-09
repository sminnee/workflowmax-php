<?php

namespace Sminnee\WorkflowMax\Scraper;

use Goutte\Client;

/**
 * Handles the execution and parsing of a log-in action via the Goutte client
 */
class LoginHandler
{

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Runs a log-in action
     * Updates the cookie jar of the connected Goutte client
     * Returns an array of two result values:
     *  - The first is a boolean, success/fail.
     *  - The second is an error message in the case of failure.
     */
    public function login(array $credentials)
    {
        // Open login form
        $crawler = $this->client->request('GET', "https://my.workflowmax.com/login.aspx");

        // Map credentials to form fields
        $formData = [
            'Code' => $credentials['username'],
            'Password' => $credentials['password'],
        ];

        // Submit the log-in form
        $form = $crawler->selectButton('Login')->form();
        $crawler = $this->client->submit($form, $formData);

        // Validate the result
        $error = $crawler->filter('.public-message .message.alert');
        if ($error->count() > 0) {
            $message = $this->cleanHTML($error->html());
            $message = ''. $message;
            return [false, $message];
        }

        return [true, null];
    }

    /**
     * Clean up an HTML string extracted from a cell
     */
    protected function cleanHTML($html)
    {
        return trim(
            str_replace(
                "\xC2\xA0",
                " ",
                html_entity_decode(
                    strip_tags($html)
                )
            )
        );
    }
}
