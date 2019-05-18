<?php

namespace Nette\Caching;

interface ISelfLock
{

	/**
	 * return value from cache
	 */
	function entry(string $key, callable $data, array $dependencies);
}
