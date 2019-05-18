<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Caching\Storages;

use Nette;
use Nette\Caching\Cache;

class APCUStorage implements Nette\Caching\ISimpleStorage, Nette\Caching\ISelfLock
{
	/** cache structure */
	private const META_CALLBACKS = 'callbacks';
	private const META_DATA = 'data';
	private const META_DELTA = 'delta';

	/** @var string */
	private $namespace;


	public function __construct()
	{
		if (!extension_loaded('apcu')) {
			throw new Nette\NotSupportedException('APCUStorage requires PHP extension apcu which is not loaded.');
		}
		if (php_sapi_name() === 'cli') {
			throw new Nette\NotSupportedException('APCUStorage does not support cli mode.');
		}
	}


	/**
	 * Read from cache.
	 * @param string $key
	 * @return mixed|NULL
	 */
	public function read(string $key)
	{
		$key = $this->formatKey($key);

		$meta = apcu_fetch($key);

		if (!$meta) {
			return NULL;
		}

		// meta structure:
		// [
		//     data => stored data
		//     delta => relative (sliding) expiration
		//     callbacks => array of callbacks (function, args)
		// ]
		// verify dependencies
		if (isset($meta[self::META_CALLBACKS]) && !Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
			$this->remove($key);
			return NULL;
		}

		if (isset($meta[self::META_DELTA])) {
			$this->write($key, $meta, [Cache::EXPIRE => $meta[self::META_DELTA]]);
		}

		return $meta[self::META_DATA];
	}


	/**
	 * Writes item into the cache.
	 * @param string $key
	 * @param mixed $data
	 * @param array $dependencies
	 * @throws Nette\InvalidStateException
	 */
	public function write(string $key, $data, array $dependencies): void
	{
		if (isset($dependencies[Cache::ITEMS])) {
			throw new Nette\InvalidStateException('Dependent items are not supported by APCUStorage.');
		}

		if ($data === NULL) {
			$this->remove($key);
			return;
		}

		$cacheKey = $this->formatKey($key);

		$meta = [
			self::META_DATA => $data,
		];

		$expire = $this->resolveExpire($dependencies, $meta);

		if (isset($dependencies[Cache::CALLBACKS]) && $dependencies[Cache::CALLBACKS] !== []) {
			$meta[self::META_CALLBACKS] = $dependencies[Cache::CALLBACKS];
		}

		if (isset($dependencies[Cache::TAGS]) || isset($dependencies[Cache::PRIORITY])) {
			throw new Nette\InvalidStateException('This storage doesn\'t support TAGS a PRIORITY.');
		}

		apcu_store($cacheKey, $meta, $expire);
	}


	public function entry(string $key, callable $data, array $dependencies)
	{
		$meta = [
			self::META_DATA => $data,
		];
		$expire = $this->resolveExpire($dependencies, $meta);
		return apcu_entry($key, $data, $expire);
	}


	public function remove(string $key): void
	{
		apcu_delete($this->formatKey($key));
	}


	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param array $conditions
	 * @throws Nette\InvalidStateException
	 */
	public function clean(array $conditions): void
	{
		if (isset($conditions[Cache::ALL]) && $conditions[Cache::ALL] == TRUE) { // == intentionally (1, TRUE)
			apcu_clear_cache();
		} else {
			throw new Nette\InvalidStateException('This storage doesn\'t support conditions except ALL.');
		}
	}


	private function formatKey($key): string
	{
		if ($this->namespace !== NULL) {
			$key = $this->namespace . '.' . $key;
		}

		return str_replace(Cache::NAMESPACE_SEPARATOR, '.', $key);
	}


	/**
	 * @param array $dependencies
	 * @param array $meta
	 * @return int
	 */
	private function resolveExpire(array $dependencies, array &$meta): int
	{
		$expire = 0;
		if (isset($dependencies[Cache::EXPIRE])) {
			$expire = (int) $dependencies[Cache::EXPIRE];
			if (isset($dependencies[Cache::SLIDING])) {
				$meta[self::META_DELTA] = $expire; // sliding time
			}
		}
		return $expire;
	}

}
