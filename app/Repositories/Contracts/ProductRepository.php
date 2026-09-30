<?php

namespace App\Repositories\Contracts;

use App\DTO\ProductDto;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepository
{
    /**
     * Restituisce una pagina di prodotti, dai più recenti. A parità di data di creazione
     * ordina per id, così le pagine restano stabili.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;

    /**
     * Cerca il prodotto per id; null se non esiste o è stato cancellato.
     */
    public function findById(string $productId): ?Product;

    /**
     * Salva un nuovo prodotto con i dati del DTO.
     */
    public function create(ProductDto $dto): Product;

    /**
     * Scrive i dati del DTO sul prodotto e restituisce la stessa istanza, già aggiornata.
     */
    public function update(Product $product, ProductDto $dto): Product;

    /**
     * Fa la soft delete del prodotto.
     */
    public function delete(Product $product): void;
}
