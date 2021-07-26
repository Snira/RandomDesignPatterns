<?php

declare(strict_types=1);

/**
 * Het Flyweight patroon is in principe een gemodificeerde object factory.
 * In plaats van dat het patroon gebruikt wordt om continu nieuwe objecten aan de maken,
 * kan het Flyweight patroon controleren of het object in kwestie al is aangemaakt.
 * Als dat laatste zo is, zal het die instantie terug geven in plaats van opnieuw een nieuwe instantie.
 * Een mooi voorbeeld van waar het Flyweight patroon toegepast kan worden is een File class in een website waar gewerkt
 * word met grote bestanden.
 */
final class File
{
    private string $data;

    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('File does not exist: ' . $filePath);
        }

        $this->data = file_get_contents($filePath);
    }

    public function getData(): string
    {
        return $this->data;
    }
}

final class Factory
{
    private static array $files = [];

    public function getFile(string $filePath): File
    {
        // Alleen het object instantieren als deze niet "herinnert" wordt.
        if (!isset(self::$files[$filePath])) {
            self::$files[$filePath] = new File($filePath);
        }

        return self::$files[$filePath];
    }

    public static function filesInCacheAmount(): int
    {
        return count(self::$files);
    }
}


$factory = new Factory();
$myLargeImageA = $factory->getFile('../Files/image.png');
$myLargeImageB = $factory->getFile('../Files/image.png');

if ($myLargeImageA === $myLargeImageB) {
    print 'Yay, these are the same object!' . PHP_EOL;
} else {
    print 'Something went wrong :(' . PHP_EOL;
}

$factory->getFile('../Files/image.png');
$factory->getFile('../Files/image2.png');
$factory->getFile('../Files/image2.png');
$factory->getFile('../Files/image.png');
$factory->getFile('../Files/image3.png');
$factory->getFile('../Files/image3.png');
$factory->getFile('../Files/image2.png');
$factory->getFile('../Files/image3.png');

print $factory::filesInCacheAmount();
