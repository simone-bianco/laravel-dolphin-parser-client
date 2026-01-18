# Laravel Dolphin Parser Client

A Laravel package for parsing PDFs using the Dolphin PDF Parser (RunPod Serverless).

[![Latest Version on Packagist](https://img.shields.io/packagist/v/simonebianco/laravel-dolphin-parser.svg)](https://packagist.org/packages/simonebianco/laravel-dolphin-parser)
[![License](https://img.shields.io/github/license/simonebianco/laravel-dolphin-parser.svg)](LICENSE.md)

## Features

- 🐬 **AI-Powered PDF Parsing**: Extract text, tables, and figures using ByteDance Dolphin model
- ⚡ **Sync & Async**: Choose between synchronous or asynchronous parsing
- 🎭 **Facade Support**: Clean, expressive syntax with `DolphinParser::parse()`
- 📦 **Storage Integration**: Download results directly to Laravel storage
- 🔄 **Retry Logic**: Built-in retry with exponential backoff
- 📝 **Type-Safe DTOs**: Full type safety with response objects

## Requirements

- PHP 8.1+
- Laravel 10.x or 11.x
- A running Dolphin Parser on RunPod

## Installation

```bash
composer require simonebianco/laravel-dolphin-parser
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=dolphin-parser-config
```

Or use the install command:

```bash
php artisan dolphin-parser:install
```

## Configuration

Add these to your `.env` file:

```env
# Required
DOLPHIN_PARSER_ENDPOINT=https://api.runpod.ai/v2/YOUR_ENDPOINT_ID
DOLPHIN_PARSER_API_KEY=your-runpod-api-key

# Optional
DOLPHIN_PARSER_TIMEOUT=300
DOLPHIN_PARSER_RETRIES=3
DOLPHIN_PARSER_CALLBACK_URL=https://your-app.com/webhook/pdf-parsed

# Storage Server (if using)
DOLPHIN_STORAGE_ENDPOINT=http://your-storage-server.com/upload
DOLPHIN_STORAGE_API_KEY=your-storage-key
```

## Quick Start

### Parse a PDF File

```php
use SimoneBianco\DolphinParser\Facades\DolphinParser;

// Synchronous parsing (waits for completion)
$result = DolphinParser::parseFile('/path/to/document.pdf');

if ($result->isSuccess()) {
    echo "Parsed {$result->pagesTotal} pages with {$result->figuresCount} figures";
}
```

### Parse Base64 Content

```php
$base64 = base64_encode(file_get_contents('/path/to/file.pdf'));

$result = DolphinParser::parse($base64);
```

### Async Parsing

```php
// Start parsing (returns immediately)
$result = DolphinParser::parseFileAsync('/path/to/large-document.pdf');

$jobId = $result->jobId;

// Later, check status
$status = DolphinParser::status($jobId);

if ($status->isSuccess()) {
    // Download from storage
    $localPath = DolphinParser::downloadFromStorage($jobId);
}
```

---

## API Reference

### Parsing Methods

#### `parse(string $base64Content, array $options = []): ParseJobResponse`

Parse a PDF from base64 content synchronously.

```php
$result = DolphinParser::parse($base64, [
    'excluded_labels' => ['foot', 'header'],
    'excluded_tags' => ['author'],
    'callback' => 'https://my-app.com/webhook',
]);
```

#### `parseAsync(string $base64Content, array $options = []): ParseJobResponse`

Parse asynchronously. Returns immediately with job ID.

```php
$result = DolphinParser::parseAsync($base64);
echo "Job started: {$result->jobId}";
```

#### `parseFile(string $filePath, array $options = []): ParseJobResponse`

Parse a file from filesystem path.

```php
$result = DolphinParser::parseFile('/path/to/document.pdf');
```

#### `parseFileAsync(string $filePath, array $options = []): ParseJobResponse`

Parse a file asynchronously.

```php
$result = DolphinParser::parseFileAsync('/path/to/large-file.pdf');
```

---

### Job Management

#### `status(string $jobId): ParseJobResponse`

Check the status of a parsing job.

```php
$status = DolphinParser::status('job-123');

if ($status->isProcessing()) {
    echo "Still processing...";
} elseif ($status->isSuccess()) {
    echo "Done!";
} elseif ($status->isFailed()) {
    echo "Failed: " . $status->error;
}
```

#### `cancel(string $jobId): array`

Cancel a running job.

```php
DolphinParser::cancel('job-123');
```

#### `getResult(string $jobId): array`

Get the full result of a completed job.

```php
$result = DolphinParser::getResult('job-123');
```

#### `listJobs(array $options = []): array`

List parsing jobs.

```php
$jobs = DolphinParser::listJobs([
    'limit' => 20,
    'statuses' => ['SUCCESS', 'FAILED'],
]);
```

---

### Storage Server

#### `downloadFromStorage(string $jobId, bool $keep = false): string`

Download ZIP from storage server to local filesystem.

```php
// Download and delete from storage
$localPath = DolphinParser::downloadFromStorage('job-123');

// Download but keep on storage
$localPath = DolphinParser::downloadFromStorage('job-123', keep: true);

// $localPath is the full path to the downloaded .zip file
```

---

### Diagnostics

#### `health(): array`

Check parser health.

```php
$health = DolphinParser::health();
// ['status' => 'ok', 'service' => 'dolphin-pdf-parser']
```

#### `checkStorage(): array`

Verify storage server connectivity.

```php
$storage = DolphinParser::checkStorage();
// ['status' => 'OK', 'message' => 'Storage server is reachable', ...]
```

#### `stats(): array`

Get parser statistics.

```php
$stats = DolphinParser::stats();
// ['total_directories' => 42, 'total_size_mb' => 128.5, ...]
```

---

## Response Object

The `ParseJobResponse` DTO provides these properties:

| Property             | Type    | Description                                         |
| -------------------- | ------- | --------------------------------------------------- |
| `status`             | string  | `SUCCESS`, `FAILED`, `PENDING`, `PROCESSING`        |
| `jobId`              | string  | Unique job identifier                               |
| `message`            | ?string | Success message                                     |
| `zipUrl`             | ?string | URL to ZIP file (RunPod internal)                   |
| `pagesTotal`         | ?int    | Total pages in PDF                                  |
| `pagesProcessed`     | ?int    | Pages successfully processed                        |
| `figuresCount`       | ?int    | Number of figures extracted                         |
| `callbackSentStatus` | ?string | `SENT`, `NOT_SET`, `ERROR`                          |
| `callbackError`      | ?string | Error message if callback failed                    |
| `error`              | ?string | Error message if job failed                         |
| `output`             | ?array  | Parsed content (if `INCLUDE_OUTPUT_IN_STATUS=true`) |

### Helper Methods

```php
$result->isSuccess();      // Job completed successfully
$result->isFailed();       // Job failed
$result->isProcessing();   // Job still running
$result->callbackWasSent(); // Callback was delivered
```

---

## Dependency Injection

You can inject the client directly:

```php
use SimoneBianco\DolphinParser\DolphinParserClient;

class PdfController extends Controller
{
    public function __construct(
        private DolphinParserClient $parser
    ) {}

    public function parse(Request $request)
    {
        $result = $this->parser->parseFile($request->file('pdf')->path());
        return response()->json($result->toArray());
    }
}
```

---

## Events & Webhooks

When using callbacks, your webhook receives:

```json
{
  "status": "SUCCESS",
  "job_id": "abc-123",
  "pages_total": 5,
  "figures_count": 3,
  "zip_base64": "UEsDBBQ..."
}
```

Example webhook controller:

```php
public function handlePdfParsed(Request $request)
{
    $jobId = $request->input('job_id');
    $status = $request->input('status');

    if ($status === 'SUCCESS') {
        $zipBase64 = $request->input('zip_base64');
        $zipContent = base64_decode($zipBase64);

        Storage::disk('local')->put("results/{$jobId}.zip", $zipContent);
    }

    return response()->json(['received' => true]);
}
```

---

## Configuration Options

| Config Key                | Env Variable                     | Default                | Description               |
| ------------------------- | -------------------------------- | ---------------------- | ------------------------- |
| `endpoint`                | `DOLPHIN_PARSER_ENDPOINT`        | -                      | RunPod endpoint URL       |
| `api_key`                 | `DOLPHIN_PARSER_API_KEY`         | -                      | RunPod API key            |
| `timeout`                 | `DOLPHIN_PARSER_TIMEOUT`         | `300`                  | Request timeout (seconds) |
| `retries`                 | `DOLPHIN_PARSER_RETRIES`         | `3`                    | Retry attempts            |
| `retry_delay`             | `DOLPHIN_PARSER_RETRY_DELAY`     | `2`                    | Delay between retries     |
| `excluded_labels`         | `DOLPHIN_PARSER_EXCLUDED_LABELS` | `foot,header`          | Labels to exclude         |
| `excluded_tags`           | `DOLPHIN_PARSER_EXCLUDED_TAGS`   | `author,meta_pub_date` | Tags to exclude           |
| `callback_url`            | `DOLPHIN_PARSER_CALLBACK_URL`    | `null`                 | Webhook URL for results   |
| `storage_disk`            | `DOLPHIN_PARSER_STORAGE_DISK`    | `local`                | Laravel storage disk      |
| `storage_path`            | `DOLPHIN_PARSER_STORAGE_PATH`    | `dolphin-parser`       | Path within disk          |
| `storage_server.endpoint` | `DOLPHIN_STORAGE_ENDPOINT`       | -                      | Storage server URL        |
| `storage_server.api_key`  | `DOLPHIN_STORAGE_API_KEY`        | -                      | Storage server API key    |

---

## Error Handling

```php
use SimoneBianco\DolphinParser\Exceptions\DolphinParserException;
use SimoneBianco\DolphinParser\Exceptions\ConfigurationException;
use SimoneBianco\DolphinParser\Exceptions\ApiRequestException;

try {
    $result = DolphinParser::parseFile('/path/to/file.pdf');
} catch (ConfigurationException $e) {
    // Missing endpoint or API key
    Log::error('Parser not configured: ' . $e->getMessage());
} catch (ApiRequestException $e) {
    // API request failed
    Log::error('API error: ' . $e->getMessage());
    $response = $e->getResponse(); // Get raw response if available
} catch (DolphinParserException $e) {
    // Generic parser error
    Log::error('Parser error: ' . $e->getMessage());
}
```

---

## Testing

```bash
composer test
```

For mocking in your tests:

```php
use SimoneBianco\DolphinParser\Facades\DolphinParser;
use SimoneBianco\DolphinParser\DTOs\ParseJobResponse;

DolphinParser::shouldReceive('parseFile')
    ->once()
    ->andReturn(new ParseJobResponse(
        status: 'SUCCESS',
        jobId: 'test-123',
        pagesTotal: 5,
        figuresCount: 2,
    ));
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.
