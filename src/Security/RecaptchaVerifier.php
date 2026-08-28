<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RecaptchaVerifier
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%recaptcha.secret_key%')]
        private readonly string $secretKey,
    ) {
    }

    public function verify(
        string $token,
        ?string $remoteIp = null
    ): bool {
        if ($token === '' || $this->secretKey === '') {
            return false;
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'body' => [
                        'secret' => $this->secretKey,
                        'response' => $token,
                        'remoteip' => $remoteIp,
                    ],
                ]
            );

            $payload = $response->toArray(false);
        } catch (\Throwable) {
            return false;
        }

        return ($payload['success'] ?? false) === true;
    }
}