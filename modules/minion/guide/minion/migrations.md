Migrations are a convenient way for you to alter your database in a structured and organized manner. You could edit fragments of SQL by hand but you would then be responsible for telling other developers that they need to go and run them. You'd also have to keep track of which changes need to be run against the production machines next time you deploy.

Migrations module tracks which migrations have already been run so all you have to do is update your source and run ./minion db:migrate. Migrations module will work out which migrations should be run. 

Once a Migration has been released (i.e. has possibly been run on a production system) it should not be modified.
Instead, new Migrations must be created.

## Metadata table

The first time a Migration is run, a special table (by default called `migrations`) is created
in which to store information about which Migrations have been applied.

The table name can be customised with the `migration.table` [configuration value](../gleez/files/config).

## File Name Conventions

The file name format is mandatory and it should exist either in application/migrations folder or your module migrations (modules/foo/migrations). The name syntax should be like UTC date with some desciption and UP/DOWN direction and ends with sql extention. The seperator should be underscore and direction should be seperated by dot.

A single migration should consist two files one UP and another DOWN with same name (important).

~~~
20140121185122_hello_world.UP.sql
20140121185122_hello_world.DOWN.sql
~~~

# Command line tools

Minion provides a set of cli commands to work with migrations which boils down to running certain sets of migrations. The very first migration related command you use will probably be db:migrate. In its most basic form it just runs the up method for all the migrations that have not yet been run. If there are no such migrations it exits.

If you specify a target version, Active Record will run the required migrations (up or down) until it has reached the specified version. The version is the numerical prefix on the migration's filename. For example to migrate to version 2008090612 run

	./minion db:migrate --version=2008090612

If this is greater than the current version (i.e. it is migrating upwards) this will run the up method on all migrations up to and including 2008090612, if migrating downwards this will run the down method on all the migrations down to, but not including, 2008090612.

## Rolling Back
A common task is to rollback the last migration, for example if you made a mistake in it and wish to correct it. Rather than tracking down the version number associated with the previous migration you can run

	./minion db:rollback

This will run the down method from the latest migration. If you need to undo several migrations you can provide a --step option:

	./minion db:rollback --step=3
will run the down method from the last 3 migrations.

The db:migrate:redo task is a shortcut for doing a rollback and then migrating back up again. As with the db:rollback task you can use the --step option if you need to go more than one version back, for example

	./minion db:migrate:redo --step=3

Neither of these commands do anything you could not do with db:migrate, they are simply more convenient since you do not need to explicitly specify the version to migrate to.

## Being Specific
If you need to run a specific migration up or down the db:migrate:up and db:migrate:down commands will do that. Just specify the appropriate version and the corresponding migration will have its up or down method invoked, for example

	./minion db:migrate:up --version=2008090612
will run the up method from the 2008090612 migration. These commands check whether the migration has already run, so for example db:migrate:up --version=2008090612 will do nothing if Migrations module believes that --version=2008090612 has already been run.



# Footnotes 
A lot of this text has been taken from http://guides.rubyonrails.org/migrations.html as I've tried to mimic their functionality and interface as much as I could.
	