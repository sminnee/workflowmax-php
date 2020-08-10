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
        $crawler = $this->client->request('GET', "https://practicemanager.xero.com/Access/Logon/TransitionToXeroLogin?username=" . urlencode($credentials['username']));

        $refreshHeader = $crawler->getHeader('Refresh');

        // Use refresh-header redirection
        if (preg_match('/^ *[0-9]+ *;(.*)$/', $refreshHeader, $matches)) {
            $crawler = $this->client->request('GET', $matches[1]);
        } else {
            throw new \LogicException("Bad Refresh header value: $refreshHeader");
        }

        // Map credentials to form fields
        $formData = [
            'Code' => $credentials['username'],
            'Password' => $credentials['password'],
        ];

        // Submit the log-in form
        $form = $crawler->selectButton('xl-form-submit')->form();
        $crawler = $this->client->submit($form, $formData);

        // Validate the result
        $error = $crawler->filter('.public-message .message.alert');
        if ($error->count() > 0) {
            $message = $this->cleanHTML($error->html());
            $message = ''. $message;
            return [false, $message];
        }

        foreach ($crawler->filter('h1.login-header') as $header) {
            if (preg_match('/redirected to the Xero login/i', $header->html())) {
                return [false, 'Login appeared not to work - get redirected to Xero login page'];
            }
        }

        $crawler = $this->client->request('GET', "https://app.my.workflowmax.com/my/overview.aspx");

        $good = false;
        $headers = $crawler->filter('.LayoutSubHeading.LayoutSubHeadingUnderline');
        foreach ($headers as $header) {
            if ($header->html() == 'Time Summary') {
                $good = true;
                break;
            }
        }

        if (!$good) {
            return [
                false,
                "Can't find 'Time Summary' heading in landing page. Suspect failed login" . $crawler->html()
            ];
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
