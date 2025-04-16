#!/usr/bin/env bash


set -exo pipefail

[ -z "$CI_COMMIT_SHA" ] && CI_COMMIT_SHA=tests

COMPOSE_FILES=("-f" "devops/test/docker-compose.yml")
NAME_PREFIX="$CI_COMMIT_SHA"
BASE_PATH="$(
  cd "$(dirname "$0")/../../" > /dev/null 2>&1 || exit
  pwd -P
)"

docker_compose_cleanup() {
    docker-compose -p ${NAME_PREFIX} "${COMPOSE_FILES[@]}" logs lib
    docker-compose -p ${NAME_PREFIX} "${COMPOSE_FILES[@]}" down --rmi all --remove-orphans
}

trap docker_compose_cleanup EXIT
trap docker_compose_cleanup ERR

#start application
docker-compose -p ${NAME_PREFIX} "${COMPOSE_FILES[@]}" up -d --build --remove-orphans

docker-compose -p ${NAME_PREFIX} "${COMPOSE_FILES[@]}" exec -T lib php -d extension=pcov.so -d pcov.enabled=1 -d xdebug.mode=coverage ./vendor/bin/phpunit --coverage-text --coverage-html ./reports/coverage --colors=never --coverage-cobertura=./reports/coverage/cobertura.xml --coverage-clover=./reports/coverage/clover.xml --log-junit=./reports/coverage/junit.xml

docker cp lib:/var/www/lib/reports/. ./reports