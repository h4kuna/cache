<?php declare(strict_types=1);

namespace Nette\Caching;

interface ISimpleStorage
{
	/**
	 * Read from cache.
	 * @return mixed
	 */
	function read(string $key);

	/**
	 * Writes item into the cache.
	 */
	function write(string $key, $data, array $dependencies): void;

	/**
	 * Removes item from the cache.
	 */
	function remove(string $key): void;

	/**
	 * Removes items from the cache by conditions.
	 */
	function clean(array $conditions): void;
}
