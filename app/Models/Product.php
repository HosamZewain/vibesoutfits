<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['active' => 'boolean', 'featured' => 'boolean', 'ready_enabled' => 'boolean', 'custom_enabled' => 'boolean'];

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function imageUrl(): string
    {
        if (! $this->image_path) {
            return asset('assets/img/placeholder.svg');
        }

        return str_starts_with($this->image_path, 'assets/')
            ? asset($this->image_path)
            : route('product.image', $this);
    }

    public function catalogPhotos(): array
    {
        return config('launch_catalog.'.$this->slug.'.photos', []);
    }
}
