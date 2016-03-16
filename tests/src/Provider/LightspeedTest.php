<?php

namespace League\OAuth2\Client\Test\Provider;

use Mockery as m;
use League\OAuth2\Client\Provider\Lightspeed;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class FooLightspeedProvider extends Lightspeed
{
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return json_decode('{"id": 12345, "link": "mock_Lightspeed_url"}', true);
    }
}

class LightspeedTest extends \PHPUnit_Framework_TestCase
{

    const ACCOUNT_ID = 123;

    /**
     * @var Lightspeed
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Lightspeed([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'accountId' => static::ACCOUNT_ID,
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testGetBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);
        $accountId = static::ACCOUNT_ID;

        $this->assertEquals('/oauth/access_token.php', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');
        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken(), 'Lightspeed does not support refresh tokens. Expected null.');
        $this->assertNull($token->getResourceOwnerId(), 'Lightspeed does not return user ID with access token. Expected null.');
    }

    public function testCanGetALongLivedAccessTokenFromShortLivedOne()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');
        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"long-lived-token","token_type":"bearer","expires_in":3600}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getLongLivedAccessToken('short-lived-token');

        $this->assertEquals('long-lived-token', $token->getToken());
    }

    /**
     * @expectedException \League\OAuth2\Client\Provider\Exception\LightspeedProviderException
     */
    public function testTryingToRefreshAnAccessTokenWillThrow()
    {
        $this->provider->getAccessToken('foo', ['refresh_token' => 'foo_token']);
    }

    public function testScopes()
    {
        $this->assertEquals(['employee:all'], $this->provider->getDefaultScopes());
    }

    // public function testUserData()
    // {
    //     $provider = new FooLightspeedProvider([
    //       'accountId' => static::ACCOUNT_ID,
    //     ]);

    //     $token = m::mock('League\OAuth2\Client\Token\AccessToken');
    //     $user = $provider->getResourceOwner($token);

    //     $this->assertEquals(12345, $user->getAccountId($token));
    // }

    /**
     * @expectedException \InvalidArgumentException
     */
    // public function testNotSettingADefaultAccountIdWillThrow()
    // {
    //     new Lightspeed([
    //       'clientId' => 'mock_client_id',
    //       'clientSecret' => 'mock_secret',
    //       'redirectUri' => 'none',
    //       'accountId' => ,
    //     ]);
    // }

    public function testProperlyHandlesErrorResponses()
    {
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getHeader')
                 ->times(1)
                 ->andReturn('application/json');
        $postResponse->shouldReceive('getBody')
                     ->times(1)
                     ->andReturn('{"error":{"message":"Foo auth error","type":"OAuthException","code":191}}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $errorMessage = '';
        $errorCode = 0;

        try {
            $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        } catch (IdentityProviderException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
        }

        $this->assertEquals('OAuthException: Foo auth error', $errorMessage);
        $this->assertEquals(191, $errorCode);
    }
}
