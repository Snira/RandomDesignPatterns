<?php

declare(strict_types=1);

/**
 * Memento patroon is er eentje die ingezet kan worden in use cases waarbij het belangrijk is
 * dat een state van een object makkelijk terug te halen is.
 * Hiervoor zijn 3 classes belangrijk.
 * De Originator is de class waarvan we de state willen gaan bewaren.
 */
class Originator
{
    /**
     * De "states" waarin wij in dit voorbeeld werken zijn voor het gemak gewoon wat random strings.
     * Maar beeld je in dat dit normaal ingezet wordt op objecten met veel variabele velden
     */
    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;

        print  'Originator: Mijn begin-state is: ' . $this->state . PHP_EOL;
    }

    /**
     * Handle method in dit voorbeeld wat er met de originator gebeurt, en dat zijn state veranderd.
     */
    public function handle(): void
    {
        print  'Originator: uitvoeren van: ' . __METHOD__ . ' met state "' . $this->state . '"' . PHP_EOL;

        $this->state = $this->generateRandomString(30);

        print  'Originator: state veranderd in: ' . $this->state . PHP_EOL;
    }

    private function generateRandomString(int $length = 10): string
    {
        return substr(
            str_shuffle(
                str_repeat(
                    $x = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    (int)ceil($length / strlen($x))
                )
            ),
            1,
            $length,
        );
    }

    /**
     * Slaat een versie van de state op in de vorm van een Memento
     */
    public function save(): Memento
    {
        return new ConcreteMemento($this->state);
    }

    /**
     * Restore een specifieke state van de Originator uit de Memento.
     */
    public function restore(Memento $memento): void
    {
        $this->state = $memento->getState();

        print  'Originator: state veranderd in: ' . $this->state . PHP_EOL;
    }
}

interface Memento
{
    public function getName(): string;

    public function getDate(): string;
}

/**
 * Invulling van een Memento.
 */
class ConcreteMemento implements Memento
{
    private string $state;
    private string $date;

    public function __construct(string $state)
    {
        $this->state = $state;
        $this->date = date('Y-m-d H:i:s');
    }

    /**
     * De Originator gebruikt deze in de restore() om de state terug te zetten.
     */
    public function getState(): string
    {
        return $this->state;
    }

    public function getName(): string
    {
        return $this->date . ' / (' . substr($this->state, 0, 9) . '...)';
    }

    public function getDate(): string
    {
        return $this->date;
    }
}

/**
 * De Caretaker werkt met het contract van de Memento.
 * Daardoor weet het niks van de Originator zijn state.
 * Het bijhouden van de state is puur de verantwoordelijkheid van de Memento
 */
class Caretaker
{
    /** @var Memento[] */
    private array $mementos = [];
    private Originator $originator;

    public function __construct(Originator $originator)
    {
        $this->originator = $originator;
    }

    /**
     * Maak een back-up van de huidige state
     */
    public function backup(): void
    {
        print  'Caretaker: opslaan van Originator state ... ' . PHP_EOL;

        $this->mementos[] = $this->originator->save();
    }

    /**
     * Draai terug naar de vorige state
     */
    public function undo(): void
    {
        if (!count($this->mementos)) {
            return;
        }

        $memento = array_pop($this->mementos);

        print  'Caretaker: terug draaien naar state: ' . $memento->getName() . PHP_EOL;
    }

    public function showHistory(): void
    {
        print  'Caretaker: dit zijn alle huidige mementos: ' . PHP_EOL;

        foreach ($this->mementos as $memento) {
            echo $memento->getName() . "\n";
        }
    }
}


$originator = new Originator('Dit is het begin');
$caretaker = new Caretaker($originator);

$caretaker->backup();
$originator->handle();

$caretaker->backup();
$originator->handle();

$caretaker->backup();
$originator->handle();

print PHP_EOL;
$caretaker->showHistory();

print 'en nu eentje terug!' . PHP_EOL;
$caretaker->undo();

print 'poah en nog een!' . PHP_EOL;
$caretaker->undo();

print 'weer een paar erbij' . PHP_EOL;


$caretaker->backup();
$originator->handle();
$caretaker->backup();
$originator->handle();
$caretaker->backup();
$originator->handle();
$caretaker->backup();
$originator->handle();

print 'en nu terug naar de eerste' . PHP_EOL;
$memento = new ConcreteMemento('Dit is het begin');
$originator->restore($memento);
$originator->handle();
$caretaker->backup();

$caretaker->showHistory();