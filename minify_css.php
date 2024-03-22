<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CLI script to minify the CSS files of a Moodle plugin.
 *
 * @author     Yannis Maragos <maragos.y@wideservices.gr>
 * @copyright  2024 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

// Get the moodlepath from the command line arguments.
// phpcs:ignore moodle.Files.MoodleInternal.MoodleInternalGlobalState
$moodlepath = false;

for ($i = 1; $i < count($argv); $i++) {
    if (substr($argv[$i], 0, 2) === '-m' || substr($argv[$i], 0, 12) === '--moodlepath') {
        $parts = explode('=', $argv[$i]);
        $moodlepath = $parts[1];
        break;
    }
}

$help = <<<EOT
CLI script to minify the CSS files of a Moodle plugin.

Options:
 -h, --help                Print out this help
 -m, --moodlepath          The full path to the Moodle directory
 -p, --pluginpath          The full path to the plugin directory
 -f, --files               Comma-separated list of CSS files
     --showdebugging       Show developer level debugging information

Example:
sudo -u www-data /usr/bin/php minify_css.php --moodlepath=/var/www/html/workplace_423
--pluginpath=/var/www/html/workplace_423/local/datatables --files=custom.css

EOT;

// Check for the existence of 'moodlepath' argument.
if (!$moodlepath || !file_exists($moodlepath . '/config.php')) {
    echo $help;
    exit(1);
}

require($moodlepath . '/config.php');
require_once($CFG->libdir . '/clilib.php');

list($options, $unrecognized) = cli_get_params(
    [
        'moodlepath' => false,
        'pluginpath' => false,
        'files' => false,
        'help' => false,
        'showdebugging' => false,
    ],
    [
        'm' => 'moodlepath',
        'p' => 'pluginpath',
        'f' => 'files',
        'h' => 'help',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

// Check for the existence of 'help', 'pluginpath', 'files' arguments.
if ($options['help'] || empty($options['pluginpath']) || empty($options['files'])) {
    echo $help;
    exit(1);
}

if ($options['showdebugging']) {
    mtrace('Enabling debugging...');
    set_debugging(DEBUG_DEVELOPER, true);
}

if (CLI_MAINTENANCE) {
    cli_error('CLI maintenance mode active, CLI execution suspended');
}

// Check for the existence of the plugin path.
if (empty($options['pluginpath']) || !is_dir($options['pluginpath'])) {
    cli_error('Plugin not found');
}

mtrace('Minifying CSS files for plugin ' . $options['pluginpath']);

core_php_time_limit::raise();

// Minify files.
$tempfiles = explode(',', $options['files']);
$tempfiles = array_map(function ($file) use ($options) {
    return $options['pluginpath'] . '/style/' . $file;
}, $tempfiles);
$files = array_filter($tempfiles, 'file_exists');

foreach ($files as $file) {
    $output = core_minify::css_files($files);
    $target = str_replace('.css', '.min.css', $file);
    file_put_contents($target, $output);
}

mtrace('Done!');
