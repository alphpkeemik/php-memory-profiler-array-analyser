#!/usr/bin/env php
<?php

define('DIR_DUMPS', '/abs/path/to/php-memory-profiler/memprof_dump_array');

function convert(int $size): string
{
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

class MemoryDiffer
{

    private $data = [];

    public function data(): void
    {
        $diffs = [];
        $sort = [];
        foreach ($this->data as $name => $value) {
            $change = max($value) - min($value);
            if (!$change) {
                continue;
            }
            $diffs[] = $name . "\n\t" . convert($change);
            $sort[] = $change;
        }
        array_multisort($sort, $diffs);

        foreach ($diffs as $value) {
            echo $value . "\n\n";
        }
    }

    public function add(array $called_functions, string $parent = '')
    {
        foreach ($called_functions as $name => $function) {
            $fullName = "$name";
            if (preg_match('/spl_autoload_call/', $fullName)) {
                continue;
            }
            if (!key_exists($fullName, $this->data)) {
                $this->data[$fullName] = [];
            }
            $this->data[$fullName][] = $function['memory_size_inclusive'];
            $this->add($function['called_functions'], "$fullName\n\t");
        }
    }


}

$differ = new class () {
    private $data = [];
    private $sort = [];

    public function add(array $new, int $time): void
    {
        $this->data[] = $new;
        $this->sort[] = $time;
    }

    public function iterate(): array
    {
        array_multisort($this->data, $this->sort);

        return $this->data;
    }

    public function diffMemory(): Generator
    {
        foreach ($this->iterate() as $row) {
            yield convert($row['memory_size']);
        }
    }

    public function debug(): void
    {
        $md = new MemoryDiffer();
        foreach ($this->iterate() as $row) {
            $md->add($row['called_functions']);
        }

        $md->data();
    }
};

foreach (glob(DIR_DUMPS. '/memory_dump.*.json') as $file) {
    $data = json_decode(file_get_contents($file), true);
    $date = preg_match('/memory_dump\.(.*)\.json/', basename($file));
    $differ->add($data, strtotime($date));
}

$differ->debug();
