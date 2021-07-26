<?php

declare(strict_types=1);

/**
 * Het Chain of Responsibility of ook wel eens Chain of Commands pattern genoemd,
 * is een patroon die nog niet heel mega bekend is bij developers terwijl je het wel vaak gebruikt.
 * Sinds PSR-15 standardkomt het dagelijk voorbij zonder misschien goed door te hebben dat het dat patroon is.
 * In dit geval hebben we het over middlewares.
 */
interface HandlerInterface
{
    /** de volgende handler */
    public function setNext(HandlerInterface $next);

    /**
     * in een Laravel Middleware wordt in de handle de $request,
     * een Closure $next en $attributes als param meegegeven, in dit voorbeeld doen we alleen $attributes
     */
    public function handle($attributes = null);

    /**
     * De volgende handler die uitgevoerd moet worden
     */
    public function next($attributes = null);
}

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * De volgende handler die uitgevoerd moet worden
     *
     * @var HandlerInterface
     */
    protected $next;

    public function setNext(HandlerInterface $next)
    {
        $this->next = $next;
    }

    /**
     * Roep volgende handle() aan als die klaar staat, anders zijn we door de chain heen
     */
    public function next($attributes = null)
    {
        if ($this->next) {
            return $this->next->handle($attributes);
        }
    }
}

class IPCheckHandler extends AbstractHandler
{
    const BANNED_IPS = ['123.123.123.123'];

    public function handle($attributes = null)
    {
        print __METHOD__ . 'Checking if valid IP ...' . PHP_EOL;

        if (in_array($attributes['ip'], self::BANNED_IPS, true)) {
            throw new Exception("Invalid IP");
        }

        return $this->next($attributes);
    }
}

class MustBeLoggedInHandler extends AbstractHandler
{
    public function handle($attributes = null)
    {
        print __METHOD__ . 'Checking if user logged in ...' . PHP_EOL;
        if (empty($attributes['user_id'])) {
            throw new Exception("Must be logged in");
        }

        return $this->next($attributes);
    }
}

class MustBeAdminUserHandler extends AbstractHandler
{
    public function handle($attributes = null)
    {
        print __METHOD__ . 'Check if user is admin ...' . PHP_EOL;
        if (empty($attributes['is_admin'])) {
            throw new Exception("Must be admin user");
        }

        return $this->next($attributes);
    }
}


$attributes = [
    'ip' => '127.0.0.1',
    'requested_uri' => '/home',
    'user_id' => 123,
    'is_admin' => true,
];

$mustBeLoggedIn = new MustBeLoggedInHandler();
$ipCheck = new IPCheckHandler();
$adminCheck = new MustBeAdminUserHandler();

$mustBeLoggedIn->setNext($ipCheck);
$ipCheck->setNext($adminCheck);

$mustBeLoggedIn->handle($attributes);