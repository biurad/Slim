<?php

declare(strict_types=1);

/*
 * This file is part of DivineNii opensource projects.
 *
 * PHP version 7.4 and above required
 *
 * @author    Divine Niiquaye Ibok <divineibok@gmail.com>
 * @copyright 2019 DivineNii (https://divinenii.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Workaround https://bugs.php.net/64566
if (ini_get('auto_prepend_file') && !in_array(realpath(ini_get('auto_prepend_file')), get_included_files(), true)) {
    require ini_get('auto_prepend_file');
}

if (is_file($_SERVER['DOCUMENT_ROOT'] . \DIRECTORY_SEPARATOR . $_SERVER['SCRIPT_NAME'])) {
    return false;
}

$script = $_ENV['APP_FRONT_CONTROLLER'] ?? 'index.php';

$_SERVER = array_merge($_SERVER, $_ENV);
$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . \DIRECTORY_SEPARATOR . $script;

// Since we are rewriting to app_dev.php, adjust SCRIPT_NAME and PHP_SELF accordingly
$_SERVER['SCRIPT_NAME'] = \DIRECTORY_SEPARATOR . $script;
$_SERVER['PHP_SELF'] = \DIRECTORY_SEPARATOR . $script;

require $script;

error_log(sprintf('%s:%d [%d]: %s', $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'], http_response_code(), $_SERVER['REQUEST_URI']), 4);
