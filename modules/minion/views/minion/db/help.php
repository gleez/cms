Database migrations is a cli tool for performing db migrations

Usage 

    db:create           creates the database
    db:drop             drops the database
    db:migrate          runs migrations that have not run yet
    db:migrate:up       runs one specific migration
    db:migrate:down     rolls back one specific migration
    db:migrate:status   shows current migration status
    db:migrate:rollback rolls back the last migration
    db:migrate:reset    runs db:drop db:create db:migrate
    db:migrate:redo     runs (db:migrate:down db:migrate:up) or 
                        (db:migrate:rollback db:migrate:migrate) 
                        depending on the specified migration
