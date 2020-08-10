<?php

namespace Sminnee\WorkflowMax\Scraper;

use Goutte\Client;
use GuzzleHttp\Cookie\CookieJar;

/**
 * Fetches the content of a WorkflowMax report
 */
class ReportFetcher
{

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getCookiesFor($url)
    {
        return CookieJar::fromArray(
            $this->client->getCookieJar()->allRawValues($url),
            parse_url($url, PHP_URL_HOST)
        );
    }

    /**
     * Make a single call to the Ajax Pro service that WorkflowMax uses
     */
    public function ajaxProCall($class, $method, $data)
    {
        return $this->client->getClient()->request(
            'POST',
            "https://app.my.workflowmax.com/ajaxpro/{$class}.ashx",
            [
                'headers' => [
                    'Content-Type' => 'text/plain',
                    'X-AjaxPro-Method' => $method,
                ],
                'cookies' => $this->getCookiesFor("https://app.my.workflowmax.com/"),
                'body' => json_encode($data),

            ]
        );
    }

    /**
     * @param int $reportID The report ID as given in the WorkflowMax URL
     * @param array $criteria An array of filters to modify. Each item should be an array withs "column", "operator", and "value"
     * @return iterable
     */
    public function getReport($reportID, $criteria = [])
    {
        $guzzleClient = $this->client->getClient();

        if ($criteria) {
            // Find the report designer ID
            $response = $guzzleClient->request(
                'GET',
                "https://app.my.workflowmax.com/reports/view.aspx?id={$reportID}",
                [ 'cookies' => $this->getCookiesFor("https://app.my.workflowmax.com/") ]
            );
            $body = '' . $response->getBody();
            if (preg_match('/new WorkflowMax.Control.ReportDesigner\(\s*([0-9]+)\s*\)/', $body, $matches)) {
                $reportDesignerID = $matches[1];
            } else {
                echo $body;
                throw new \LogicException("Can't find report designer ID in WorkflowMax HTML source");
            }

            // Parse available criteria, and build a map of the filter IDs
            $availableCriteriaString = preg_replace('/;\/\*.*$/', '', '' . $this->ajaxProCall(
                'WorkFlowMax.Web.UI.ReportDesigner,WorkFlowMax.Web.UI',
                'LoadEditableCriteria',
                ['id' => $reportDesignerID ]
            )->getBody());
            $availableCriteria = json_decode($availableCriteriaString, true);

            if (!isset($availableCriteria['criteriaList']['criteria'])) {
                throw new \LogicException("Unclear criteria schema data in $availableCriteriaString");
            }

            $criteriaMap = [];
            foreach ($availableCriteria['criteriaList']['criteria'] as $criterion) {
                if (!isset($criteriaMap[$criterion['name']])) {
                    $criteriaMap[$criterion['name']] = [];
                }
                $criteriaMap[$criterion['name']][] = $criterion['id'];
            }

            // Populate the filters
            foreach ($criteria as $criterion) {
                if (empty($criteriaMap[$criterion['column']])) {
                    throw new \LogicException("Can't find enough filters for " . $criterion['column']);
                }

                $filterID = array_shift($criteriaMap[$criterion['column']]);

                $this->ajaxProCall(
                    'WorkFlowMax.Web.UI.ReportDesigner,WorkFlowMax.Web.UI',
                    'UpdateCriteriaDateMode',
                    ['id' => $filterID, 'mode' => 'CUSTOM']
                );
                $this->ajaxProCall(
                    'WorkFlowMax.Web.UI.ReportDesigner,WorkFlowMax.Web.UI',
                    'UpdateCriteriaOperator',
                    ['id' => $filterID, '_operator' => $criterion['operator']]
                );
                $this->ajaxProCall(
                    'WorkFlowMax.Web.UI.ReportDesigner,WorkFlowMax.Web.UI',
                    'UpdateCriteriaDateValue',
                    ['id' => $filterID, 'value' => $criterion['value']]
                );
            }

            $reportID = $reportDesignerID;
        }

        $response = $this->ajaxProCall(
            'WorkFlowMax.Web.UI.ReportExport,WorkflowMax.App',
            'Export',
            ['design_id' => $reportID, 'format' => 'csv']
        );


        // Some garbled content in the JSON body just to mess with us.
        $jsonBody = preg_replace('/;\/\*.*$/', '', ''.$response->getBody());
        $csvExport = json_decode($jsonBody, true);

        if (!isset($csvExport["url"])) {
            throw new \LogicException("Couldn't export report: " . var_export($response, true) . "\n" . $response->getBody());
        }

        $downloadURL = "https://app.my.workflowmax.com/reports/" . $csvExport["url"];

        $csvFilename = tempnam('/tmp', 'report');
        $guzzleClient->get(
            $downloadURL,
            [
                'allow_redirects' => true,
                'cookies' => $this->getCookiesFor("https://app.my.workflowmax.com/"),
                'save_to' => $csvFilename,
            ]
        );

        return new CsvFileIterator($csvFilename, true);
    }
}
