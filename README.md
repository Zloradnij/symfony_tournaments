# symfony_tournaments
git clone git@github.com:Zloradnij/symfony_tournaments.git \
cd symfony_tournaments

####.env in the project, because the project is a test.

docker-compose build \
docker-compose up -d \
docker exec -it tournaments-php bash

cd /tournaments \
composer install \
php bin/console make:migration \
php bin/console doctrine:migrations:migrate \
php bin/console asset-map:compile

add teams to http://localhost:8093/teams/ \
add tournaments to http://localhost:8093/tournaments/ 
