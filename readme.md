# Install

- clone repo
- `composer install`
- copy .env.example to .env
- configure .env
- `php bin/console doctrine:database:create`
- `php bin/console doctrine:migrations:migrate`
- `php bin/console doctrine:fixtures:load`

# Docker

not tested, maybe works
