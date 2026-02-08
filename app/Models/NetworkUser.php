<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Backwards-compatible NetworkUser shim.
 *
 * Historically there was a separate `network_users` table and model. The
 * project has unified customers into the `users` table (see Customer model).
 * To avoid widespread refactors across services and jobs, provide a shim
 * model that maps to `users` and exposes expected legacy relationships.
 */
class NetworkUser extends Customer
{
    /**
     * The table backing this model (unified users table).
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * If code expects a link to an owning user (e.g., account manager),
     * provide a `user` relation that references the User who created/owns
     * this network user (stored in `user_id` when present).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
