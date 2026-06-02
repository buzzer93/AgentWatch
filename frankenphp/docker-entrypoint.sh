#!/bin/sh
set -e

# Ce script s'exécute au démarrage du conteneur, avant FrankenPHP.
# Il prépare l'application puis lance la commande passée (frankenphp, php, bin/console).

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	# En dev, le code est monté en volume : si vendor/ est vide, on installe.
	if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	# Affiche la version de Symfony (et révèle une éventuelle erreur d'init).
	php bin/console -V

	if grep -q ^DATABASE_URL= .env; then
		echo 'Attente de la base de données...'
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
			if [ $? -eq 255 ]; then
				# Code 255 = erreur irrécupérable, inutile de réessayer.
				ATTEMPTS_LEFT_TO_REACH_DATABASE=0
				break
			fi
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "Toujours en attente de la base... $ATTEMPTS_LEFT_TO_REACH_DATABASE tentatives restantes."
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo 'Base de données injoignable :'
			echo "$DATABASE_ERROR"
			exit 1
		fi
		echo 'Base de données prête.'

		# Applique automatiquement les migrations Doctrine en attente.
		if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
			php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
		fi
	fi

	echo 'Application PHP prête !'
fi

exec docker-php-entrypoint "$@"
