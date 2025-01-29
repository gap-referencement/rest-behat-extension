<?php

namespace AllManager\RestBehatExtension;

use AllManager\RestBehatExtension\Rest\ApiBrowser;
use AllManager\RestBehatExtension\Rest\HttpExchangeFormatter;
use AllManager\RestBehatExtension\Rest\WrongResponseExpectation;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class ApiContext implements Context
{
    public function __construct(
        private ApiBrowser $apiBrowser,
    ) {
    }

    #[When('I send a :method request to :url')]
    public function iSendARequest(
        string $method,
        string $url,
    ): void {
        $this->apiBrowser->sendRequest($method, $url);
    }

    #[When('I send a :method request to :url with body:')]
    public function iSendARequestWithBody(
        string $method,
        string $url,
        PyStringNode $body,
    ): void {
        $this->apiBrowser->sendRequest($method, $url, $body->getRaw());
    }

    #[When('I send a POST request to :url as HTML form with body:')]
    public function iSendAPostRequestToAsHtmlFormWithBody(
        string $url,
        TableNode $body,
    ): void {
        $formElements = [];
        foreach ($body as $element) {
            if (!isset($element['object'])) {
                throw new \Exception('You have to specify an object attribute');
            }

            $formElements[] = $element;
        }

        $this->apiBrowser->sendRequest('POST', $url, $formElements);
    }

    #[When('I send a :method request to :url with params in url')]
    public function iSendASpecialRequest(string $method, string $url): void
    {
        $url = $this->transformUrl($url);
        $this->apiBrowser->sendRequest($method, $url);
    }

    #[When('I send a :method request to :url with special body:')]
    public function iSendARequestWithSpecialBody(string $method, string $url, PyStringNode $body): void
    {
        $arrayBody = json_decode($body->getRaw(), true);

        foreach ($arrayBody as $key => $item) {
            $arrayBody[$key] = $this->transformData($item);
        }

        $json = (string) json_encode($arrayBody);
        $this->apiBrowser->sendRequest($method, $url, $json);
    }

    #[Then('response status code should be :code')]
    public function theResponseCodeShouldBe(string $code): void
    {
        $response = $this->apiBrowser->getResponse();

        $expected = intval($code);
        $actual = $response->getStatusCode();

        if ($actual !== $expected) {
            throw new WrongResponseExpectation(sprintf('Expected status code %d, got %d', $expected, $actual), $this->apiBrowser->getRequest(), $response);
        }
    }

    #[Given('I set :headerName header equal to :headerValue')]
    public function iSetHeaderEqualTo(string $headerName, string $headerValue): void
    {
        $this->apiBrowser->setRequestHeader($headerName, $headerValue);
    }

    #[Given('I add :headerName header equal to :headerValue')]
    public function iAddHeaderEqualTo(string $headerName, string $headerValue): void
    {
        $this->apiBrowser->addRequestHeader($headerName, $headerValue);
    }

    #[When('I set basic authentication with :username and :password')]
    public function iSetBasicAuthenticationWithAnd(string $username, string $password): void
    {
        $authorization = base64_encode($username.':'.$password);
        $this->apiBrowser->setRequestHeader('Authorization', 'Basic '.$authorization);
    }

    #[Then('print request and response')]
    public function printRequestAndResponse(): void
    {
        $formatter = $this->buildHttpExchangeFormatter();
        echo "REQUEST:\n";
        echo $formatter->formatRequest();
        echo "\nRESPONSE:\n";
        echo $formatter->formatFullExchange();
    }

    #[Then('print request')]
    public function printRequest(): void
    {
        echo $this->buildHttpExchangeFormatter()->formatRequest();
    }

    #[Then('print response')]
    public function printResponse(): void
    {
        echo $this->buildHttpExchangeFormatter()->formatFullExchange();
    }

    private function buildHttpExchangeFormatter(): HttpExchangeFormatter
    {
        return new HttpExchangeFormatter($this->apiBrowser->getRequest(), $this->apiBrowser->getResponse());
    }

    private function transformData(mixed $item): mixed
    {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                $item[$key] = $this->transformData($value);
            }
        } elseif (is_object($item)) {
            $item = (array) $item;
            foreach ($item as $key => $value) {
                $item[$key] = $this->transformData($value);
            }
        } elseif (is_string($item) && preg_match('/^__date\((.*)\)$/', $item, $matches)) {
            $item = (new \DateTimeImmutable($matches[1]))->format(DATE_ATOM);
        }

        return $item;
    }

    private function transformUrl(string $url): string
    {
        if (str_contains($url, '__date')) {
            $url = $this->replaceDate($url);
        }

        if (str_contains($url, '__id_from_previous_response')) {
            $url = $this->replaceIdFromPreviousResponse($url);
        }

        return $url;
    }

    private function replaceDate(string $url): string
    {
        $matches = [];
        preg_match_all('/__date\(([^)]*)\)/', $url, $matches);
        foreach ($matches[0] as $key => $match) {
            $url = str_replace($match, (new \DateTimeImmutable($matches[1][$key]))->format('Y-m-d'), $url);
        }

        return $url;
    }

    private function replaceIdFromPreviousResponse(string $url): string
    {
        $body = json_decode($this->apiBrowser->getResponse()->getBody(), true);
        $id = $body['id'] ?? $body['items'][0]['id'] ?? null;

        if (null === $id) {
            throw new \InvalidArgumentException('Cannot find id on previous response');
        }

        return str_replace('__id_from_previous_response', $id, $url);
    }
}
