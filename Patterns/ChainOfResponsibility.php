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
     * een Closure $next en $request als param meegegeven,
     * in dit voorbeeld doen we een array met wat request settings
     */
    public function handle($request = null);

    /**
     * De volgende handler die uitgevoerd moet worden
     */
    public function next($request = null);
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
    public function next($request = null)
    {
        if ($this->next) {
            return $this->next->handle($request);
        }
    }
}

class IPCheckHandler extends AbstractHandler
{
    const BANNED_IPS = ['123.123.123.123'];

    public function handle($request = null)
    {
        print __METHOD__ . 'Checking if valid IP ...' . PHP_EOL;

        if (in_array($request['ip'], self::BANNED_IPS, true)) {
            throw new Exception("Invalid IP");
        }

        return $this->next($request);
    }
}

class MustBeLoggedInHandler extends AbstractHandler
{
    public function handle($request = null)
    {
        print __METHOD__ . 'Checking if user logged in ...' . PHP_EOL;
        if (!$request['user_id']) {
            throw new Exception("Must be logged in");
        }

        return $this->next($request);
    }
}

class MustBeBoyHandler extends AbstractHandler
{
    public function handle($request = null)
    {
        print __METHOD__ . 'Check if user is Boy ...' . PHP_EOL;
        if (!$request['is_admin']) {
            throw new Exception("Must be Boy");
        }

        return $this->next($request);
    }
}


$request = [
    'ip' => '127.0.0.1',
    'requested_uri' => '/home',
    'user_id' => 123,
    'is_boy' => false,
];

$mustBeLoggedIn = new MustBeLoggedInHandler();
$ipCheck = new IPCheckHandler();
$boyCheck = new MustBeBoyHandler();

$mustBeLoggedIn->setNext($ipCheck);
$ipCheck->setNext($boyCheck);

$mustBeLoggedIn->handle($request);