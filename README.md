# Moodle Minify CSS

This is a CLI script to minify the CSS files of a Moodle plugin.

## Usage

The script accepts the following command line options:

- `-h, --help`: Print out this help
- `-m, --moodlepath`: The full path to the Moodle directory
- `-p, --pluginpath`: The full path to the plugin directory
- `-f, --files`: Comma-separated list of CSS files
- `--showdebugging`: Show developer level debugging information

Example:

```bash
php minify_css.php --moodlepath=/path/to/moodle --pluginpath=/path/to/plugin --files=file1.css,file2.css
```

This will minify the `file1.css` and `file2.css` files in the plugin `style` directory.
