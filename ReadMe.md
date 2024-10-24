<h3>About:</h3>
Em, it's just my personal site. Nothing to see here for now ;)<br />

<h3>Deploy project locally:</h3>
- Open root directory.
- Create `.env.local` from `.env` and setup variables there.
- Run `make init` command.

<h3>Resources:</h3>
- Server http://localhot:8010
- Client build http://localhot:8011
  - Run `make watch` dev http://localhost:8012
- Mail catcher http://localhot:1080
  - Run mail consumer `make mailer` 

<h3>API Documentation (OpenAPI Specification):</h3>

- http://localhost:8010/api/doc - UI 
- http://localhost:8010/api/doc.json - JSON
