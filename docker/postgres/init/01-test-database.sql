-- The test suite runs against PostgreSQL (see phpunit.xml) so that tests
-- exercise the same engine as production. It needs its own database, kept
-- separate from the dev one so RefreshDatabase cannot wipe local data.
--
-- Postgres only runs this on FIRST initialisation of the db_data volume. On an
-- existing volume, create it by hand:
--   docker exec portfolio-db-1 psql -U portfolio -d postgres \
--     -c "CREATE DATABASE portfolio_test OWNER portfolio;"
CREATE DATABASE portfolio_test OWNER portfolio;
