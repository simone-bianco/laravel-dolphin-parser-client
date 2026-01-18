<?php

namespace SimoneBianco\DolphinParser\Facades;

use Illuminate\Support\Facades\Facade;
use SimoneBianco\DolphinParser\DTOs\ParseJobResponse;

/**
 * Facade for DolphinParserClient.
 *
 * @method static ParseJobResponse parse(string $base64Content, array $options = [])
 * @method static ParseJobResponse parseAsync(string $base64Content, array $options = [])
 * @method static ParseJobResponse parseFile(string $filePath, array $options = [])
 * @method static ParseJobResponse parseFileAsync(string $filePath, array $options = [])
 * @method static ParseJobResponse status(string $jobId)
 * @method static array cancel(string $jobId)
 * @method static array health()
 * @method static array checkStorage()
 * @method static array stats()
 * @method static array listJobs(array $options = [])
 * @method static array getResult(string $jobId)
 * @method static string downloadFromStorage(string $jobId, bool $keep = false)
 * @method static array getConfig()
 *
 * @see \SimoneBianco\DolphinParser\DolphinParserClient
 */
class DolphinParser extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'dolphin-parser';
    }
}
