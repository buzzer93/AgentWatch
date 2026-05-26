# TODO.md — Roadmap RSS AI Watch

## Objectif

Construire progressivement une webapp Symfony connectée à n8n pour collecter, analyser, scorer et trier des flux RSS tech avec IA.

La priorité est de livrer rapidement une version utile, puis d'ajouter les briques avancées.

---

# Phase 0 — Préparation projet

## Décisions à prendre

- [ ] Choisir le nom final du projet.
- [ ] Choisir le domaine ou sous-domaine.
  - Exemple : `veille.nicolas-rodriguez.fr`
- [ ] Choisir la version PHP.
  - PHP 8.3 si Symfony 7.4.
  - PHP 8.4 si Symfony 8.
- [ ] Choisir le framework CSS.
  - Bootstrap pour aller vite.
  - Tailwind pour une UI plus moderne.
- [ ] Choisir le mode IA initial.
  - OpenAI uniquement.
  - Ollama uniquement.
  - Hybride.

## Fichiers initiaux

- [ ] Créer `README.md`.
- [ ] Créer `AGENT.md`.
- [ ] Créer `TODO.md`.
- [ ] Créer `.env.example`.
- [ ] Créer `docker-compose.yml`.
- [ ] Créer un dossier `docs/`.
- [ ] Créer un dossier `n8n/workflows/`.
- [ ] Créer un dossier `n8n/prompts/`.

---

# Phase 1 — Infrastructure Docker

## Docker Compose

Services à prévoir :

- [ ] Caddy.
- [ ] PHP/Symfony.
- [ ] PostgreSQL.
- [ ] n8n.
- [ ] Ollama, optionnel au début.

## Réseau

- [ ] Créer un réseau Docker commun.
- [ ] Vérifier que Symfony peut accéder à PostgreSQL.
- [ ] Vérifier que n8n peut accéder à PostgreSQL.
- [ ] Vérifier que Caddy peut exposer Symfony.
- [ ] Vérifier que Caddy peut exposer n8n si besoin.

## Variables d'environnement

- [ ] Configurer `DATABASE_URL`.
- [ ] Configurer les accès PostgreSQL.
- [ ] Configurer les variables n8n.
- [ ] Configurer le token interne API.
- [ ] Configurer les clés IA si cloud.
- [ ] Configurer les variables mail.

## Caddy

- [ ] Créer un Caddyfile.
- [ ] Exposer la webapp Symfony.
- [ ] Exposer n8n sur un sous-domaine protégé.
- [ ] Vérifier HTTPS.
- [ ] Vérifier les redirections HTTP vers HTTPS.

---

# Phase 2 — Création application Symfony

## Installation

- [ ] Créer le projet Symfony.
- [ ] Installer Doctrine ORM.
- [ ] Installer Maker Bundle en dev.
- [ ] Installer Twig.
- [ ] Installer les assets.
- [ ] Installer le framework CSS choisi.
- [ ] Configurer PostgreSQL.

## Entités

- [ ] Créer `RssSource`.
- [ ] Créer `RssItem`.
- [ ] Créer `AiAnalysis`.
- [ ] Créer `UserFeedback`.
- [ ] Créer `DailySelection`.
- [ ] Créer `DailySelectionItem`.

## Enums

- [ ] Créer `FeedbackType`.
- [ ] Créer `ArticleCategory`.
- [ ] Créer `SourceCategory`, optionnel.

## Repositories

- [ ] Ajouter une méthode pour récupérer les articles récents.
- [ ] Ajouter une méthode pour récupérer les articles non traités.
- [ ] Ajouter une méthode pour récupérer les meilleurs articles du jour.
- [ ] Ajouter une méthode pour récupérer les articles par catégorie.
- [ ] Ajouter une méthode pour récupérer les feedbacks positifs.

---

# Phase 3 — Gestion des sources RSS

## CRUD Sources

- [ ] Page liste des sources.
- [ ] Formulaire d'ajout.
- [ ] Formulaire d'édition.
- [ ] Bouton activer / désactiver.
- [ ] Champ priorité.
- [ ] Champ catégorie.
- [ ] Affichage du nombre d'articles importés.

## Import initial

- [ ] Préparer une première liste de 10 flux RSS.
- [ ] Tester l'import.
- [ ] Étendre progressivement jusqu'à 50 flux max.

---

# Phase 4 — API interne pour n8n

