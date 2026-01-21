<?php

namespace SimoneBianco\DolphinParser;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SimoneBianco\DolphinParser\DTOs\ParseJobResponse;
use SimoneBianco\DolphinParser\Exceptions\ApiRequestException;
use SimoneBianco\DolphinParser\Exceptions\ConfigurationException;

/**
 * Dolphin PDF Parsers Client for Laravel.
 *
 * @example
 * // Parse a PDF file
 * $result = DolphinParser::parseFile('/path/to/file.pdf');
 *
 * // Parse base64 content
 * $result = DolphinParser::parse($base64Content);
 *
 * // Check job status
 * $status = DolphinParser::status($jobId);
 */
class DolphinParserClient
{
    protected string $endpoint;
    protected string $apiKey;
    protected int $timeout;
    protected int $retries;
    protected float $retryDelay;
    protected array $excludedLabels;
    protected array $excludedTags;
    protected ?string $callbackUrl;
    protected string $storageDisk;
    protected string $storagePath;

    public function __construct(array $config = [])
    {
        $this->endpoint = $config['endpoint'] ?? config('dolphin-parser.endpoint', '');
        $this->apiKey = $config['api_key'] ?? config('dolphin-parser.api_key', '');
        $this->timeout = $config['timeout'] ?? config('dolphin-parser.timeout', 300);
        $this->retries = $config['retries'] ?? config('dolphin-parser.retries', 3);
        $this->retryDelay = $config['retry_delay'] ?? config('dolphin-parser.retry_delay', 2);
        $this->excludedLabels = $config['excluded_labels'] ?? config('dolphin-parser.excluded_labels', ['foot', 'header']);
        $this->excludedTags = $config['excluded_tags'] ?? config('dolphin-parser.excluded_tags', ['author', 'meta_pub_date']);
        $this->callbackUrl = $config['callback_url'] ?? config('dolphin-parser.callback_url');
        $this->storageDisk = $config['storage_disk'] ?? config('dolphin-parser.storage_disk', 'local');
        $this->storagePath = $config['storage_path'] ?? config('dolphin-parser.storage_path', 'dolphin-parser');

        $this->validateConfiguration();
    }

    /**
     * Parse a PDF from base64 content (synchronous).
     *
     * @param string $base64Content Base64 encoded PDF
     * @param array $options Additional options (excluded_labels, excluded_tags, callback)
     * @return ParseJobResponse
     * @throws ApiRequestException
     * @throws ConnectionException
     */
    public function parse(string $base64Content, array $options = []): ParseJobResponse
    {
        $payload = $this->buildPayload($base64Content, $options);

        $response = $this->request()
            ->post("{$this->endpoint}/runsync", ['input' => $payload]);

        return $this->handleResponse($response);
    }

    /**
     * Parse a PDF from base64 content (asynchronous).
     * Returns immediately with job_id, use status() to check progress.
     *
     * @param string $base64Content Base64 encoded PDF
     * @param array $options Additional options
     * @return ParseJobResponse
     * @throws ApiRequestException
     * @throws ConnectionException
     */
    public function parseAsync(string $base64Content, array $options = []): ParseJobResponse
    {
        $payload = $this->buildPayload($base64Content, $options);

        $response = $this->request()
            ->post("{$this->endpoint}/run", ['input' => $payload]);

        return $this->handleResponse($response);
    }

    /**
     * Parse a PDF file from path.
     *
     * @param string $filePath Path to PDF file
     * @param array $options Additional options
     * @return ParseJobResponse
     * @throws ApiRequestException
     * @throws ConnectionException
     */
    public function parseFile(string $filePath, array $options = []): ParseJobResponse
    {
        $content = file_get_contents($filePath);
        $base64 = base64_encode($content);

        return $this->parse($base64, $options);
    }

    /**
     * Parse a PDF file asynchronously.
     *
     * @param string $filePath Path to PDF file
     * @param array $options Additional options
     * @return ParseJobResponse
     * @throws ApiRequestException
     * @throws ConnectionException
     */
    public function parseFileAsync(string $filePath, array $options = []): ParseJobResponse
    {
        $content = file_get_contents($filePath);
        $base64 = base64_encode($content);

        return $this->parseAsync($base64, $options);
    }

    /**
     * Check the status of a parsing job.
     *
     * @param string $jobId Job identifier
     * @return ParseJobResponse
     * @throws ApiRequestException
     * @throws ConnectionException
     */
    public function status(string $jobId): ParseJobResponse
    {
        $response = $this->request()
            ->get("{$this->endpoint}/status/{$jobId}");

        return $this->handleResponse($response);
    }

