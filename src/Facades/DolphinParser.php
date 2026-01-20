<?php

namespace SimoneBianco\DolphinParser\Facades;

use SimoneBianco\DolphinParser\Exceptions\ApiRequestException;
use Illuminate\Support\Facades\Facade;
use SimoneBianco\DolphinParser\DTOs\ParseJobResponse;

/**
 * Facade for DolphinParserClient.
 *
 * @method static ParseJobResponse parse(string $base64Content, array $options = []) @throws ApiRequestException
 * @method static ParseJobResponse parseAsync(string $base64Content, array $options = []) @throws ApiRequestException
 * @method static ParseJobResponse parseFile(string $filePath, array $options = []) @throws ApiRequestException
 * @method static ParseJobResponse parseFileAsync(string $filePath, array $options = []) @throws ApiRequestException
 * @method static ParseJobResponse status(string $jobId) @throws ApiRequestException
 * @method static array cancel(string $jobId) @throws ApiRequestException
 * @method static array health() @throws ApiRequestException
 * @method static array checkStorage() @throws ApiRequestException
 * @method static array stats() @throws ApiRequestException
 * @method static array listJobs(array $options = []) @throws ApiRequestException
 * @method static array getResult(string $jobId) @throws ApiRequestException
 * @method static string downloadFromStorage(string $jobId, bool $keep = false) @throws ApiRequestException
 * @method static array getConfig()
 *
 * @see \SimoneBianco\DolphinParser\DolphinParserClient
 * @mixin \SimoneBianco\DolphinParser\DolphinParserClient
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
