# MSP Prodotto

Microservizio REST per l'anagrafica dei prodotti, scritto in Laravel 13 su PHP 8.3. Espone un CRUD sotto `/api/v1/products` protetto da JWT RS256, con documentazione OpenAPI 3.0 e una pagina web di prova.

## Avvio rapido

```bash
docker run --rm -p 8000:80 enricobianco/simple-products-crud
```

Al primo avvio l'immagine si scarica da Docker Hub, senza bisogno di account. La pagina di prova, già autenticata, è su `http://localhost:8000/demo/`. Il documento OpenAPI si legge su `http://localhost:8000/docs/api-docs.json`.

## Sviluppo locale

```bash
composer install
docker compose up
```

Il compose monta il progetto nel container, quindi le modifiche al codice si vedono senza rifare la build. Il servizio risponde su `http://localhost:8000`, con la pagina di prova su `/demo/` e il documento OpenAPI su `/docs/api-docs.json`.

## Test

```bash
docker compose exec msp-product php artisan test
```

Con PHP 8.3 installato sulla macchina basta `php artisan test`.
