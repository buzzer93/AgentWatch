#syntax=docker/dockerfile:1

# =========================================================================
#  Image FrankenPHP pour AgentWatch (Symfony 8 / PHP 8.4)
#  Multi-stage : une base commune, puis une cible "dev" et une cible "prod".
# =========================================================================

# -------------------------------------------------------------------------
#  Étape commune : tout ce qui est partagé entre dev et prod
# -------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4 AS frankenphp_base

WORKDIR /app

# Paquets système utilisés par Composer / Symfony / le healthcheck.
RUN apt-get update && apt-get install -y --no-install-recommends \
		acl \
		curl \
		file \
		gettext \
		git \
	&& rm -rf /var/lib/apt/lists/*

# Extensions PHP. "install-php-extensions" est fourni par l'image FrankenPHP.
# pdo_pgsql est AJOUTÉ ici car ton app utilise PostgreSQL via Doctrine.
RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
		pdo_pgsql \
	;

# Permet à Composer de tourner en root dans le conteneur.
ENV COMPOSER_ALLOW_SUPERUSER=1

# FrankenPHP chargera tous les .ini déposés dans ce dossier.
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY --link frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

# Caddy expose des métriques sur le port 2019 : on s'en sert pour le healthcheck.
HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile" ]

# -------------------------------------------------------------------------
#  Étape DEV : Xdebug + config PHP de développement + code monté en volume
# -------------------------------------------------------------------------
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev
ENV XDEBUG_MODE=off

# php.ini "development" = messages d'erreur verbeux, pratique en local.
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
	install-php-extensions xdebug \
	;

COPY --link frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

# En dev, le code est monté via un volume (voir compose.override.yaml),
# donc on n'a rien à copier ici.

# -------------------------------------------------------------------------
#  Étape PROD : code figé dans l'image, dépendances optimisées, OPcache strict
# -------------------------------------------------------------------------
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod

# php.ini "production" = pas de fuite d'infos, perfs optimisées.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/

# 1) On installe d'abord SEULEMENT les dépendances (cache Docker efficace :
#    tant que composer.json/lock ne changent pas, cette couche est réutilisée).
COPY --link composer.json composer.lock symfony.lock ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# 2) Puis on copie le reste du code applicatif.
COPY --link . ./

# 3) Et on finalise (autoload optimisé, .env compilé, scripts Symfony).
RUN set -eux; \
	rm -Rf frankenphp/; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer dump-env prod; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;
