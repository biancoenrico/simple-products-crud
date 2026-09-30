# MSP Prodotto

Microservizio operativo della piattaforma WEB-ATTIVO per l'anagrafica dei prodotti: un CRUD REST sotto `/api/v1/products`, protetto dal JWT RS256 che firma il gateway (AG), con il documento OpenAPI 3.0 che AG legge per registrarlo.

Laravel 13 su PHP 8.3, struttura Controller → Service → Repository con dependency injection.

## Provare con Docker

Basta Docker e un solo comando. L'immagine è pubblica, e al primo avvio `docker run` la scarica da sé senza chiedere credenziali:

```bash
docker run --rm -p 8000:80 ghcr.io/<owner>/msp-prodotto
```

Docker non rende raggiungibile un container dall'host finché non glielo si chiede, e `-p 8000:80` porta la porta 80 del container sulla 8000 del computer. Se la 8000 è già occupata se ne sceglie un'altra, per esempio `-p 9000:80`, e negli indirizzi qui sotto si usa quella. Il container si ferma con `Ctrl+C`, e `--rm` lo cancella all'uscita.

La pagina di prova è su `http://localhost:8000/demo/`, già autenticata, e chiama le API vere per la lista paginata, la creazione, il dettaglio, la modifica e la cancellazione. Gli errori dell'API compaiono sulla pagina, quelli di validazione sotto il campo e gli altri in un avviso in cima. Il documento OpenAPI sta su `http://localhost:8000/docs/api-docs.json`.

All'avvio il container prepara da solo `.env`, chiave dell'app e database SQLite, genera una coppia di chiavi RSA e firma un token valido un anno per la pagina. L'immagine non usa volumi, per cui i dati durano quanto il container e uno nuovo riparte con database vuoto e chiavi nuove.

## Avvio

Servono PHP 8.3 con le estensioni `openssl` e `pdo_sqlite`, e Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Il servizio risponde su `http://localhost:8000`. In sviluppo il database è SQLite; per usarne un altro si cambiano le variabili `DB_*` nel `.env`.

### Con Docker compose

```bash
composer install
docker compose up
```

Il compose costruisce l'immagine dal `Dockerfile` e monta il progetto in `/app`, con il `vendor` dell'host: le modifiche al codice e alla pagina di prova si vedono senza rifare la build. Il servizio risponde su `http://localhost:8000`, la pagina di prova su `/demo/`. Il setup d'avvio crea `.env`, database e chiavi solo se mancano e lascia com'è ciò che trova. A ogni avvio rigenera soltanto il token della pagina, in `public/demo/config.js`, che è escluso dal repository.

## La chiave pubblica di AG

AG firma i token con la sua chiave privata; il servizio li verifica con la chiave pubblica corrispondente, che va copiata dall'istanza AG in `storage/oauth-public.key`. Il file è escluso dal repository. Per tenerlo altrove si imposta `JWT_PUBLIC_KEY_PATH` nel `.env`.

Se la chiave manca, una richiesta ai prodotti con un token risponde `500`.

Per provare il servizio senza un AG si genera una coppia di chiavi e si firma un token come farebbe AG, con i dati dell'utente in base64 nel claim `data`:

```bash
openssl genrsa -out /tmp/ag-private.key 2048
openssl rsa -in /tmp/ag-private.key -pubout -out storage/oauth-public.key

TOKEN=$(php -r 'require "vendor/autoload.php"; echo Firebase\JWT\JWT::encode(["exp" => time() + 3600, "data" => base64_encode("{}")], file_get_contents("/tmp/ag-private.key"), "RS256");')

curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/v1/products
```

## Rotte

| Metodo | Percorso | Autenticazione | Risposte |
|---|---|---|---|
| `GET` | `/api/health` | no | `200 {"status":"ok"}` |
| `GET` | `/docs/api-docs.json` | no | `200` documento OpenAPI 3.0 |
| `GET` | `/api/v1/products` | JWT | `200` lista paginata · `422` |
| `POST` | `/api/v1/products` | JWT | `201` · `422` |
| `GET` | `/api/v1/products/{productId}` | JWT | `200` · `404` |
| `PUT` | `/api/v1/products/{productId}` | JWT | `200` · `404` · `422` |
| `DELETE` | `/api/v1/products/{productId}` | JWT | `204` · `404` |

Un prodotto ha `id` (UUID), `name` (obbligatorio, massimo 255 caratteri), `description` (facoltativa) e `status` (`active` o `inactive`, `active` se non indicato). La lista accetta `per-page` da 1 a 100, 15 se non indicato, ed è ordinata dal prodotto creato più di recente. La cancellazione è logica: il prodotto cancellato non compare più e risponde `404`.

Le rotte dei prodotti restituiscono i dati sotto `data`. Sotto `/api` gli errori sono un JSON `{"message": "..."}` anche senza header `Accept`, e i `422` aggiungono `errors` per campo. Senza token, o con un token scaduto, firmato da un'altra chiave o senza un claim `data` valido, la risposta è `401`; un `productId` che non è un UUID risponde `404` prima ancora del controllo del token.

## Documento OpenAPI

Il documento si genera dagli attributi PHP 8 sui controller, sulla resource e sulla request:

```bash
php artisan l5-swagger:generate
```

Con `L5_SWAGGER_GENERATE_ALWAYS=true`, come nel `.env.example`, si rigenera a ogni richiesta a `/docs/api-docs.json`. In produzione il flag va lasciato spento e il comando si lancia al deploy.

## Test

```bash
php artisan test
```

I test passano tutti dall'API HTTP, su SQLite in memoria. La suite genera una coppia RSA a ogni esecuzione e firma da sé i propri token, quindi non serve nessuna chiave.

## Cosa è stato lasciato fuori

Il perimetro concordato è un'anagrafica di prodotti con nome, descrizione e stato. Restano fuori:

- i permessi: `x-permissions` nel documento, il filtro dei dati per scope, le colonne di organizzazione, account e utente, gli header `permissions` e `source` di AG e la verifica di `X-BODY-SIGNATURE`. Il middleware salva comunque il contenuto del token in `config('user_data')`, per un futuro controllo dei permessi;
- le librerie WA-common e wa-PermissionChecker;
- il service locale, RabbitMQ e gli eventi;
- i filtri di ricerca sulla lista, `PATCH`, `x-extra_loads`, `x-notification_types` e le notifiche;
- la registrazione in AG e il deploy;
- la traduzione dei messaggi di validazione, che restano quelli inglesi di Laravel.
