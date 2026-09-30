<?php

namespace App\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Stadio della pipeline che filtra una lista in base a un parametro della query string.
 * Se il parametro è assente o vuoto passa la query intatta al filtro successivo.
 */
abstract class BaseFilter
{
    protected Request $request;

    protected string $searchName;

    public function __construct()
    {
        $this->request = request();
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @param  Closure(Builder<covariant \Illuminate\Database\Eloquent\Model>): mixed  $next
     * @return mixed
     */
    public function handle(Builder $query, Closure $next): mixed
    {
        if ($this->request->filled($this->searchName)) {
            $query = $this->apply($query);
        }

        return $next($query);
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<covariant \Illuminate\Database\Eloquent\Model>
     */
    protected function apply(Builder $query): Builder
    {
        return $query->where($this->searchName, $this->request->query($this->searchName));
    }
}
