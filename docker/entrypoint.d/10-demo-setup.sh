# A ogni avvio del container prepara ambiente, database, una coppia di chiavi al posto di quella
# di AG e il token che usa la pagina /demo/, saltando ciò che esiste già.
# L'immagine base lo carica con `source`, da root, prima di supervisord: i passi girano in un
# subshell perché `set -e` non resti attivo nel suo entrypoint, e un errore ferma il container.
(
    set -e
    cd /app

    if [ ! -f .env ]; then
        cp .env.example .env
        php artisan key:generate --force --no-interaction
    fi

    touch database/database.sqlite
    php artisan migrate --force --no-interaction

    if [ ! -f storage/oauth-public.key ]; then
        openssl genrsa -out storage/demo-private.key 2048
        openssl rsa -in storage/demo-private.key -pubout -out storage/oauth-public.key
    fi

    mkdir -p public/demo

    # Se la chiave pubblica viene da un vero AG manca la privata per firmare, e la pagina lo segnala
    if [ -f storage/demo-private.key ]; then
        php -r '
            require "vendor/autoload.php";
            $token = Firebase\JWT\JWT::encode(
                ["exp" => time() + 365 * 24 * 3600, "data" => base64_encode("{}")],
                file_get_contents("storage/demo-private.key"),
                "RS256",
            );
            echo "window.DEMO_TOKEN = ".json_encode($token).";\n";
        ' > public/demo/config.js
    else
        echo "Manca storage/demo-private.key: nessun token per /demo/"
        rm -f public/demo/config.js
    fi

    chown -R application:application storage bootstrap/cache database public/demo
) || exit 1
