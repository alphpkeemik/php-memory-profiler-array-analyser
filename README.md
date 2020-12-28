# php-memory-profiler-array-analyser
Simple PHP script to analyse [php-memprof](https://github.com/arnaud-lb/php-memory-profiler) array output.

# usage
## collecting data
* Install php-memprof
* Add to point where memory is increased and code iterates 
```
define('DIR_DUMPS', '/abs/path/to/php-memory-profiler/memprof_dump_array');
$fileName = DIR_DUMPS
    . '/memory_dump.'
    . date('Y-m-d His')
    . '.json';
file_put_contents(
    $fileName,
    json_encode(memprof_dump_array())
);
```
* run code
## analysing
* change DIR_DUMPS in analyse.php
* run analyse.php