    /**
     * Cancel a running job.
     *
     * @param string $jobId Job identifier
     * @return array
     * @throws ConnectionException
     */
    public function cancel(string $jobId): array
    {
        $response = $this->request()
            ->post("{$this->endpoint}/runsync", [
                'input' => ['action' => 'stop_job', 'job_id' => $jobId]
            ]);

        return $response->json();
    }

    /**
     * Get parser health status.
     *
     * @return array
     * @throws ConnectionException
     */
    public function health(): array
    {
        $response = $this->request()
            ->post("{$this->endpoint}/runsync", [
                'input' => ['action' => 'health']
            ]);

        return $response->json('output', []);
    }

    /**
     * Check storage server connectivity.
     *
     * @return array
     * @throws ConnectionException
     */
    public function checkStorage(): array
    {
        $response = $this->request()
            ->post("{$this->endpoint}/runsync", [
                'input' => ['action' => 'check_storage']
            ]);

        return $response->json('output', []);
    }

    /**
     * Get parser statistics.
     *
     * @return array
     * @throws ConnectionException
     */
    public function stats(): array
    {
        $response = $this->request()
            ->post("{$this->endpoint}/runsync", [
                'input' => ['action' => 'stats']
            ]);

        return $response->json('output', []);
    }

    /**
     * List parsing jobs.
     *
     * @param array $options (statuses, limit, offset)
     * @return array
     * @throws ConnectionException
     */
    public function listJobs(array $options = []): array
    {
        $response = $this->request()
            ->post("{$this->endpoint}/runsync", [
                'input' => array_merge(['action' => 'list_jobs'], $options)
            ]);

        return $response->json('output', []);
    }

    /**
     * Get result of a completed job.
     *
     * @param string $jobId Job identifier
     * @return array
     * @throws ConnectionException
     */
    public function getResult(string $jobId): array
    {
        $response = $this->request()
            ->post("{$this->endpoint}/runsync", [
                'input' => ['action' => 'get_result', 'job_id' => $jobId]
            ]);

        return $response->json('output', []);
    }

    /**
     * Download ZIP from storage server.
     *
     * @param string $jobId Job identifier
     * @param bool $keep Keep file on storage server after download
     * @return string Local path to downloaded file
     * @throws ConnectionException
     * @throws ApiRequestException
     */
    public function downloadFromStorage(string $jobId, bool $keep = false): string
    {
        $storageEndpoint = config('dolphin-parser.storage_server.endpoint');
        $storageApiKey = config('dolphin-parser.storage_server.api_key');

        if (empty($storageEndpoint)) {
            throw ApiRequestException::requestFailed('Storage server not configured');
        }

        $baseUrl = str_replace('/upload', '', $storageEndpoint);
        $url = "{$baseUrl}/download/{$jobId}" . ($keep ? '?keep=true' : '');

        $response = Http::withHeaders(['X-API-Key' => $storageApiKey])
            ->timeout($this->timeout)
            ->get($url);

        if (!$response->successful()) {
            throw ApiRequestException::requestFailed(
                "Failed to download from storage: {$response->status()}",
                $response->json()
            );
        }

        $filename = "{$jobId}.zip";
        $path = "{$this->storagePath}/{$filename}";

        Storage::disk($this->storageDisk)->put($path, $response->body());

        return Storage::disk($this->storageDisk)->path($path);
    }

    /**
     * Build request payload.
     */
    protected function buildPayload(string $base64Content, array $options): array
    {
        return [
            'pdf_base64' => $base64Content,
            'excluded_labels' => $options['excluded_labels'] ?? $this->excludedLabels,
            'excluded_tags' => $options['excluded_tags'] ?? $this->excludedTags,
            'callback' => $options['callback'] ?? $this->callbackUrl,
        ];
    }

    /**
     * Create HTTP request with auth and retry logic.
     */
    protected function request(): PendingRequest
    {
        return Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->retry($this->retries, $this->retryDelay * 1000);
    }

    /**
     * Handle API response.
     * @throws ApiRequestException
     */
    protected function handleResponse(Response $response): ParseJobResponse
    {
        if (!$response->successful()) {
            throw ApiRequestException::requestFailed(
                $response->body(),
                $response->json()
            );
        }

        return ParseJobResponse::fromResponse($response->json());
    }

    /**
     * Validate configuration.
     * @throws ConfigurationException
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->endpoint)) {
            throw ConfigurationException::missingEndpoint();
        }

        if (empty($this->apiKey)) {
            throw ConfigurationException::missingApiKey();
        }
    }

    /**
     * Get current configuration.
     */
    public function getConfig(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'timeout' => $this->timeout,
            'retries' => $this->retries,
            'excluded_labels' => $this->excludedLabels,
            'excluded_tags' => $this->excludedTags,
            'callback_url' => $this->callbackUrl,
        ];
    }
}
