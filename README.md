# hemaScorecard
HEMA Scorecard - Tournament Management Software

Software to run and manage tournaments.

Developed by Sean Franklin
A HEMA Alliance supported project

## Running in Docker
Install [Docker](https://docs.docker.com/install/) and [Docker Compose](https://docs.docker.com/compose/install/).

After that simply run
```bash
$ docker compose up
```
in the source root directory. The application will be avaliable on http://localhost:8000.
The PHP Docker image contains Xdebug and this repository contains a sample VS Code config in `.vscode` which allows the
user to debug application via the "Listen for XDebug" command.

### Data persistence
The `docker-compose.yml` file is set up so that the `./initdb` directory is mounted at `/docker-entrypoint-initdb.d`
(database initialization scripts) and
the `./data` directory is mounted at `/var/lib/mysql` (the database itself). The database's data is thus persisted in
`./data`, and the directory will be created by Docker if it does not exist.

When `./data` is not present/is empty (i.e. when you first run the application) the database container will run the
scripts in `./initdb` to initialize the database. Further runs of the application will not re-run the database
initialization scripts unless you delete `./data`.

### Troubleshooting
In case of issues with running the `web` container (`xdebug install failed`) issue

```bash
$ docker compose down
$ docker compose rm -fsv
```

If there is a need to get inside a running container simply run

```bash
$ docker compose exec db /bin/bash
```

to get into `db` container, and

```bash
$ docker compose exec web /bin/bash
```

to get into the `web` container, respectively.
