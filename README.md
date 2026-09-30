# MSP Prodotto

Microservizio operativo della piattaforma WEB-ATTIVO per l'anagrafica dei prodotti. Espone un CRUD REST sotto `/api/v1/products`, protetto dal JWT RS256 firmato dal gateway (AG), e un documento OpenAPI 3.0 che AG legge per registrarlo.

Laravel 13 su PHP 8.3, struttura Controller → Service → Repository con dependency injection.

## Provare con Docker

Serve solo Docker. L'immagine è pubblica: al primo avvio `docker run` la scarica senza chiedere credenziali:

```bash
docker run --rm -p 8000:80 ghcr.io/<owner>/msp-prodotto
```

`-p 8000:80` collega la porta 80 del container alla 8000 del computer; senza, il container non è raggiungibile dall'host. Se la 8000 è occupata se ne usa un'altra, per esempio `-p 9000:80`, e la si sostituisce negli indirizzi qui sotto. `Ctrl+C` ferma il container e `--rm` lo cancella all'uscita.

Su `http://localhost:8000/demo/` c'è una pagina di prova già autenticata che chiama le API vere. I filtri per nome e stato stanno in alto, il pulsante per creare un prodotto sopra la tabella, e da ogni riga si apre il dettaglio, la modifica o la cancellazione. Gli errori di validazione compaiono sotto il campo, gli altri in un avviso in basso a destra. Il documento OpenAPI è su `http://localhost:8000/docs/api-docs.json`.

All'avvio il container crea `.env`, chiave dell'app e database SQLite, poi genera una coppia di chiavi RSA e firma per la pagina un token valido un anno. Non ci sono volumi: i dati durano quanto il container, e un container nuovo parte con database vuoto e chiavi nuove.

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

Il compose costruisce l'immagine dal `Dockerfile` e monta il progetto in `/app`, `vendor` dell'host compreso, quindi le modifiche al codice e alla pagina di prova si vedono senza rifare la build. Il servizio risponde su `http://localhost:8000` e la pagina di prova su `/demo/`.

All'avvio `.env`, database e chiavi si creano solo se mancano; quelli che ci sono restano come sono. Si rigenera ogni volta soltanto il token della pagina, in `public/demo/config.js`, che è escluso dal repository.

## La chiave pubblica di AG

AG firma i token con la sua chiave privata e il servizio li verifica con la chiave pubblica corrispondente. Quella va copiata dall'istanza AG in `storage/oauth-public.key`, un file escluso dal repository; per tenerla altrove si imposta `JWT_PUBLIC_KEY_PATH` nel `.env`.

Se la chiave manca, una richiesta ai prodotti con un token risponde `500`.

Senza un AG si può generare una coppia di chiavi e firmare un token come lo firmerebbe AG, con i dati dell'utente in base64 nel claim `data`:

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

Un prodotto ha `id` (UUID), `name` (obbligatorio, massimo 255 caratteri), `description` (facoltativa) e `status` (`active` o `inactive`, `active` se non indicato). La lista è ordinata dal prodotto creato più di recente e accetta `per-page` da 1 a 100 (15 se non indicato). Due parametri la filtrano: `name` cerca il testo dentro il nome e `status` tiene solo i prodotti in quello stato. In `name` maiuscole e minuscole si equivalgono, ma solo per le lettere senza accento. I link di paginazione conservano i filtri. La cancellazione è logica: un prodotto cancellato sparisce dalla lista e risponde `404`.

Le rotte dei prodotti restituiscono i dati sotto `data`. Sotto `/api` gli errori sono un JSON `{"message": "..."}` anche senza header `Accept`, e i `422` aggiungono `errors` per campo. La risposta è `401` se il token manca, è scaduto, è firmato da un'altra chiave o non ha un claim `data` valido. Un `productId` che non è un UUID risponde `404` prima del controllo del token.

## Documento OpenAPI

Il documento si genera dagli attributi PHP 8 sui controller, sulla resource e sulla request:

```bash
php artisan l5-swagger:generate
```

Con `L5_SWAGGER_GENERATE_ALWAYS=true`, il valore del `.env.example`, si rigenera a ogni richiesta a `/docs/api-docs.json`. In produzione il flag resta spento e il comando si lancia al deploy.

## Test

```bash
php artisan test
```

Tutti i test passano dall'API HTTP, su SQLite in memoria. A ogni esecuzione la suite genera una coppia RSA e firma i propri token, quindi non serve nessuna chiave.

## Cosa è stato lasciato fuori

Il perimetro concordato è un'anagrafica di prodotti con nome, descrizione e stato. Restano fuori:

- i permessi: `x-permissions` nel documento, il filtro dei dati per scope, le colonne di organizzazione, account e utente, gli header `permissions` e `source` di AG e la verifica di `X-BODY-SIGNATURE`. Il middleware salva comunque il contenuto del token in `config('user_data')`, in vista di un futuro controllo dei permessi;
- le librerie WA-common e wa-PermissionChecker;
- il service locale, RabbitMQ e gli eventi;
- `PATCH`, `x-extra_loads`, `x-notification_types` e le notifiche;
- la registrazione in AG e il deploy.
