<?php

namespace App\Http\Requests;

/**
 * Update accepts the same payload as store. Kept as a distinct class so the
 * two can diverge (e.g. locking the sale date after month-end close) without
 * having to untangle a shared request first.
 */
class UpdateSaleRequest extends StoreSaleRequest {}
