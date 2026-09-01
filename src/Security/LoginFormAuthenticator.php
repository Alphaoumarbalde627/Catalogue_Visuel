<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Security\RecaptchaVerifier;
use App\Services\FlashMessageService;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private readonly RecaptchaVerifier $recaptchaVerifier,
        private readonly FlashMessageService $flashMessageService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        // Extract submitted data in a robust way (support nested form names)
        $data = $request->request->all();

        $find = function (array $arr, string $key) use (&$find) {
            foreach ($arr as $k => $v) {
                if ($k === $key) {
                    return $v;
                }
                if (is_array($v)) {
                    $res = $find($v, $key);
                    if ($res !== null) {
                        return $res;
                    }
                }
            }

            return null;
        };

        $email = $find($data, 'email') ?? $request->request->get('email', '');
        $password = $find($data, 'password') ?? $request->request->get('password', '');
        $csrfToken = $find($data, '_csrf_token') ?? $find($data, '_token') ?? $request->request->get('_csrf_token') ?? $request->request->get('_token');

        $rememberMe = (bool) $find($data, '_remember_me');

        $emailViolations = $this->validator->validate($email, [
            new NotBlank(message: "L'adresse e-mail est obligatoire."),
            new Email(message: "L'adresse e-mail n'est pas valide."),
        ]);
        if (count($emailViolations) > 0) {
            throw new CustomUserMessageAuthenticationException($emailViolations[0]->getMessage());
        }

        $passwordViolations = $this->validator->validate($password, [
            new NotBlank(message: 'Le mot de passe est obligatoire.'),
        ]);
        if (count($passwordViolations) > 0) {
            throw new CustomUserMessageAuthenticationException($passwordViolations[0]->getMessage());
        }

        // Mémorise le dernier email utilisé
        $request->getSession()->set(
            SecurityRequestAttributes::LAST_USERNAME,
            $email
        );

        /*
         * Vérification reCAPTCHA
         */
        $token = $request->request->get('g-recaptcha-response');
        if (!$token) {
            throw new CustomUserMessageAuthenticationException(
            'Veuillez effectuer la vérification reCAPTCHA.'
            );
        }

       if (!$this->recaptchaVerifier->verify(
            $token,
            $request->getClientIp()
        )) 
        {
            throw new CustomUserMessageAuthenticationException(
                'La vérification reCAPTCHA a échoué. Veuillez réessayer.'
            );
        }

        // Badge CSRF
        $badges = [
            new CsrfTokenBadge('authenticate', $csrfToken),
        ];

        // Badge "Se souvenir de moi"
        if ($rememberMe) {
            $rememberMeBadge = new RememberMeBadge();
            $rememberMeBadge->enable();

            $badges[] = $rememberMeBadge;
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            $badges
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {

        $this->flashMessageService->success(
            'Connexion réussie avec succès.'
        );
        if (
            $targetPath = $this->getTargetPath(
                $request->getSession(),
                $firewallName
            )
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('app_dashboard')
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
