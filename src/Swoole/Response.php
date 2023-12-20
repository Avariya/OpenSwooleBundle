<?php
declare(strict_types=1);

namespace OpenSwooleServerBundle\Swoole;

/**
 * Class Response
 */
class Response
{
    /**
     * @param \Swoole\Http\Response                      $response
     * @param \Symfony\Component\HttpFoundation\Response $symfonyResponse
     */
    public static function toSwoole(\OpenSwoole\Http\Response $response, \Symfony\Component\HttpFoundation\Response $symfonyResponse): void
    {
        $allHeadersWithoutCookies = $symfonyResponse->headers->allPreserveCaseWithoutCookies();
        // headers
        foreach ($allHeadersWithoutCookies as $name => $values) {
            foreach ($values as $value) {
                if ($value) {
                    $response->header($name, (string) $value);
                }
            }
        }

        // status
        $response->status($symfonyResponse->getStatusCode());

        // cookies
        foreach ($symfonyResponse->headers->getCookies() as $cookie) {
            $response->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }

        $response->end($symfonyResponse->getContent());
    }
}