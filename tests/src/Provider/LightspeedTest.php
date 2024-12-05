<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\Lightspeed;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\StreamInterface;
use Mockery as m;

class FooLightspeedProvider extends Lightspeed
{
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return json_decode('{"@attributes":{"count":"1"},"Account":{"accountID":"12345","name":"Boo Name","link":{"@attributes":{"href":"mock_Lightspeed_url"}}}}', true);
    }
}

class LightspeedTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Lightspeed
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Lightspeed([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown(): void
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

        $this->assertEquals('/oauth/access_token.php', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $stream = $this->createMock(StreamInterface::class);
            $stream
                ->method('__toString')
                ->willReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');

        $response = m::mock(\Psr\Http\Message\ResponseInterface::class);
        $response->shouldReceive('getHeader')
            ->times(1)
            // ->andReturn('application/json');
            ->andReturn(['Content-Type' => 'application/json']);
        $response->shouldReceive('getBody')
            ->times(1)
            // ->andReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');
            // ->andReturn(m::mock(StreamInterface::class, ['getContents' => '{"access_token":"long-lived-token","token_type":"bearer","expires_in":3600}']));
            ->andReturn($stream);

        $client = m::mock(\GuzzleHttp\ClientInterface::class);
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
        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('{"access_token":"long-lived-token","token_type":"bearer","expires_in":3600}');

        $response = m::mock(\Psr\Http\Message\ResponseInterface::class);
        $response->shouldReceive('getHeader')
            ->times(1)
            // ->andReturn('application/json');
            ->andReturn(['Content-Type' => 'application/json']);
        $response->shouldReceive('getBody')
            ->times(1)
            // ->andReturn('{"access_token":"long-lived-token","token_type":"bearer","expires_in":3600}');
            ->andReturn($stream);

        $client = m::mock(\GuzzleHttp\ClientInterface::class);
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getLongLivedAccessToken('short-lived-token');

        $this->assertEquals('long-lived-token', $token->getToken());
    }

    public function testScopes()
    {
        $this->assertEquals(['employee:all'], $this->provider->getDefaultScopes());
    }

    public function testAccountData()
    {
        $provider = new FooLightspeedProvider();

        $token = m::mock(\League\OAuth2\Client\Token\AccessToken::class);
        $account = $provider->getResourceOwner($token);

        $this->assertEquals(12345, $account->getId($token));
        $this->assertEquals('Boo Name', $account->getName($token));
    }
}
