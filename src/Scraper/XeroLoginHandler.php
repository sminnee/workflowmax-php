<?php

namespace Sminnee\WorkflowMax\Scraper;

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\BrowserKit\Response As BKResponse;

use Symfony\Component\BrowserKit\HttpBrowser;


/**
 * Handles the execution and parsing of a Xero-SSO log-in action via the Goutte client
 */
class XeroLoginHandler
{

    protected $client;

    public function __construct(HttpBrowser $client)
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
        // When debugging these flows, the following commands are handy
        // var_dump($this->client->getInternalResponse());
        // var_dump($this->client->getInternalResponse());


        // Open first login form - just asks for username
        $crawler = $this->client->request('GET', "https://my.workflowmax.com/Access/Logon/CombinedLogin");

        // Submit the first form
        $formData = [
            'Code' => $credentials['username'],
        ];
        $form = $crawler->filter('form')->form();
        $crawler = $this->client->submit($form, $formData);

        $refreshHeader = $this->client->getInternalResponse()->getHeader('Refresh');
        if(preg_match('#^\s*[0-9]+\s*;url=(.+)$#', $refreshHeader, $matches)) {
            $nextUrl = $matches[1];
        } else {
            throw new \LogicException("Bad refresh header '$refreshHeader'. Suspect Xero have changed their web-app.");
        }

        // Fetch the form
        $crawler = $this->client->request('GET', $nextUrl);

        // Map credentials to form fields
        $formData = [
            'Username' => $credentials['username'],
            'Password' => $credentials['password'],
        ];

        // Submit the log-in form
        $form = $crawler->filter('form')->form();
        $crawler = $this->client->submit($form, $formData);

        if ($credentials['totp_secret']) {
            // Handle MFA screen
            $rawHTML = $crawler->html();
            if (preg_match('/"state": *"([^"]+)",/', $rawHTML, $matches)) {
                $stateToken = $matches[1]; 
            } else {
                throw new \LogicException("Couldn't find state token in raw HTML: $rawHTML");
            }
            
            if (preg_match('/"nonce": *"([^"]+)",/', $rawHTML, $matches)) {
                $nonceToken = $matches[1]; 
            } else {
                throw new \LogicException("Couldn't find nonce token in raw HTML: $rawHTML");
            }

            $requestVerificationToken = $crawler->filter('#RequestVerificationToken')->attr('value');

            $response = $this->client->request('POST', 'https://login.xero.com/identity/user/ui/login/two-factor/verify', [
                'oneTimePassword' =>'<< MANUALLY ENTER 6 DIGIT CODE HERE >>',
                'rememberMe' => false,
            ], [], [
                'http-RequestVerificationToken' => $requestVerificationToken,
            ]);

            $result = json_decode($this->client->getInternalResponse()->getContent(), true);
            
            if (empty($result['success'])) {
                throw new \LogicException("MFA check failed. This could be due to changes in the login flow, or the MFA token calculation is broken.");
            }

            $crawler = $this->client->request('GET', 'https://login.xero.com/identity/connect/authorize/callback?' .
                'client_id=xero-workflowmax-web' .
                '&response_type=code%20id_token&scope=openid%20profile%20email&redirect_uri=https%3A%2F%2Fmy.workflowmax.com%2FAccess%2FLogon%2FCompleteIdentityLogin' . 
                '&state=' . $stateToken .
                '&nonce=' . $nonceToken .
                '&response_mode=form_post', [], [], [
                    'http-Sec-Fetch-Dest' => 'document',
                    'http-Sec-Fetch-Mode' => 'navigate',
                    'http-Sec-Fetch-Site' => 'same-origin',
                    'http-Sec-Fetch-User' => '?1'
                ]);
        }

        // Submit the 2nd step form
        $crawler = $this->client->submitForm('Click to continue');
    
        // Check that you can see Time Summary on the homepage
        $crawler = $this->client->request('GET', "https://app.my.workflowmax.com/my/overview.aspx");

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
