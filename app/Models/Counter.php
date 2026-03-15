<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    protected $table = 'counters';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }

    public static function getValue(string $key, int $default = 0): int
    {
        $row = static::query()->find($key);
        return $row ? (int) $row->value : $default;
    }

    public static function incrementKey(string $key, int $by = 1): int
    {
        $row = static::query()->firstOrCreate(['key' => $key], ['value' => 0]);
        $row->increment('value', $by);

        // Rafraîchir la valeur en mémoire après increment()
        $row->refresh();
        return (int) $row->value;
    }
}