## Sécurité

- [ ] Créer un token API interne.
- [ ] Lire le token depuis `.env`.
- [ ] Vérifier le token dans les endpoints internes.

## Endpoints

- [ ] `POST /internal/rss-items`
  - Crée un article s'il n'existe pas.
  - Ignore les doublons par URL ou hash.

- [ ] `POST /internal/ai-analysis`
  - Ajoute ou met à jour l'analyse IA d'un article.

- [ ] `POST /internal/daily-selection`
  - Crée la sélection quotidienne.

- [ ] `GET /internal/rss-sources`
  - Retourne les sources actives pour n8n.

---

# Phase 5 — Workflow n8n collecte RSS

## Workflow Collecte

- [ ] Créer un workflow n8n `RSS Collect`.
- [ ] Déclenchement toutes les heures.
- [ ] Lire les sources actives.
- [ ] Pour chaque source, lire le flux RSS.
- [ ] Normaliser les données.
- [ ] Envoyer vers Symfony ou écrire directement dans PostgreSQL.
- [ ] Gérer les erreurs de flux indisponibles.
- [ ] Logger les imports.

## Déduplication

- [ ] Dédupliquer par URL.
- [ ] Dédupliquer par hash titre + source + date.
- [ ] Ne pas réimporter les anciens articles.

---

# Phase 6 — Analyse IA

## Prompt article-analysis

- [ ] Créer `n8n/prompts/article-analysis.prompt.md`.
- [ ] Définir le rôle de l'IA.
- [ ] Définir le profil de Nicolas.
- [ ] Définir les catégories.
- [ ] Définir les critères de scoring.
- [ ] Demander une sortie JSON stricte.

## Analyse article

- [ ] Workflow n8n `Article Analysis`.
- [ ] Récupérer les articles non traités.
- [ ] Envoyer titre + extrait + source à l'IA.
- [ ] Recevoir résumé + tags + scores.
- [ ] Stocker dans `ai_analysis`.
- [ ] Marquer l'article comme traité.

## Scores

- [ ] relevanceScore.
- [ ] businessScore.
- [ ] learningScore.
- [ ] contentScore.
- [ ] finalScore.
- [ ] reasoning.

---

# Phase 7 — Dashboard Symfony

## Page dashboard

- [ ] Créer `DashboardController`.
- [ ] Afficher le Top 5 du jour.
- [ ] Afficher le résumé global du jour.
- [ ] Afficher les boutons de feedback.
- [ ] Afficher les articles récents à fort score.

## UI Article Card

Chaque carte doit afficher :

- [ ] titre ;
- [ ] source ;
- [ ] date ;
- [ ] résumé IA ;
- [ ] tags ;
- [ ] score final ;
- [ ] raison de sélection ;
- [ ] bouton lire l'article ;
- [ ] boutons feedback.

## Liste articles

- [ ] Page `/articles`.
- [ ] Pagination.
- [ ] Filtre par catégorie.
- [ ] Filtre par source.
- [ ] Filtre par score.
- [ ] Filtre par feedback.
- [ ] Recherche texte simple.

## Détail article

- [ ] Page `/articles/{id}`.
- [ ] Afficher contenu brut disponible.
- [ ] Afficher analyse IA complète.
- [ ] Afficher feedbacks.
- [ ] Ajouter commentaires utilisateur.

---

# Phase 8 — Feedback utilisateur

## Boutons feedback

- [ ] Meilleur sujet du jour.
- [ ] Utile.
- [ ] Pas utile.
- [ ] Opportunité business.
- [ ] Idée de contenu.
- [ ] À lire plus tard.
- [ ] Sauvegardé.

## Backend

- [ ] Créer `FeedbackController`.
- [ ] Créer `FeedbackService`.
- [ ] Empêcher les doublons absurdes.
- [ ] Permettre plusieurs feedbacks différents sur un même article si pertinent.
- [ ] Ajouter un commentaire optionnel.

## Exploitation

- [ ] Créer une page des articles sauvegardés.
- [ ] Créer une page des opportunités business.
- [ ] Créer une page des idées de contenu.

---

# Phase 9 — Sélection quotidienne Top 5

## Workflow n8n Daily Selection

