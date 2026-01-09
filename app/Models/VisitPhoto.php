<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VisitPhoto extends Model
{
    use HasFactory;

    protected $table = 'visit_photos';

    protected $fillable = [
        'visit_id',
        'photo_path',
        'descripcion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($photo) {
            // Convert photo_path to WebP if exists
            if ($photo->photo_path) {
                $convertedPath = \App\Services\ImageConversionService::convertToWebP($photo->photo_path);
                if ($convertedPath && $convertedPath !== $photo->photo_path) {
                    // Update without triggering another save event
                    $photo->updateQuietly(['photo_path' => $convertedPath]);
                }
            }
        });
    }

    /**
     * Relationship with Visit
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    // Accessor para obtener la URL completa de la imagen
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    // Accessor para verificar si la imagen existe
    public function getPhotoExistsAttribute()
    {
        return $this->photo_path ? Storage::exists($this->photo_path) : false;
    }
}
