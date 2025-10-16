<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property array<string>|null $titles_before
 * @property array<string>|null $titles_after
 * @property string $prosight_id
 * @property string $email
 * @property string|null $phone
 * @property string $gender
 * @property string|null $marital_status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string $display_name
 */
class Salesman extends Model
{
    /** @use HasFactory<\Database\Factories\SalesmanFactory> */
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** @var list<string> */
    protected $fillable = [
        'first_name',
        'last_name',
        'titles_before',
        'titles_after',
        'prosight_id',
        'email',
        'phone',
        'gender',
        'marital_status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'titles_before' => 'array',
        'titles_after' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return Attribute<string, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $titlesBefore = $this->titles_before ? implode(' ', $this->titles_before) : '';
                $titlesAfter = $this->titles_after ? implode(' ', $this->titles_after) : '';

                $name = trim("{$this->first_name} {$this->last_name}");

                return trim("{$titlesBefore} {$name} {$titlesAfter}");
            }
        );
    }
}
