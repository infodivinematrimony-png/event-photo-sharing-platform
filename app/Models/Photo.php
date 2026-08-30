<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Photo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'filename',
        'original_path',
        'optimized_path',
        'watermarked_path',
        'thumbnail_path',
        'file_size',
        'width',
        'height',
        'mime_type',
        'processing_status',
        'processing_error',
        'is_visible',
        'display_order',
        'caption',
        'access_token',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_visible' => 'boolean',
        'display_order' => 'integer',
        'last_downloaded_at' => 'datetime',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->access_token) {
                $model->access_token = Str::random(32);
            }
        });
    }

    /**
     * Relationships
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get image URL for display
     */
    public function getDisplayUrl(): string
    {
        if ($this->event->watermark_enabled) {
            return asset('storage/' . $this->watermarked_path);
        }

        return asset('storage/' . $this->optimized_path);
    }

    /**
     * Get original image URL (for authorized download)
     */
    public function getOriginalUrl(): string
    {
        return route('photos.download', $this->access_token);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(): string
    {
        return asset('storage/' . $this->thumbnail_path);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['processing_status' => 'processing']);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['processing_status' => 'completed']);
    }

    /**
     * Mark as failed with error message
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'processing_status' => 'failed',
            'processing_error' => $error,
        ]);
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    /**
     * Check if processing is complete
     */
    public function isProcessingComplete(): bool
    {
        return $this->processing_status === 'completed';
    }

    /**
     * Check if processing failed
     */
    public function isProcessingFailed(): bool
    {
        return $this->processing_status === 'failed';
    }
}
