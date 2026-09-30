<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * Tiene solo i record il cui nome contiene il testo di `?name=`.
 * `%` e `_` scritti dall'utente valgono come caratteri e non come jolly.
 */
class ByName extends BaseFilter
{
    protected string $searchName = 'name';

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function apply(Builder $query): Builder
    {
        // SQLite non ha un carattere di escape di default in LIKE, e `\` in MySQL va raddoppiato: `!` vale per entrambi
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], (string) $this->request->query($this->searchName));

        return $query->whereRaw("{$this->searchName} like ? escape '!'", ["%{$escaped}%"]);
    }
}
