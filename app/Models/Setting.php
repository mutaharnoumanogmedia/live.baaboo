<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember("setting.{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): self
    {
        $value = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
        Cache::forget("setting.{$key}");
        return $setting;
    }

    /**
     * Get all settings as key => [value, type, group] for form.
     */
    public static function getAllKeys(): array
    {
        $out = [];
        foreach (static::all() as $row) {
            $out[$row->key] = [
                'value' => static::castValue($row->value, $row->type),
                'type' => $row->type,
                'group' => $row->group ?? 'general',
            ];
        }
        return $out;
    }

    protected static function castValue(?string $value, string $type)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => in_array(strtolower($value), ['1', 'true', 'yes'], true),
            default => $value,
        };
    }

    /**
     * Default keys used by the app settings screen.
     */
    public static function defaultKeys(): array
    {
        return [
            'default_quiz_timer' => ['default' => 10, 'type' => 'integer', 'group' => 'quiz'],
            'min_quiz_timer' => ['default' => 2, 'type' => 'integer', 'group' => 'quiz'],
            'max_quiz_timer' => ['default' => 120, 'type' => 'integer', 'group' => 'quiz'],
            'app_name' => ['default' => config('app.name'), 'type' => 'string', 'group' => 'general'],
            'support_email' => ['default' => '', 'type' => 'string', 'group' => 'general'],
            'maintenance_mode' => ['default' => false, 'type' => 'boolean', 'group' => 'general'],
            'chat_enabled' => ['default' => true, 'type' => 'boolean', 'group' => 'features'],
        ];
    }
}
