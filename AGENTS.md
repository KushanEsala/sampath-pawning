# Project Database Rule

- Never run Laravel database migrations in this project, including `php artisan migrate` and all of its variants.
- Preserve the existing MySQL database and schema. Only import or modify database data when the user explicitly requests it.
