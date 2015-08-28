<?php namespace GLGeorgiev\LaravelOAuth2Client;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class User implements ResourceOwnerInterface
{

    protected $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['id'];
    }

    public function toArray()
    {
        return $this->response;
    }
}
