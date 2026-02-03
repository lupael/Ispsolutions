<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

/**
 * Admin panel controller alias.
 *
 * Routes in web.php reference AdminController for admin panel actions.
 * This class delegates to ISPController so all admin routes resolve correctly.
 */
class AdminController extends ISPController
{
}
