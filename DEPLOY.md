Déploiement rapide (Docker)

Local (Docker Compose)
- Pré-requis: Docker Desktop
- Variables: ajuste `.env.local` ou passe DATABASE_URL Postgres
- Lancer:
  - `docker compose up --build`
- L’application écoute sur http://localhost:8080

Base de données
- Le compose fournit Postgres. Par défaut: app/!ChangeMe!@database:5432/app
- L’entrypoint attend la DB et applique les migrations automatiquement.

Production (idées)
- Koyeb: créer un service Docker depuis ce repo, ajouter un add-on Postgres (Neon/Supabase externe recommandé). Envoyer `DATABASE_URL` et `APP_ENV=prod`.
- Fly.io: `fly launch` avec Dockerfile, configurer un Postgres (Neon/Supabase) et secrets `DATABASE_URL`.

Notes
- Le serveur démarré est le serveur PHP intégré pour simplicité. Pour du trafic réel, préférer Caddy/Nginx + PHP-FPM.
- SQLite n’est pas conseillé en prod (stockage éphémère). Utilise Postgres.