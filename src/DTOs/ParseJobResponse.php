<?php

namespace SimoneBianco\DolphinParser\DTOs;

use Illuminate\Contracts\Support\Arrayable;

/**
 * DTO for parse job response.
 */
class ParseJobResponse implements Arrayable
{
    public function __construct(
        public readonly string $status,
        public readonly string $jobId,
        public readonly ?string $message = null,
        public readonly ?string $zipUrl = null,
        public readonly ?int $pagesTotal = null,
        public readonly ?int $pagesProcessed = null,
        public readonly ?int $figuresCount = null,
        public readonly ?string $callbackSentStatus = null,
        public readonly ?string $callbackError = null,
        public readonly ?string $error = null,
        public readonly ?array $output = null,
        public readonly ?int $delayTime = null,
        public readonly ?int $executionTime = null,
        public readonly ?string $workerId = null,
    ) {}

    /**
     * Create from RunPod API response.
     */
    public static function fromResponse(array $data): self
    {
        $output = $data['output'] ?? [];
        
        return new self(
            status: $output['status'] ?? $data['status'] ?? 'UNKNOWN',
            jobId: $output['job_id'] ?? $data['id'] ?? '',
            message: $output['message'] ?? null,
            zipUrl: $output['zip_url'] ?? null,
            pagesTotal: $output['pages_total'] ?? null,
            pagesProcessed: $output['pages_processed'] ?? null,
            figuresCount: $output['figures_count'] ?? null,
            callbackSentStatus: $output['callback_sent_status'] ?? null,
            callbackError: $output['callback_error'] ?? null,
            error: $output['error'] ?? null,
            output: $output['output'] ?? null,
            delayTime: $data['delayTime'] ?? null,
            executionTime: $data['executionTime'] ?? null,
            workerId: $data['workerId'] ?? null,
        );
    }

    /**
     * Check if job completed successfully.
     */
    public function isSuccess(): bool
    {
        return strtoupper($this->status) === 'SUCCESS';
    }

    /**
     * Check if job failed.
     */
    public function isFailed(): bool
    {
        return strtoupper($this->status) === 'FAILED';
    }

    /**
     * Check if job is still processing.
     */
    public function isProcessing(): bool
    {
        return in_array(strtoupper($this->status), ['PENDING', 'PROCESSING', 'IN_QUEUE', 'IN_PROGRESS']);
    }

    /**
     * Check if callback was sent successfully.
     */
    public function callbackWasSent(): bool
    {
        return $this->callbackSentStatus === 'SENT';
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'job_id' => $this->jobId,
            'message' => $this->message,
            'zip_url' => $this->zipUrl,
            'pages_total' => $this->pagesTotal,
            'pages_processed' => $this->pagesProcessed,
            'figures_count' => $this->figuresCount,
            'callback_sent_status' => $this->callbackSentStatus,
            'callback_error' => $this->callbackError,
            'error' => $this->error,
            'output' => $this->output,
            'delay_time' => $this->delayTime,
            'execution_time' => $this->executionTime,
            'worker_id' => $this->workerId,
        ];
    }
}
