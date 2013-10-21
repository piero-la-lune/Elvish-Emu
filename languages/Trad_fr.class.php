<?php

class Trad {

		# Mots

	const W_SECONDE = 'seconde';
	const W_MINUTE = 'minute';
	const W_HOUR = 'heure';
	const W_DAY = 'jour';
	const W_WEEK = 'semaine';
	const W_MONTH = 'mois';
	const W_YEAR = 'année';
	const W_DECADE = 'décennie';
	const W_SECONDE_P = 'secondes';
	const W_MINUTE_P = 'minutes';
	const W_HOUR_P = 'heures';
	const W_DAY_P = 'jours';
	const W_WEEK_P = 'semaines';
	const W_MONTH_P = 'mois';
	const W_YEAR_P = 'années';
	const W_DECADE_P = 'décennies';

	const W_BACK = 'Retour à l\'album';

		# Phrases

	const S_AGO = 'il y a %duration% %pediod%';
	const S_NOTFOUND = 'La page que vous recherchez n\'existe pas…';
	const S_FORBIDDEN = 'Vous n\'avez pas les droits suffisants pour accèder à cette page…';

		# Verbes

	const V_LOGIN = 'Se connecter';
	const V_CONTINUE = 'Continuer';
	const V_SAVE = 'Enregistrer';
	const V_REMOVE = 'Supprimer';
	const V_EDIT = 'Modifier';

		# Forms

	const F_USERNAME = 'Nom d\'utilisateur :';
	const F_PASSWORD = 'Mot de passe :';
	const F_RANK = 'Rang :';
	const F_COOKIE = 'Type de connexion :';
	const F_COOKIE_FALSE = 'Ordinateur public';
	const F_COOKIE_TRUE = 'Ordinateur privé (rester connecté)';
	const F_URL = 'URL :';
	const F_URL_REWRITING = 'URL rewriting :';
	const F_LANGUAGE = 'Langue :';
	const F_ADD = 'Ajouter :';
	const F_AUTHORIZED_USERS = 'Utilisateurs :';
	const F_DIR = 'Dossier :';

	const F_NAME = 'Nom de l\'album :';
	const F_COMMENT = 'Commentaire :';

	const F_TIP_PASSWORD = 'Laissez vide pour ne pas le changer.';
	const F_TIP_URL_REWRITING = 'Laissez vide pour désactiver l\'URL rewriting. Sinon, indiquez le chemin du dossier de Elvish Emu (en commençant et terminant par un "/") par rapport au nom de domaine.';

		# Titres

	const T_404 = 'Erreur 404 – Page non trouvée';
	const T_403 = 'Erreur 403 – Accès interdit';
	const T_LOGIN = 'Connexion';
	const T_LOGOUT = 'Déconnexion';
	const T_INSTALLATION = 'Installation';
	const T_SETTINGS = 'Préférences';
	const T_GLOBAL_SETTINGS = 'Réglages généraux';
	const T_USERS_SETTINGS = 'Utilisateurs';
	const T_HOME = 'Accueil';
	const T_NEW = 'Nouveau';
	const T_NEW_ALBUM = 'Nouveau album';
	const T_ALBUMS = 'Albums';

		# Alertes

	const A_ERROR_LOGIN = 'Mauvais nom d\'utilisateur ou mot de passe.';
	const A_ERROR_LOGIN_WAIT = 'Merci de patienter %duration% %period% avant de réessayer. Ceci est une protection contre les attaques malveillantes.';
	const A_ERROR_FORM = 'Merci de remplir tous les champs.';
	const A_ERROR_AJAX = 'Une erreur est survenue. Merci de réessayer.';
	const A_ERROR_AJAX_LOGIN = 'Vous êtes déconnecté. Raffraichissez la page, connectez-vous, puis vous pourrez réessayer.';
	const A_ERROR_SAME_USERNAME = 'Ce nom d\'utilisateur est déjà utilisé.';
	const A_ERROR_EMPTY_LOGIN = 'Merci de spécifier un nom d\'utilisateur et un mot de passe.';
	const A_ERROR_NO_USER = 'Aucun utilisateur ne correspond.';
	const A_ERROR_EMPTY_NAME = 'Merci de donner un nom à l\'album.';

	const A_SUCCESS_INSTALL = 'Elvish Emu est maintenant correctement installé. Connectez-vous pour commencer à l\'utiliser.';
	const A_SUCCESS_SETTINGS = 'Les préférences ont bien été enregistrées.';
	const A_SUCCESS_USER_ADD = 'L\'utilisateur a bien été ajouté.';
	const A_SUCCESS_USER_RM = 'L\'utilisateur a bien été supprimé.';
	const A_SUCCESS_USER_EDIT = 'L\'utilisateur a bien été modifié.';
	const A_SUCCESS_NEW_ALBUM = 'L\'album a bien été créé.';
	const A_SUCCESS_EDIT_ALBUM = 'L\'album a bien été modifié.';
	const A_SUCCESS_RM_ALBUM = 'L\'album a bien été supprimé.';
	const A_SUCCESS_EDIT_FILE = 'Le fichier a bien été modifié.';

	public static $settings = array(
		'validate_url' => 'L\'url n\'est pas valide.'
	);

	public static $ranks = array(
		RANK_ADMIN => 'Administrateur',
		RANK_VISITOR => 'Visiteur'
	);

}

?>