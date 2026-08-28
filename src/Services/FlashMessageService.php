<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FlashMessageService
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function success(string $message): void
    {
        $session = $this->requestStack->getSession();

        if ($session instanceof SessionInterface) {
            $session->getFlashBag()->add('success', $message);
        }
    }

    public function error(string $message): void
    {
        $session = $this->requestStack->getSession();

        if ($session instanceof SessionInterface) {
            $session->getFlashBag()->add('error', $message);
        }
    }
}