# Projet technique (Prototype CRUD)

Avant de commencer le développement du projet principal, j'ai réalisé un projet technique servant de prototype : une application web de **gestion des tâches logicielles**, inspirée des workflows de développement, permettant de gérer les développeurs et de suivre les tâches qui leur sont attribuées.

La plateforme permet de créer et d'organiser les tâches, de les assigner aux développeurs et de suivre leur progression selon leur priorité, leur échéance et leur statut.

## Fonctionnalités Clés

- **Developer Management :** Gestion des développeurs.
- **Task Management :** Création et attribution des tâches aux développeurs.
- **Task Priorities and Deadlines :** Définition des priorités et des échéances des tâches.
- **Task Status Management :** Suivi des tâches selon leur statut (En attente, En cours, Terminée).
- **Task Tracking and Organization :** Suivi et organisation des tâches.

## Classes principales du modèle

- **Developer :** Représente un développeur pouvant se voir attribuer des tâches.
- **Task :** Représente une tâche avec sa priorité, son échéance et son statut.

## Relations principales

- **Developer ↔ Task :** Relation One-to-Many (un développeur peut se voir attribuer plusieurs tâches).