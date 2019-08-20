<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\LightspeedResourceOwner;

class LightspeedResourceOwnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LightspeedResourceOwner
     */
    protected $account;

    protected function setUp()
    {
        $arr = [
            '[@attributes' => [
                'count' => 1,
            ],
            'Account' => [
                'accountID' => 4333,
                'name' => 'Foo Name',
                'link' => [
                    '@attributes' => [
                        'href' => '/API/Account/4333',
                    ],
                ],
            ],
        ];

        $this->account = new LightspeedResourceOwner($arr);
    }

    public function testGettersReturnNullWhenNoKeyExists()
    {
        $this->assertEquals('4333', $this->account->getId());
        $this->assertEquals('Foo Name', $this->account->getName());
    }

    public function testCanGetAllDataBackAsAnArray()
    {
        $data = $this->account->toArray();

        $expectedData = [
            '[@attributes' => [
                'count' => 1,
            ],
            'Account' => [
                'accountID' => 4333,
                'name' => 'Foo Name',
                'link' => [
                    '@attributes' => [
                        'href' => '/API/Account/4333',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedData, $data);
    }
}
