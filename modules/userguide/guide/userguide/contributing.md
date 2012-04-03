[!!]  When the docs get merged these images/links should be update

# Contributing

Kohana is community driven, and we rely on community contributions for the documentation.

## Guidelines

Documentation should use complete sentences, good grammar, and be as clear as possible.  Use lots of example code, but make sure the examples follow the Kohana conventions and style.

Try to commit often, with each commit only changing a file or two, rather than changing a ton of files and commiting it all at once.  This will make it easier to offer feedback and merge your changes.   Make sure your commit messages are clear and descriptive.  Bad: "Added docs",  Good: "Added initial draft of hello world tutorial",  Bad: "Fixed typos",  Good: "Fixed typos on the query builder page"

If you feel a menu needs to be rearranged or a module needs new pages, please open a [bug report](http://dev.kohanaframework.org/projects/userguide3/issues/new) to discuss it.

## Quick Method

To quickly point out something that needs improvement, report a [bug report](http://dev.kohanaframework.org/projects/userguide3/issues/new).

If you want to contribute some changes, you can do so right from your browser without even knowing git!

First create an account on [Github](https://github.com/signup/free).

You will need to fork the module for the area you want to improve.  For example, to improve the [ORM documentation](../orm) fork <http://github.com/bluehawk/orm>.  To improve the [Kohana documentation](../kohana), fork <http://github.com/bluehawk/core>, etc.  So, find the module you want to improve and click on the Fork button in the top right.

![Fork the module](contrib-github-fork.png)

The files that make the User Guide portion are found in `guide/<module>/`, and the API browser portion is made from the comments in the source code itself.  Navigate to one of the files you want to change and click the edit button in the top right of the file viewer.

![Click on edit to edit the file](contrib-github-edit.png)

Make the changes and add a **detailed commit message**.  Repeat this for as many files as you want to improve. (Note that you can't preview what the changes will look unless you actually test it locally.)

After you have made your changes, send a pull request so your improvements can be reviewed to be merged into the official documentation.

![Send a pull request](contrib-github-pull.png)

Once your pull request has been accepted, you can delete your repository if you want.  Your commit will have been copied to the official branch.

## If you know git

**Bluehawk's forks all have a `docs` branch.  Please do all work in that branch.**

To make pulling all the docs branches easier, the "docs" branch of [http://github.com/bluehawk/kohana](http://github.com/bluehawk/kohana) contains git submodule links to all the other "docs" branches, so you can clone that to easily get all the docs.  The main Kohana docs are in [http://github.com/bluehawk/core/tree/docs/guide/kohana/], and docs for each module are in the respective module in the guide folder. (Again, make sure you are in the `docs` branch.)

**Short version**: Fork bluehawk's fork of the module whose docs you wish to improve (e.g. `git://github.com/bluehawk/orm.git` or `git://github.com/bluehawk/core.git`), checkout the `docs` branch, make changes, and then send bluehawk a pull request.

**Long version:**  (This still assumes you at least know your way around git, especially how submodules work.)

 1. Fork the specific repo you want to contribute to on github. (For example go to http://github.com/bluehawk/core and click the fork button.)

 1. To make pulling the new userguide changes easier, I have created a branch of `kohana` called `docs` which contains git submodules of all the other doc branchs.  You can either manually add my remotes to your existing kohana repo, or create a new kohana install from mine by doing these commands:
	
		git clone git://github.com/bluehawk/kohana
		
		# Get the docs branch
		git checkout origin/docs
		
		# Fetch the system folder and all the modules
		git submodule init
		git submodule update

 1. Now go into the repo of the area of docs you want to contribute to and add your forked repo as a new remote, and push to it.
 
		cd system
		
		# make sure we are up to date with the docs branch
		git merge origin/docs
		(if this fails or you can't commit later type "git checkout -b docs" to create a local docs branch)
		
		# add your repository as a new remote
		git remote add <your name> git@github.com:<your name>/core.git
		
		# (make some changes to the docs)
		
		# now commit the changes and push to your repo
		git commit
		git push <your name> docs

 1. Send a pull request on github.