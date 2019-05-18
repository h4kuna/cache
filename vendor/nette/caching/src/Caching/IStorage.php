<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Caching;


/**
 * Cache storage.
 */
interface IStorage extends ISimpleStorage
{

	/**
	 * Prevents item reading and writing. Lock is released by write() or remove().
	 */
	function lock(string $key): void;
}
