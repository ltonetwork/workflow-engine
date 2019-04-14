<?php

/**
 * Try adding scenario with invalid data
 */

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class InvalidScenarioDataCest
{
    /**
     * @var array
     **/
    protected $data;

    /**
     * Init scenario data
     */
    public function _before(ApiTester $I): void
    {
        $data = file_get_contents('tests/_data/scenarios/basic-user-and-system.json');

        $this->data = json_decode($data, true);        
        $this->data['id'] = '3461288f-108e-4398-8d2d-7914ffd99ly8';
    }

    /**
     * Provide data for testing invalid values
     *
     * @return array
     */
    protected function invalidValuesProvider()
    {
        return [
            [
                'field' => 'title', 
                'value' => ['foo'], 
                'message' => 'Unable to cast Scenario::title from an array to a string',
                'code' => 400
            ],
            [
                'field' => 'description', 
                'value' => ['foo'], 
                'message' => 'Unable to cast Scenario::description from an array to a string',
                'code' => 400
            ],
            [
                'field' => '$schema', 
                'value' => 'foo', 
                'message' => ['schema property value is not valid'], 
                'code' => 400
            ],
            [
                'field' => '$schema', 
                'value' => 'https://specs.livecontracts.io/scenario/schema.json#', 
                'message' => ['schema property value is not valid'], 
                'code' => 400
            ],
            [
                'field' => '$schema', 
                'value' => 'https://specs.livecontracts.io/0.2.0/scenario/schema.json#', 
                'message' => ['schema property value is not valid'], 
                'code' => 400
            ],
            [
                'field' => '$schema', 
                'value' => 'https://specs.livecontracts.io/v0.2.0/process/schema.json#', 
                'message' => ['schema property value is not valid'], 
                'code' => 400
            ],
            [
                'field' => '$schema', 
                'value' => 'https://specs.livecontracts.io/v10.25.120/scenario/schema.json#', 
                'code' => 200
            ],
            [
                'field' => 'id', 
                'value' => '2557288f-108e-4398-8d2d-7914ffd93150', // id of existing scenario
                'code' => 200
            ],
            [
                'field' => 'actors', 
                'value' => 'foo', 
                'message' => ['Expected iterable, string(3) "foo" given'],
                'code' => 400
            ],
            [
                'field' => 'actions', 
                'value' => 'foo', 
                'message' => ['Expected iterable, string(3) "foo" given'],
                'code' => 400
            ],
            [
                'field' => 'states', 
                'value' => 'foo', 
                'message' => ['Expected iterable, string(3) "foo" given'],
                'code' => 400
            ],
            [
                'field' => 'assets', 
                'value' => 'foo', 
                'message' => ['Expected iterable, string(3) "foo" given'],
                'code' => 400
            ],
            [
                'field' => 'actors', 
                'value' => ['foo'], 
                'message' => ['Expected array,instance of stdClass,instance of Actor or instance of JsonSchema, string(3) "foo" given'],
                'code' => 400
            ],
            [
                'field' => 'actions', 
                'value' => ['foo'], 
                'message' => ['Expected array,instance of stdClass or instance of Action, string(3) "foo" given'],
                'code' => 400
            ],
            [
                'field' => 'states', 
                'value' => ['foo'], 
                'message' => ['Expected array,instance of stdClass or instance of State, string(3) "foo" given'],
                'code' => 400
            ],
            // [
            //     'field' => 'actors', 
            //     'value' => [
            //         'foo_actor' => [
            //             'title' => 'Foo actor',
            //             'identity' => 'non-exist-identity-id'
            //         ]
            //     ], 
            //     'message' => ['Expected array,instance of stdClass,instance of Actor or instance of JsonSchema, string(3) "foo" given'],
            //     'code' => 400
            // ],
        ];
    }

    /**
     * Save scenario with invalid values
     *
     * @dataprovider invalidValuesProvider
     */
    public function testInvalidValues(ApiTester $I, \Codeception\Example $example)
    {
        $this->data[$example['field']] = $example['value'];

        $this->test($I, $example['message'] ?? null, $example['code'] ?? 500);
    }

    /**
     * Perform test
     */
    protected function test(ApiTester $I, $message, $code = 500)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/scenarios', $this->data);

        $I->seeResponseCodeIs($code);

        $isJson = isset($message) && !is_string($message);
        if ($isJson) {
            $I->seeResponseIsJson();
            $I->seeResponseContainsJson($message);
        } elseif (isset($message)) {
            $I->seeResponseEquals($message);
        } else {
            $I->seeResponseContainsJson(['title' => 'Basic system and user']);
        }
    }
}