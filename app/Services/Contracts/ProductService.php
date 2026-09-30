<?php

namespace App\Services\Contracts;

use App\DTO\ProductDto;
use App\Exceptions\ProductException;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductService
{
    /**
     * Restituisce una pagina di prodotti, dai più recenti.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(int $perPage): LengthAwarePaginator;

    /**
     * Restituisce il prodotto con l'id dato.
     *
     * @throws ProductException se il prodotto non esiste o è stato cancellato
     */
    public function findById(string $productId): Product;

    /**
     * Crea un prodotto con i dati del DTO e lo restituisce.
     */
    public function create(ProductDto $dto): Product;

    /**
     * Sovrascrive nome, descrizione e stato del prodotto con quelli del DTO e lo restituisce aggiornato.
     *
     * @throws ProductException se il prodotto non esiste o è stato cancellato
     */
    public function updateById(string $productId, ProductDto $dto): Product;

    /**
     * Fa la soft delete del prodotto: la riga resta, con deleted_at valorizzato.
     *
     * @throws ProductException se il prodotto non esiste o è stato cancellato
     */
    public function deleteById(string $productId): void;
}
