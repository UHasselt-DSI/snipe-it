<?php

namespace App\Models;

use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Keys.
 */
class Key extends SnipeModel
{
    use HasFactory;
    use Loggable;
    use Presentable;
    use Searchable;
    use SoftDeletes;
    use ValidatingTrait;

    protected $table = 'keys';

    protected $presenter = \App\Presenters\KeyPresenter::class;

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'key_id',
        'notes',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'location' => ['name'],
    ];

    /**
     * Key validation rules
     */
    public $rules = [
        'key_id' => 'required|string|max:255|unique:keys,key_id',
        'location_id' => 'exists:locations,id|nullable',
        'notes' => 'nullable|string',
        'assigned_to' => 'exists:users,id|nullable',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key_id',
        'location_id',
        'assigned_to',
        'status',
        'notes',
        'created_by',
    ];

    /**
     * Whether the model should inject its identifier to the unique
     * validation rules before attempting validation.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

    /**
     * Establishes the key -> location relationship
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Establishes the key -> assigned user relationship
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->withTrashed();
    }

    /**
     * Establishes the key -> created by relationship
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Returns the action logs associated with the key
     */
    public function assetlog()
    {
        return $this->hasMany(Actionlog::class, 'item_id')
            ->where('item_type', self::class)
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Scope to only available keys
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to only checked out keys
     */
    public function scopeCheckedOut($query)
    {
        return $query->where('status', 'checked_out');
    }

    /**
     * Check if the key is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if the key is checked out
     */
    public function isCheckedOut(): bool
    {
        return $this->status === 'checked_out';
    }

    /**
     * Determine if the key is available for checkout.
     * Used by SnipeModel::showCheckoutButton().
     */
    public function availableForCheckout(): bool
    {
        return $this->isAvailable();
    }

    /**
     * Determine if the key is available for checkin.
     * Used by SnipeModel::showCheckinButton().
     */
    public function availableForCheckIn(): bool
    {
        return $this->isCheckedOut();
    }

    /**
     * Get the display name for the key.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->key_id ?? '';
    }

    /**
     * Check if the key can be deleted (only if available/not checked out).
     */
    public function isDeletable(): bool
    {
        return $this->isAvailable();
    }

    /**
     * Query builder scope to order on location
     */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations', 'keys.location_id', '=', 'locations.id')
            ->orderBy('locations.name', $order);
    }

    /**
     * Query builder scope to order on assigned user
     */
    public function scopeOrderAssignedTo($query, $order)
    {
        return $query->leftJoin('users', 'keys.assigned_to', '=', 'users.id')
            ->orderBy('users.first_name', $order)
            ->orderBy('users.last_name', $order);
    }

    /**
     * Additional text search for the assigned user, since assignedTo uses a
     * BelongsTo (not MorphTo) relation — the Searchable trait's default
     * assignedTo handling calls whereHasMorph() which fails on BelongsTo.
     */
    public function advancedTextSearch(Builder $query, array $terms)
    {
        foreach ($terms as $term) {
            $query->orWhereHas('assignedTo', function ($q) use ($term) {
                $q->where('first_name', 'LIKE', "%{$term}%")
                  ->orWhere('last_name', 'LIKE', "%{$term}%")
                  ->orWhere('username', 'LIKE', "%{$term}%")
                  ->orWhere('display_name', 'LIKE', "%{$term}%");
            });
        }

        return $query;
    }
}