- [ ] Déclenchement tous les jours.
- [ ] Récupérer les articles des dernières 24h.
- [ ] Trier par finalScore.
- [ ] Retirer les doublons thématiques.
- [ ] Envoyer les meilleurs candidats à une IA cloud.
- [ ] Sélectionner 5 sujets maximum.
- [ ] Générer un résumé global.
- [ ] Stocker dans `daily_selection`.
- [ ] Envoyer une notification mail avec lien vers dashboard.

## Prompt daily-selection

- [ ] Créer `n8n/prompts/daily-selection.prompt.md`.
- [ ] Prendre en compte le feedback historique.
- [ ] Favoriser les sujets actionnables.
- [ ] Favoriser les opportunités business.
- [ ] Favoriser les sujets pouvant devenir du contenu.

---

# Phase 10 — Notification mail

## Mail quotidien

- [ ] Configurer SMTP.
- [ ] Créer un template mail simple.
- [ ] Envoyer un lien vers le dashboard.
- [ ] Optionnel : inclure les titres du Top 5.
- [ ] Ne pas faire un mail trop long.

Exemple :

```txt
Ton Top 5 de veille est prêt.

Voir le dashboard :
https://veille.example.com/dashboard
```

---

# Phase 11 — Boucle d'amélioration IA

## Analyse feedback

- [ ] Créer un workflow hebdomadaire `Feedback Learning`.
- [ ] Récupérer les feedbacks positifs et négatifs.
- [ ] Résumer les préférences de Nicolas.
- [ ] Produire un bloc de contexte réutilisable dans les prompts.
- [ ] Stocker ce bloc dans PostgreSQL ou dans un fichier de prompt.

## Préférences à apprendre

- [ ] Sources souvent pertinentes.
- [ ] Catégories préférées.
- [ ] Types de sujets ignorés.
- [ ] Types de sujets transformables en contenu.
- [ ] Types de sujets business.

---

# Phase 12 — Préparation LinkedIn

À faire seulement quand le Top 5 quotidien est fiable.

## Weekly Digest

- [ ] Créer une page `/weekly`.
- [ ] Regrouper les meilleurs sujets de la semaine.
- [ ] Détecter les tendances.
- [ ] Générer 3 angles de posts LinkedIn.
- [ ] Générer un brouillon de post.
- [ ] Ajouter statut :
  - brouillon ;
  - à modifier ;
  - prêt ;
  - publié.

## Prompt LinkedIn

- [ ] Créer `n8n/prompts/weekly-linkedin.prompt.md`.
- [ ] Ton naturel, pas trop corporate.
- [ ] Angle développeur indépendant.
- [ ] Angle apprentissage / retour d'expérience.
- [ ] Pas de contenu trop vendeur.

---

# Phase 13 — Améliorations futures

## Recherche

- [ ] Recherche full-text PostgreSQL.
- [ ] Recherche par tags.
- [ ] Recherche par score.
- [ ] Recherche par source.

## Mémoire long terme

- [ ] Ajouter embeddings plus tard.
- [ ] Évaluer Qdrant.
- [ ] Déduplication sémantique.
- [ ] Recherche sémantique.

## Qualité

- [ ] Ajouter logs d'import.
- [ ] Ajouter page erreurs RSS.
- [ ] Ajouter page monitoring n8n.
- [ ] Ajouter backups PostgreSQL.
- [ ] Ajouter export CSV ou JSON.

---

# Première liste de flux à préparer

## Catégories

- [ ] Symfony / PHP.
- [ ] IA développeur.
- [ ] Agents IA / MCP.
- [ ] Docker / DevOps.
- [ ] n8n / automatisation.
- [ ] Business freelance.
- [ ] Open source.
- [ ] Sécurité.

## Limite

- [ ] Ne pas dépasser 50 flux au début.
- [ ] Commencer avec 10 à 15 flux.
- [ ] Ajouter progressivement selon la qualité des résultats.

---

# Définition du MVP terminé

Le MVP est terminé quand :

- [ ] n8n collecte automatiquement des articles RSS.
- [ ] Les articles sont stockés en base.
- [ ] Une analyse IA est générée.
- [ ] Le Top 5 quotidien apparaît dans Symfony.
- [ ] Nicolas peut donner un feedback.
- [ ] Le feedback est stocké.
- [ ] Un mail quotidien notifie que le dashboard est prêt.

---

# Principe directeur

Toujours privilégier :

```txt
Simple → Fonctionnel → Utile → Améliorable
```

Ne pas chercher à construire la version parfaite dès le début.
