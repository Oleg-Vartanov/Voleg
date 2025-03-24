<h3>About:</h3>

Welcome to my site!<br />
Currently there is only a Football Predictions game.
Maybe there will be more to come, who knows. So stay tuned ;)<br />
Check it out at [voleg.me](http://www.voleg.me)

<h3>Deploy project locally:</h3>

- Open root directory.
- Setup environment variables.
  - Create `.env.local` from `.env` and setup variables there as you like.
  - For /backend default `.env` will work. But make your own `.env.local` to rewrite some variables.
  - For /frontend make sure `VITE_API_BASE_URL` variable is setup correctly. You can overwrite it in `.env.local`.
- Run `make init` command.

*See other commands in Makefile.

<h3>Default Resources:</h3>

- API Documentation (OpenAPI Specification)
  - UI - [localhost:8010/api/doc](http://localhost:8010/api/doc)
  - JSON - [localhost:8010/api/doc.json](http://localhost:8010/api/doc.json)

- Frontend
  - Build - [localhost](http://localhost)
  - Run `make watch` for dev build - [localhost:8011](http://localhost:8011)
- Backend
  - API - [localhost:8010](http://localhost:8010)
- Dev mail catcher
  - [localhost:1080](http://localhost:1080)
    - Run mail consumer `make mailer`
