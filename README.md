# Présentation Castor Docker

- `slides/index.html` : les slides, un seul fichier sans dépendance. Il suffit de l'ouvrir dans un navigateur.
  - `→` / `espace` : slide suivante, `←` : précédente, `f` : plein écran, `n` : notes
  - `#12` dans l'URL ouvre directement le schéma
- `demo/` : le projet de démo, une étape par point du schéma
  - `demo/DEMO.md` : le déroulé, les commandes et quoi montrer
  - `demo/step.sh N` : affiche le diff de l'étape N et l'applique
  - `demo/steps/` : l'état de chaque étape

Le plugin vient de `../docker` (dépôt path), donc la démo utilise la branche
en cours, avec le build parallèle.

La démo attend le dépôt du plugin cloné à côté de ce dossier
(`../docker`, voir `demo/castor.composer.json`), puis :

```bash
cd demo
castor composer install
./step.sh 0
castor up
```
