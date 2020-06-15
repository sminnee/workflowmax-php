<?php

namespace Sminnee\WorkflowMax\Scraper;

use Goutte\Client;

/**
 * Handles the execution and parsing of a Xero-SSO log-in action via the Goutte client
 */
class XeroLoginHandler
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
        $crawler = $this->client->request('GET', "https://practicemanager.xero.com/Access/Logon/TransitionToXeroLogin?offsetFromUtc=-720&username=" . urlencode($credentials['username']));

        $refreshHeader = $this->client->getInternalResponse()->getHeader('Refresh');
        if(preg_match('#^\s*[0-9]+\s*;url=(.+)$#', $refreshHeader, $matches)) {
            $nextUrl = $matches[1];
        } else {
            throw new \LogicException("Bad refresh header '$refreshHeader'. Suspect Xero have changed their web-app.");
        }

        $crawler = $this->client->request('GET', $nextUrl);

        // Map credentials to form fields
        $formData = [
            'Username' => $credentials['username'],
            'Password' => $credentials['password'],
        ];

        // Submit the log-in form
        $form = $crawler->filter('form')->form();
        $crawler = $this->client->submit($form, $formData);

        // Submit the 2nd step form
        $form = $crawler->selectButton('Click to continue')->form();
        $crawler = $this->client->submit($form, []);

        // Check that you can see Time Summary on the homepage
        $crawler = $this->client->request('GET', "https://my.workflowmax.com/my/overview.aspx");

        $good = false;
        $headers = $crawler->filter('.LayoutSubHeading.LayoutSubHeadingUnderline');
        foreach ($headers as $header) {
            if ($header->textContent == 'Time Summary') {
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
