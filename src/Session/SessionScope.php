<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Session;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Exception\ScopeException;
use Spiral\Session\Middleware\SessionMiddleware;

/**
 * Provides access to the currently active session scope.
 */
final class SessionScope implements SessionInterface
{
    /** Locations for unnamed segments i.e. default segment. */
    private const DEFAULT_SECTION = '_DEFAULT';

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function isStarted(): bool
    {
        return $this->getActiveSession()->isStarted();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function resume()
    {
        return $this->getActiveSession()->resume();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function getID(): ?string
    {
        return $this->getActiveSession()->getID();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function regenerateID(): SessionInterface
    {
        $this->getActiveSession()->regenerateID();

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function commit(): bool
    {
        return $this->getActiveSession()->commit();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function abort(): bool
    {
        return $this->getActiveSession()->abort();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function destroy(): bool
    {
        return $this->getActiveSession()->destroy();
    }

    /**
     * @inheritDoc
     *
     * @throws ScopeException
     */
    public function getSection(string $name = null): SessionSectionInterface
    {
        return new SectionScope($this, $name ?? self::DEFAULT_SECTION);
    }

    /**
     * @return SessionInterface
     *
     * @throws ScopeException
     */
    public function getActiveSession(): SessionInterface
    {
        try {
            $request = $this->container->get(ServerRequestInterface::class);
            $session = $request->getAttribute(SessionMiddleware::ATTRIBUTE);
            if ($session === null) {
                throw new ScopeException('Unable to receive active Session, invalid request scope');
            }

            return $session;
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to receive active session', $e->getCode(), $e);
        }
    }
}
