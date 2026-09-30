<?php

namespace App\Filters;

/**
 * Tiene solo i record con lo stato indicato in `?status=`.
 */
class ByStatus extends BaseFilter
{
    protected string $searchName = 'status';
}
