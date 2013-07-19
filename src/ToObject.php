<?php

/**
 * This file includes everything necessary to use ToObject.
 * Because ToObject uses all of its classes, require seems
 * to do an easier job than autoloading.
*/
require_once __DIR__ . '/utils/EnumerableReflection.php';
require_once __DIR__ . '/traits/ArrayAccess_t.php';
require_once __DIR__ . '/traits/Enumerable.php';
require_once __DIR__ . '/traits/Iterator_t.php';
require_once __DIR__ . '/traits/Count_t.php';
require_once __DIR__ . '/interfaces/IComparable.php';

require_once __DIR__ . '/cls/ObjectClass.php';
require_once __DIR__ . '/cls/StringClass.php';
require_once __DIR__ . '/cls/ArrayClass.php';
require_once __DIR__ . '/cls/HashClass.php';

?>