<?php declare(strict_types=1);

use Nette\Caching\Cache;
use Nette\Caching\Storages\APCUStorage;
use Nette\Caching\Storages\FileStorage;

define(TEMP_DIR, __DIR__ . '/temp');

require __DIR__ . '/vendor/autoload.php';

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, TEMP_DIR);

$key = '../' . implode('', range("\x00", "\x1F"));

if (isset($_GET['apcu']) && $_GET['apcu'] !== '') {
	$storage = new APCUStorage();
} else {
	$storage = new FileStorage(TEMP_DIR);
}

$cache = new Cache($storage);

$class = explode('\\', get_class($cache->getStorage()));
$file = TEMP_DIR . '/' . end($class);

echo $cache->save($key, function () use ($file) {
	$counter = (int) file_get_contents($file);
	file_put_contents($file, ++$counter);
	sleep(5);

	return $counter;
}, [
	Cache::EXPIRE => '1 minute',
]);
