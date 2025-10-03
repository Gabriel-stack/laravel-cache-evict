<?php

declare(strict_types=1);

namespace Vectorial1024\LaravelCacheEvict;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class AbstractEvictStrategy
{
    protected OutputStyle $output;

    public string $storeName;

    public function __construct(string $storeName)
    {
        $this->storeName = $storeName;
        // default should be null output; useful when somehow calling this outside of Artisan console context
        // does not affect actual behavior
        // todo good opportunity to refactor with property hooks in PHP 8.4
        $this->output = new OutputStyle(new StringInput(''), new NullOutput());
    }

    /**
     * Sets the console output of this strategy; this may be helpful when creating progress bars.
     */
    public function setOutput(OutputStyle $output)
    {
        $this->output = $output;
    }

    /**
     * Execute the key eviction strategy.
     * 
     * Children classes should define their eviction logic here.
     */
    abstract public function execute();

    /**
     * Converts the given bytes to a human readable format, to be displayed to the user.
     * @param int $bytes
     * @return string The human-readable byte size.
     */
    protected function bytesToHuman(int $bytes): string
    {
        // it turns out Laravel already has a helper for this
        // but it requires the intl extension and is only available in Laravel 10+
        // prefer the Laravel solution if available and supported; if they don't have it, then we can still use the fallback hand-crafted solution
        if (extension_loaded('intl') && class_exists(\Illuminate\Support\Number::class)) {
            return \Illuminate\Support\Number::fileSize($bytes, 2, 2);
        }
        // see https://stackoverflow.com/questions/15188033/human-readable-file-size
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
