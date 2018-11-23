# Running tests in IO

## Create .env

Create .env file in plugin IO with the following content:

```
TEST_SUITE_DIR=/var/www/plenty/beta/pl/packages/plentymarkets/plugin-test-suite/
WORKSPACE=/var/www/plenty/beta/
```

## Setup testing DB

### Migrate

- General: `parmesan migrate:refresh --database=mysql-test --env=testing`
- Plugins: `parmesan plugin:install --database=mysql-test --env=testing`

### Seed

`parmesan db:seed --database=mysql-test --env=testing`

## How to execute phpunit

- Navigate to `/var/www/m77/master.plentymarkets.com/plugins/inbox/plugins/IO` in the vagrant vm.
- Run `phpunit`
- You may add filter to run specific tests or test classes e.g. `phpunit --filter=MyTestClass` or `phpunit --filter=it_runs_my_test_method`.


# Write tests

<!-- TODO -->