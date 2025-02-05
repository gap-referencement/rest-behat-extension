<?php

namespace AllManager\RestBehatExtension\Json;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class JsonContext implements Context
{
    private string $valueInMemory;

    public function __construct(
        private JsonInspector $jsonInspector,
        private ?string $jsonSchemaBaseUrl = null,
    ) {
        if (null !== $this->jsonSchemaBaseUrl) {
            $this->jsonSchemaBaseUrl = mb_rtrim($this->jsonSchemaBaseUrl, '/');
        }
    }

    #[When('I load JSON:')]
    public function iLoadJson(PyStringNode $jsonContent): void
    {
        $this->jsonInspector->writeJson((string) $jsonContent);
    }

    #[Then('the response should be in JSON')]
    public function responseShouldBeInJson(): void
    {
        $this->jsonInspector->readJson();
    }

    #[Then('/^the JSON node "(?P<jsonNode>[^"]*)" should be equal to "(?P<expectedValue>.*)"$/')]
    public function theJsonNodeShouldBeEqualTo(string $jsonNode, string $expectedValue): void
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $expectedValue = $this->evaluateExpectedValue($expectedValue);

            if ($expectedValue != $realValue) {
                throw new \InvalidArgumentException(sprintf('The node "%s" is "%s" but "%s" was expected.', $jsonNode, $realValue, $expectedValue));
            }
        });
    }

    #[Then('/^the JSON node "(?P<jsonNode>[^"]*)" should have (?P<expectedNth>\d+) elements?$/')]
    #[Then('/^the JSON array node "(?P<jsonNode>[^"]*)" should have (?P<expectedNth>\d+) elements?$/')]
    public function theJsonNodeShouldHaveElements(string $jsonNode, int $expectedNth): void
    {
        $this->assert(function () use ($jsonNode, $expectedNth) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);

            if (count($realValue) !== $expectedNth) {
                throw new \InvalidArgumentException(sprintf('The node "%s" has %d elements but %d was expected.', $jsonNode, count($realValue), $expectedNth));
            }
        });
    }

    #[Then('/^the JSON array node "(?P<jsonNode>[^"]*)" should contain "(?P<expectedValue>.*)" element$/')]
    public function theJsonArrayNodeShouldContainElements(string $jsonNode, string $expectedValue): void
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);

            if (!in_array($expectedValue, $realValue)) {
                throw new \InvalidArgumentException(sprintf('The node "%s" does not contain "%s" element.', $jsonNode, $expectedValue));
            }
        });
    }

    #[Then('/^the JSON array node "(?P<jsonNode>[^"]*)" should not contain "(?P<expectedValue>.*)" element$/')]
    public function theJsonArrayNodeShouldNotContainElements(string $jsonNode, string $expectedValue): void
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);

            if (in_array($expectedValue, $realValue)) {
                throw new \InvalidArgumentException(sprintf('The node "%s" contains "%s" element.', $jsonNode, $expectedValue));
            }
        });
    }

    #[Then('/^the JSON node "(?P<jsonNode>[^"]*)" should contain "(?P<expectedValue>.*)"$/')]
    public function theJsonNodeShouldContain(string $jsonNode, string $expectedValue): void
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);

            if (false === str_contains((string) $realValue, $expectedValue)) {
                throw new \InvalidArgumentException(sprintf('The node "%s" does not contain "%s".', $jsonNode, $expectedValue));
            }
        });
    }

    #[Then('/^the JSON node "(?P<jsonNode>[^"]*)" should not contain "(?P<unexpectedValue>.*)"$/')]
    public function theJsonNodeShouldNotContain(string $jsonNode, string $unexpectedValue): void
    {
        $this->assert(function () use ($jsonNode, $unexpectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);

            if (str_contains((string) $realValue, $unexpectedValue)) {
                throw new \InvalidArgumentException(sprintf('The node "%s" contains "%s".', $jsonNode, $unexpectedValue));
            }
        });
    }

    #[Given('/^the JSON node "(?P<jsonNode>[^"]*)" should exist$/')]
    public function theJsonNodeShouldExist(string $jsonNode): void
    {
        try {
            $this->evaluateJsonNodeValue($jsonNode);
        } catch (\Exception $e) {
            throw new WrongJsonExpectation(sprintf("The node '%s' does not exist.", $jsonNode), $this->readJson(), $e);
        }
    }

    #[Given('/^the JSON node "(?P<jsonNode>[^"]*)" should not exist$/')]
    public function theJsonNodeShouldNotExist(string $jsonNode): void
    {
        $e = null;

        try {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
        } catch (\Exception $e) {
            // If the node does not exist an exception should be thrown
        }

        if (null === $e) {
            throw new WrongJsonExpectation(sprintf("The node '%s' exists and contains '%s'.", $jsonNode, json_encode($realValue)), $this->readJson(), $e);
        }
    }

    #[Then('/^the JSON should be valid according to this schema:$/')]
    public function theJsonShouldBeValidAccordingToThisSchema(PyStringNode $jsonSchemaContent): void
    {
        $tempFilename = tempnam(sys_get_temp_dir(), 'rae');
        file_put_contents($tempFilename, $jsonSchemaContent);
        $this->assert(function () use ($tempFilename) {
            $this->jsonInspector->validateJson(
                new JsonSchema($tempFilename, new Validator(), new SchemaStorage()),
            );
        });
        unlink($tempFilename);
    }

    #[Then('/^the JSON should be valid according to the schema "(?P<filename>[^"]*)"$/')]
    public function theJsonShouldBeValidAccordingToTheSchema(string $filename): void
    {
        $filename = $this->resolveFilename($filename);

        $this->assert(function () use ($filename) {
            $this->jsonInspector->validateJson(
                new JsonSchema($filename, new Validator(), new SchemaStorage()),
            );
        });
    }

    #[Then('the JSON node ":jsonNode" should be equal to')]
    public function theJsonNodeShouldBeEqualToMultiLines(string $jsonNode, PyStringNode $expectedValue): void
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $expectedValue = $this->evaluateExpectedValue($expectedValue);

            if ($expectedValue != $realValue) {
                throw new \InvalidArgumentException(sprintf('The node "%s" is "%s" but "%s" was expected.', $jsonNode, $realValue, $expectedValue));
            }
        });
    }

    #[Then('the JSON should be equal to:')]
    public function theJsonShouldBeEqualTo(PyStringNode $jsonContent): void
    {
        $realJsonValue = $this->readJson();

        try {
            $expectedJsonValue = new Json($jsonContent);
        } catch (\Exception $e) {
            throw new \Exception('The expected JSON is not a valid');
        }

        $this->assert(function () use ($realJsonValue, $expectedJsonValue) {
            if ((string) $realJsonValue !== (string) $expectedJsonValue) {
                throw new \InvalidArgumentException(sprintf("The JSON is\n%s\nbut\n%s\nwas expected.", $realJsonValue, $expectedJsonValue));
            }
        });
    }

    #[Then('the JSON path expression :pathExpression should be equal to json :expectedJson')]
    public function theJsonPathExpressionShouldBeEqualToJson(string $pathExpression, string $expectedJson): void
    {
        $expectedJson = new Json($expectedJson);
        $actualJson = Json::fromRawContent($this->jsonInspector->searchJsonPath($pathExpression));

        if ((string) $actualJson !== (string) $expectedJson) {
            throw new \InvalidArgumentException(sprintf("The JSON path expression '%s' is\n%s\nbut\n%s\nwas expected.", $pathExpression, $actualJson, $expectedJson));
        }
    }

    #[Then('the JSON path expression :pathExpression should be equal to:')]
    public function theJsonExpressionShouldBeEqualTo(string $pathExpression, PyStringNode $expectedJson): void
    {
        $this->theJsonPathExpressionShouldBeEqualToJson($pathExpression, (string) $expectedJson);
    }

    #[Then('the JSON node ":jsonNode" should be equal to dynamic ":expectedValue"')]
    public function theJsonNodeShouldBeEqualToDynamic(string $jsonNode, string $expectedValue): void
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $expectedValue = $this->evaluateExpectedValue($expectedValue);

            if ($expectedValue != $realValue) {
                throw new \InvalidArgumentException(sprintf('The node "%s" is "%s" but "%s" was expected.', $jsonNode, $realValue, $expectedValue));
            }
        });
    }

    #[Then('I keep the JSON node ":jsonNode" value in memory')]
    public function iKeepTheJSONNodeValueInMemory(string $jsonNode): void
    {
        $this->valueInMemory = $this->evaluateJsonNodeValue($jsonNode);
    }

    #[Then('the JSON node ":jsonNode" should be equal to the value in memory')]
    public function theJsonNodeShouldBeEqualToTheValueInMemory(string $jsonNode): void
    {
        $this->assert(function () use ($jsonNode) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);

            if ($realValue !== $this->valueInMemory) {
                throw new \InvalidArgumentException(sprintf('The node "%s" is "%s" but "%s" was expected.', $jsonNode, $realValue, $this->valueInMemory));
            }
        });
    }

    private function evaluateJsonNodeValue(string $jsonNode): mixed
    {
        return $this->jsonInspector->readJsonNodeValue($jsonNode);
    }

    private function evaluateExpectedValue(string $expectedValue): bool|string|null
    {
        if (in_array($expectedValue, ['true', 'false'])) {
            return filter_var($expectedValue, FILTER_VALIDATE_BOOLEAN);
        }

        if ('null' === $expectedValue) {
            return null;
        }

        if (preg_match('/^__date\((.*)\)$/', $expectedValue, $matches)) {
            $expectedValue = (new \DateTimeImmutable($matches[1]))->format(DATE_ATOM);
        }

        return $expectedValue;
    }

    private function readJson(): Json
    {
        return $this->jsonInspector->readJson();
    }

    private function resolveFilename(string $filename): false|string
    {
        if (true === is_file($filename)) {
            return realpath($filename);
        }

        if (null === $this->jsonSchemaBaseUrl) {
            throw new \RuntimeException(sprintf('The JSON schema file "%s" doesn\'t exist', $filename));
        }

        $filename = $this->jsonSchemaBaseUrl.'/'.$filename;

        if (false === is_file($filename)) {
            throw new \RuntimeException(sprintf('The JSON schema file "%s" doesn\'t exist', $filename));
        }

        return realpath($filename);
    }

    private function assert(callable $assertion): void
    {
        try {
            $assertion();
        } catch (\Exception $e) {
            throw new WrongJsonExpectation($e->getMessage(), $this->readJson(), $e);
        }
    }
}
