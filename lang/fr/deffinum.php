<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'deffinum', language 'fr'
 *
 * @package   mod_deffinum
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['toc'] = 'Table des matières';
$string['navigation'] = 'Navigation';
$string['aicchacptimeout'] = 'Délai d\'attente AICC HACP';
$string['aicchacptimeout_desc'] = 'Durée en minutes pendant laquelle une session AICC HACP externe peut rester ouverte';
$string['aicchacpkeepsessiondata'] = 'Données de session AICC HACP';
$string['aicchacpkeepsessiondata_desc'] = 'Durée en jours pour conserver les données de session AICC HACP externe (une valeur élevée remplira la table avec des données anciennes mais peut être utile lors du débogage)';
$string['aiccuserid'] = 'Passer l\'ID utilisateur numérique AICC';
$string['aiccuserid_desc'] = 'La norme AICC pour les noms d\'utilisateur est très restrictive par rapport à Moodle et autorise uniquement les caractères alphanumériques, le tiret et le trait de soulignement. Les points, les espaces et le symbole @ ne sont pas autorisés. Si activé, les numéros d\'identification des utilisateurs sont transmis au package AICC au lieu des noms d\'utilisateur.';
$string['activation'] = 'Activation';
$string['activityloading'] = 'Vous serez automatiquement redirigé vers l\'activité dans';
$string['activityoverview'] = 'Vous avez des packages DEFFINUM qui nécessitent une attention';
$string['activitypleasewait'] = 'Chargement de l\'activité, veuillez patienter ...';
$string['adminsettings'] = 'Paramètres administrateur';
$string['advanced'] = 'Paramètres';
$string['aliasonly'] = 'Lors de la sélection d\'un fichier imsmanifest.xml à partir d\'un dépôt, vous devez utiliser un alias/raccourci pour ce fichier.';
$string['allowapidebug'] = 'Activer le débogage et la traçabilité de l\'API (définir le masque de capture avec apidebugmask)';
$string['allowtypeexternal'] = 'Activer le type de package externe';
$string['allowtypeexternalaicc'] = 'Activer l\'URL AICC directe';
$string['allowtypeexternalaicc_desc'] = 'Si activé, cela permet une URL directe vers un package AICC simple';
$string['allowtypelocalsync'] = 'Activer le type de package téléchargé';
$string['allowtypeaicchacp'] = 'Activer l\'AICC HACP externe';
$string['allowtypeaicchacp_desc'] = 'Si activé, cela permet une communication externe AICC HACP sans nécessiter la connexion de l\'utilisateur pour les requêtes post du package AICC externe';
$string['apidebugmask'] = 'Masque de capture de débogage API - utiliser une expression régulière simple sur &lt;nom d\'utilisateur&gt;:&lt;nom de l\'activité&gt; par exemple admin:.* déboguera uniquement pour l\'utilisateur admin';
$string['areacontent'] = 'Fichiers de contenu';
$string['areapackage'] = 'Fichier de package';
$string['asset'] = 'Ressource';
$string['assetlaunched'] = 'Ressource - Vue';
$string['attempt'] = 'Tentative';
$string['attempts'] = 'Tentatives';
$string['attemptstatusall'] = 'Tableau de bord et page d\'entrée';
$string['attemptstatusmy'] = 'Tableau de bord uniquement';
$string['attemptstatusentry'] = 'Page d\'entrée uniquement';
$string['attemptsx'] = '{$a} tentatives';
$string['attemptsmanagement'] = 'Gestion des tentatives';
$string['attempt1'] = '1 tentative';
$string['attr_error'] = 'Valeur incorrecte pour l\'attribut ({$a->attr}) dans la balise {$a->tag}.';
$string['autocommit'] = 'Validation automatique';
$string['autocommit_help'] = 'Si activé, les données DEFFINUM sont automatiquement enregistrées dans la base de données. Utile pour les objets DEFFINUM qui n\'enregistrent pas régulièrement leurs données.';
$string['autocommitdesc'] = 'Enregistrer automatiquement les données DEFFINUM si le package DEFFINUM ne les enregistre pas.';
$string['autocontinue'] = 'Continuation automatique';
$string['autocontinue_help'] = 'Si activé, les objets d\'apprentissage suivants sont lancés automatiquement, sinon le bouton Continuer doit être utilisé.';
$string['autocontinuedesc'] = 'Si activé, les objets d\'apprentissage suivants sont lancés automatiquement, sinon le bouton Continuer doit être utilisé.';
$string['averageattempt'] = 'Tentatives moyennes';
$string['badmanifest'] = 'Certaines erreurs de manifeste : voir le journal des erreurs';
$string['badimsmanifestlocation'] = 'Un fichier imsmanifest.xml a été trouvé, mais il n\'était pas à la racine de votre fichier zip, veuillez reconditionner votre DEFFINUM';
$string['badarchive'] = 'Vous devez fournir un fichier zip valide';
$string['browse'] = 'Aperçu';
$string['browsed'] = 'Consulté';
$string['browsemode'] = 'Mode aperçu';
$string['browserepository'] = 'Parcourir le dépôt';
$string['calculatedweight'] = 'Poids calculé';
$string['calendarend'] = '{$a} se termine';
$string['calendarstart'] = '{$a} commence';
$string['cannotaccess'] = 'Vous ne pouvez pas appeler ce script de cette manière';
$string['cannotfindsco'] = 'Impossible de trouver le SCO';
$string['closebeforeopen'] = 'Vous avez spécifié une date de clôture avant la date d\'ouverture.';
$string['collapsetocwinsize'] = 'Réduire la TOC lorsque la taille de la fenêtre est inférieure';
$string['collapsetocwinsizedesc'] = 'Ce paramètre vous permet de spécifier la taille de la fenêtre en dessous de laquelle la TOC doit se réduire automatiquement.';
$string['compatibilitysettings'] = 'Paramètres de compatibilité';
$string['completed'] = 'Terminé';
$string['completiondetail:completionstatuspassed'] = 'Passer l\'activité';
$string['completiondetail:completionstatuscompleted'] = 'Terminer l\'activité';
$string['completiondetail:completionstatuscompletedorpassed'] = 'Terminer ou passer l\'activité';
$string['completiondetail:completionscore'] = 'Recevoir un score de {$a} ou plus';
$string['completiondetail:allscos'] = 'Faire toutes les parties de cette activité';
$string['completionscorerequired'] = 'Score minimum requis';
$string['completionscorerequireddesc'] = 'Un score minimum de {$a} est requis pour terminer';
$string['completionstatus_passed'] = 'Passé';
$string['completionstatus_completed'] = 'Terminé';
$string['completionstatusallscos'] = 'Exiger que tous les scos renvoient le statut de terminaison';
$string['completionstatusallscos_help'] = 'Certains packages DEFFINUM contiennent plusieurs composants ou "scos" - lorsque cela est activé, tous les scos du package doivent renvoyer le statut de leçon pertinent pour que cette activité soit marquée comme terminée.';
$string['completionstatusrequired'] = 'Statut requis';
$string['completionstatusrequireddesc'] = 'L\'étudiant doit atteindre au moins un des statuts suivants : {$a}';
$string['completionstatusrequired_help'] = 'Cochez un ou plusieurs statuts obligera un utilisateur à atteindre au moins un des statuts cochés pour être marqué comme terminé dans cette activité DEFFINUM, ainsi que toute autre exigence de terminaison d\'activité.';
$string['confirmloosetracks'] = 'AVERTISSEMENT : Le package semble avoir été modifié. Si la structure du package est modifiée, certains suivis d\'utilisateurs peuvent être perdus pendant le processus de mise à jour.';
$string['contents'] = 'Contenus';
$string['coursepacket'] = 'Package de cours';


$string['coursestruct'] = 'Structure du cours';
$string['crontask'] = 'Traitement en arrière-plan pour DEFFINUM';
$string['currentwindow'] = 'Fenêtre actuelle';
$string['datadir'] = 'Erreur du système de fichiers : impossible de créer le répertoire de données du cours';
$string['defaultdisplaysettings'] = 'Paramètres d\'affichage par défaut';
$string['defaultgradesettings'] = 'Paramètres de note par défaut';
$string['defaultothersettings'] = 'Autres paramètres par défaut';
$string['deleteattemptcheck'] = 'Êtes-vous absolument sûr de vouloir complètement supprimer ces tentatives ?';
$string['deleteallattempts'] = 'Supprimer toutes les tentatives DEFFINUM';
$string['deleteselected'] = 'Supprimer les tentatives sélectionnées';
$string['deleteuserattemptcheck'] = 'Êtes-vous absolument sûr de vouloir complètement supprimer toutes vos tentatives ?';
$string['details'] = 'Détails du suivi';
$string['directories'] = 'Afficher les liens de répertoire';
$string['disabled'] = 'Désactivé';
$string['display'] = 'Afficher le package';
$string['displayattemptstatus'] = 'Afficher le statut de la tentative';
$string['displayattemptstatus_help'] = 'Cette préférence permet de résumer les tentatives des utilisateurs à afficher dans le bloc de présentation du cours sur le tableau de bord et/ou la page d\'entrée DEFFINUM.';
$string['displayattemptstatusdesc'] = 'Afficher ou non un résumé des tentatives de l\'utilisateur dans le bloc de présentation du cours sur le tableau de bord et/ou la page d\'entrée DEFFINUM.';
$string['displaycoursestructure'] = 'Afficher la structure du cours sur la page d\'entrée';
$string['displaycoursestructure_help'] = 'Si activé, la table des matières est affichée sur la page de présentation de DEFFINUM.';
$string['displaycoursestructuredesc'] = 'Si activé, la table des matières est affichée sur la page de présentation de DEFFINUM.';
$string['displaydesc'] = 'Afficher ou non le package DEFFINUM dans une nouvelle fenêtre.';
$string['displaysettings'] = 'Paramètres d\'affichage';
$string['dnduploaddeffinum'] = 'Ajouter un package DEFFINUM';
$string['domxml'] = 'Bibliothèque externe DOMXML';
$string['element'] = 'Élément';
$string['enter'] = 'Entrer';
$string['entercourse'] = 'Entrer dans le cours';
$string['errorlogs'] = 'Journal des erreurs';
$string['eventattemptdeleted'] = 'Tentative supprimée';
$string['eventinteractionsviewed'] = 'Interactions vues';
$string['eventreportviewed'] = 'Rapport vu';
$string['eventscolaunched'] = 'Sco lancé';
$string['eventscorerawsubmitted'] = 'Score brut DEFFINUM soumis';
$string['eventstatussubmitted'] = 'Statut DEFFINUM soumis';
$string['eventtracksviewed'] = 'Suivis vus';
$string['eventuserreportviewed'] = 'Rapport utilisateur vu';
$string['everyday'] = 'Tous les jours';
$string['everytime'] = 'À chaque utilisation';
$string['exceededmaxattempts'] = 'Vous avez atteint le nombre maximum de tentatives.';
$string['exit'] = 'Quitter le cours';
$string['exitactivity'] = 'Quitter l\'activité';
$string['expired'] = 'Désolé, cette activité a fermé le {$a} et n\'est plus disponible';
$string['external'] = 'Mettre à jour le moment des packages externes';
$string['failed'] = 'Échoué';
$string['finishdeffinum'] = 'Si vous avez terminé de visualiser cette ressource, {$a}';
$string['finishdeffinumlinkname'] = 'cliquez ici pour revenir à la page du cours';
$string['firstaccess'] = 'Premier accès';
$string['firstattempt'] = 'Première tentative';
$string['floating'] = 'Flottant';
$string['forcecompleted'] = 'Forcer terminé';
$string['forcecompleted_help'] = 'Si activé, le statut de la tentative actuelle est forcé à "terminé". (Applicable uniquement aux packages DEFFINUM 1.2.)';
$string['forcecompleteddesc'] = 'Cette préférence définit la valeur par défaut pour le paramètre forcer terminé';
$string['forcenewattempts'] = 'Forcer une nouvelle tentative';
$string['forcenewattempts_help'] = 'Il y a 3 options :

* Non - Si une tentative précédente est terminée, passée ou échouée, l\'étudiant aura la possibilité d\'entrer en mode révision ou de commencer une nouvelle tentative.
* Lorsque la tentative précédente est terminée, passée ou échouée - Cela repose sur le package DEFFINUM définissant le statut de "terminé", "passé" ou "échoué".
* Toujours - Chaque réentrée dans l\'activité DEFFINUM générera une nouvelle tentative et l\'étudiant ne sera pas renvoyé au même point qu\'il avait atteint lors de sa tentative précédente.';
$string['forceattemptalways'] = 'Toujours';
$string['forceattemptoncomplete'] = 'Lorsque la tentative précédente est terminée, passée ou échouée';
$string['forcejavascript'] = 'Forcer les utilisateurs à activer JavaScript';
$string['forcejavascript_desc'] = 'Si activé (recommandé), cela empêche l\'accès aux objets DEFFINUM lorsque JavaScript n\'est pas pris en charge/activé dans le navigateur de l\'utilisateur. Si désactivé, l\'utilisateur peut visualiser le DEFFINUM, mais la communication API échouera et aucune information de note ne sera enregistrée.';
$string['forcejavascriptmessage'] = 'JavaScript est requis pour visualiser cet objet, veuillez activer JavaScript dans votre navigateur et réessayer.';
$string['found'] = 'Manifeste trouvé';
$string['frameheight'] = 'La hauteur de la fenêtre ou du cadre de la scène.';
$string['framewidth'] = 'La largeur de la fenêtre ou du cadre de la scène.';
$string['fromleft'] = 'De la gauche';
$string['fromtop'] = 'Du haut';
$string['fullscreen'] = 'Remplir tout l\'écran';
$string['general'] = 'Données générales';
$string['gradeaverage'] = 'Note moyenne';
$string['gradeforattempt'] = 'Note pour la tentative';
$string['gradehighest'] = 'Note la plus élevée';
$string['grademethod'] = 'Méthode de notation';
$string['grademethod_help'] = 'La méthode de notation définit comment la note pour une seule tentative de l\'activité est déterminée.

Il y a 4 méthodes de notation :

* Objets d\'apprentissage - Le nombre d\'objets d\'apprentissage terminés/passés
* Note la plus élevée - Le score le plus élevé obtenu dans tous les objets d\'apprentissage réussis
* Note moyenne - La moyenne de tous les scores
* Note totale - La somme de tous les scores';
$string['grademethoddesc'] = 'La méthode de notation définit comment la note pour une seule tentative de l\'activité est déterminée.';
$string['gradereported'] = 'Note rapportée';
$string['gradesettings'] = 'Paramètres de note';
$string['gradescoes'] = 'Objets d\'apprentissage';
$string['gradesum'] = 'Note totale';
$string['height'] = 'Hauteur';
$string['hidden'] = 'Caché';
$string['hidebrowse'] = 'Désactiver le mode aperçu';
$string['hidebrowse_help'] = 'Le mode aperçu permet à un étudiant de parcourir une activité avant de tenter de la faire. Si le mode aperçu est désactivé, le bouton d\'aperçu est caché.';
$string['hidebrowsedesc'] = 'Le mode aperçu permet à un étudiant de parcourir une activité avant de tenter de la faire.';
$string['hideexit'] = 'Masquer le lien de sortie';
$string['hidereview'] = 'Masquer le bouton de révision';
$string['hidetoc'] = 'Afficher la structure du cours dans le lecteur';
$string['hidetoc_help'] = 'Comment la table des matières est affichée dans le lecteur DEFFINUM';
$string['hidetocdesc'] = 'Ce paramètre spécifie comment la table des matières est affichée dans le lecteur DEFFINUM.';
$string['highestattempt'] = 'Tentative la plus élevée';
$string['chooseapacket'] = 'Choisir ou mettre à jour un package';
$string['identifier'] = 'Identifiant de la question';
$string['incomplete'] = 'Incomplet';
$string['indicator:cognitivedepth'] = 'Cognitif DEFFINUM';
$string['indicator:cognitivedepth_help'] = 'Cet indicateur est basé sur la profondeur cognitive atteinte par l\'étudiant dans une activité DEFFINUM.';
$string['indicator:cognitivedepthdef'] = 'Cognitif DEFFINUM';
$string['indicator:cognitivedepthdef_help'] = 'Le participant a atteint ce pourcentage d\'engagement cognitif offert par les activités DEFFINUM pendant cet intervalle d\'analyse (Niveaux = Pas de vue, Vue, Soumission, Vue du feedback)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'Social DEFFINUM';
$string['indicator:socialbreadth_help'] = 'Cet

 indicateur est basé sur la largeur sociale atteinte par l\'étudiant dans une activité DEFFINUM.';
$string['indicator:socialbreadthdef'] = 'Social DEFFINUM';
$string['indicator:socialbreadthdef_help'] = 'Le participant a atteint ce pourcentage d\'engagement social offert par les activités DEFFINUM pendant cet intervalle d\'analyse (Niveaux = Pas de participation, Participant seul)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['interactions'] = 'Interactions';
$string['masteryoverride'] = 'Le score de maîtrise remplace le statut';
$string['masteryoverride_help'] = 'Si activé et qu\'un score de maîtrise est fourni, lorsque LMSFinish est appelé et qu\'un score brut a été défini, le statut sera recalculé en utilisant le score brut et le score de maîtrise, et tout statut fourni par le DEFFINUM (y compris "incomplet") sera remplacé.';
$string['masteryoverridedesc'] = 'Cette préférence définit la valeur par défaut pour le paramètre de remplacement du score de maîtrise';
$string['myattempts'] = 'Mes tentatives';
$string['myaiccsessions'] = 'Mes sessions AICC';
$string['repositorynotsupported'] = 'Ce dépôt ne prend pas en charge le lien direct vers un fichier imsmanifest.xml.';
$string['trackid'] = 'ID';
$string['trackid_help'] = 'Ceci est l\'identifiant défini par votre package DEFFINUM pour cette question, la spécification DEFFINUM ne permet pas de fournir le texte complet de la question.';
$string['trackcorrectcount'] = 'Nombre correct';
$string['trackcorrectcount_help'] = 'Nombre de résultats corrects pour la question';
$string['trackpattern'] = 'Modèle';
$string['trackpattern_help'] = 'Ceci est ce qu\'une réponse correcte à cette question serait, cela ne montre pas la réponse de l\'apprenant.';
$string['tracklatency'] = 'Latence';
$string['tracklatency_help'] = 'Le temps écoulé entre le moment où la question a été mise à disposition de l\'apprenant pour une réponse et le moment de la première réponse.';
$string['trackresponse'] = 'Réponse';
$string['trackresponse_help'] = 'Ceci est la réponse donnée par l\'apprenant pour cette question';
$string['trackresult'] = 'Résultat';
$string['trackresult_help'] = 'Indique si l\'apprenant a entré une réponse correcte.';
$string['trackscoremin'] = 'Score minimum';
$string['trackscoremin_help'] = 'Valeur minimale pouvant être attribuée pour le score brut';
$string['trackscoremax'] = 'Score maximum';
$string['trackscoremax_help'] = 'Valeur maximale pouvant être attribuée pour le score brut';
$string['trackscoreraw'] = 'Score brut';
$string['trackscoreraw_help'] = 'Nombre reflétant la performance de l\'apprenant par rapport à la plage délimitée par les valeurs de min et max';
$string['tracksuspenddata'] = 'Suspendre les données';
$string['tracksuspenddata_help'] = 'Fournit un espace pour stocker et récupérer des données entre les sessions de l\'apprenant';
$string['tracktime'] = 'Temps';
$string['tracktime_help'] = 'Heure à laquelle la tentative a commencé';
$string['tracktype'] = 'Type';
$string['tracktype_help'] = 'Type de la question, par exemple "choix" ou "réponse courte".';
$string['trackweight'] = 'Poids';
$string['trackweight_help'] = 'Poids attribué à la question lors du calcul du score.';
$string['invalidactivity'] = 'L\'activité DEFFINUM est incorrecte';
$string['invalidmanifestname'] = 'Seuls les fichiers imsmanifest.xml ou .zip peuvent être sélectionnés';
$string['invalidstatus'] = 'Statut invalide';
$string['invalidurl'] = 'URL spécifiée invalide';
$string['invalidurlhttpcheck'] = 'URL spécifiée invalide. Message de débogage :<pre>{$a->cmsg}</pre>';
$string['invalidhacpsession'] = 'Session HACP invalide';
$string['invalidmanifestresource'] = 'AVERTISSEMENT : Les ressources suivantes ont été référencées dans votre manifeste mais n\'ont pas pu être trouvées :';
$string['last'] = 'Dernier accès le';
$string['lastaccess'] = 'Dernier accès';
$string['lastattempt'] = 'Dernière tentative complétée';
$string['lastattemptlock'] = 'Verrouiller après la dernière tentative';
$string['lastattemptlock_help'] = 'Si activé, un étudiant est empêché de lancer le lecteur DEFFINUM après avoir utilisé toutes ses tentatives allouées.';
$string['lastattemptlockdesc'] = 'Si activé, un étudiant est empêché de lancer le lecteur DEFFINUM après avoir utilisé toutes ses tentatives allouées.';
$string['location'] = 'Afficher la barre de localisation';
$string['max'] = 'Score maximum';
$string['maximumattempts'] = 'Nombre de tentatives';
$string['maximumattempts_help'] = 'Ce paramètre permet de limiter le nombre de tentatives. Il est uniquement applicable pour les packages DEFFINUM 1.2 et AICC.';
$string['maximumattemptsdesc'] = 'Cette préférence définit le nombre maximum de tentatives par défaut pour une activité';
$string['maximumgradedesc'] = 'Cette préférence définit la note maximale par défaut pour une activité';
$string['menubar'] = 'Afficher la barre de menu';
$string['min'] = 'Score minimum';
$string['missing_attribute'] = 'Attribut manquant {$a->attr} dans la balise {$a->tag}';
$string['missingparam'] = 'Un paramètre requis est manquant ou incorrect';
$string['missing_tag'] = 'Balise manquante {$a->tag}';
$string['mode'] = 'Mode';
$string['modulename'] = 'Package DEFFINUM';
$string['modulename_help'] = 'Un package DEFFINUM est une collection de fichiers qui sont emballés selon une norme convenue pour les objets d\'apprentissage. Le module d\'activité DEFFINUM permet de télécharger des packages DEFFINUM ou AICC sous forme de fichier zip et de les ajouter à un cours.

Le contenu est généralement affiché sur plusieurs pages, avec navigation entre les pages. Il existe diverses options pour afficher le contenu dans une fenêtre contextuelle, avec une table des matières, avec des boutons de navigation, etc. Les activités DEFFINUM incluent généralement des questions, les notes étant enregistrées dans le carnet de notes.

Les activités DEFFINUM peuvent être utilisées

* Pour présenter du contenu multimédia et des animations
* Comme outil d\'évaluation';
$string['modulename_link'] = 'mod/deffinum/view';
$string['modulenameplural'] = 'Packages DEFFINUM';
$string['nav'] = 'Afficher la navigation';
$string['nav_help'] = 'Ce paramètre spécifie s\'il faut afficher ou masquer les boutons de navigation et leur position.

Il y a 3 options :

* Non - Les boutons de navigation ne sont pas affichés
* Sous le contenu - Les boutons de navigation sont affichés sous le contenu du package DEFFINUM
* Flottant - Les boutons de navigation sont affichés en flottant, la position depuis le haut et depuis la gauche étant déterminée par le package.';
$string['navdesc'] = 'Ce paramètre spécifie s\'il faut afficher ou masquer les boutons de navigation et leur position.';
$string['navpositionleft'] = 'Position des boutons de navigation depuis la gauche en pixels.';
$string['navpositiontop'] = 'Position des boutons de navigation depuis le haut en pixels.';
$string['networkdropped'] = 'Le lecteur DEFFINUM a déterminé que votre connexion Internet est instable ou a été interrompue. Si vous continuez dans cette activité DEFFINUM, vos progrès peuvent ne pas être enregistrés.<br />
Vous devriez quitter l\'activité maintenant et revenir lorsque vous aurez une connexion Internet fiable.';
$string['newattempt'] = 'Commencer une nouvelle tentative';
$string['next'] = 'Continuer';
$string['noactivity'] = 'Rien à signaler';
$string['noattemptsallowed'] = 'Nombre de tentatives autorisées';
$string['noattemptsmade'] = 'Nombre de tentatives que vous avez faites';
$string['no_attributes'] = 'La balise {$a->tag} doit avoir des attributs';
$string['no_children'] = 'La balise {$a->tag} doit avoir des enfants';
$string['nolimit'] = 'Tentatives illimitées';
$string['nomanifest'] = 'Package de fichier incorrect - imsmanifest.xml manquant ou structure AICC';
$string['noprerequisites'] = 'Désolé, mais vous n\'avez pas les prérequis nécessaires pour accéder à cette activité.';
$string['noreports'] = 'Aucun rapport à afficher';
$string['normal'] = 'Normal';
$string['noscriptnodeffinum'] = 'Votre navigateur ne prend pas en charge JavaScript ou il a la prise en charge de JavaScript désactivée. Ce package DEFFINUM peut ne pas se lire ou enregistrer les données correctement.';
$string['notattempted'] = 'Non tenté';
$string['not_corr_type'] = 'Type incompatible pour la balise {$a->tag}';
$string['notopenyet'] = 'Désolé, cette activité n\'est pas disponible avant le {$a}';
$string['object

ives'] = 'Objectifs';
$string['openafterclose'] = 'Vous avez spécifié une date d\'ouverture après la date de clôture';
$string['optallstudents'] = 'tous les utilisateurs';
$string['optattemptsonly'] = 'utilisateurs avec tentatives uniquement';
$string['optnoattemptsonly'] = 'utilisateurs sans tentatives uniquement';
$string['options'] = 'Options (Empêché par certains navigateurs)';
$string['optionsadv'] = 'Options (Avancées)';
$string['optionsadv_desc'] = 'Si coché, la largeur et la hauteur seront listées comme paramètres avancés.';
$string['organization'] = 'Organisation';
$string['organizations'] = 'Organisations';
$string['othersettings'] = 'Paramètres supplémentaires';
$string['page-mod-deffinum-x'] = 'Toute page de module DEFFINUM';
$string['pagesize'] = 'Taille de la page';
$string['package'] = 'Fichier de package';
$string['package_help'] = 'Le fichier de package est un fichier zip (ou pif) contenant des fichiers de définition de cours DEFFINUM/AICC.';
$string['packagedir'] = 'Erreur du système de fichiers : impossible de créer le répertoire de package';
$string['packagefile'] = 'Aucun fichier de package spécifié';
$string['packagehdr'] = 'Package';
$string['packageurl'] = 'URL';
$string['packageurl_help'] = 'Ce paramètre permet de spécifier une URL pour le package DEFFINUM au lieu de choisir un fichier via le sélecteur de fichiers.';
$string['passed'] = 'Réussi';
$string['php5'] = 'PHP 5 (bibliothèque native DOMXML)';
$string['pluginadministration'] = 'Administration du package DEFFINUM';
$string['pluginname'] = 'Package DEFFINUM';
$string['popup'] = 'Nouvelle fenêtre';
$string['popuplaunched'] = 'Ce package DEFFINUM a été lancé dans une fenêtre contextuelle. Si vous avez terminé de visualiser cette ressource, cliquez ici pour revenir à la page du cours';
$string['popupmenu'] = 'Dans un menu déroulant';
$string['popupopen'] = 'Ouvrir le package dans une nouvelle fenêtre';
$string['popupsblocked'] = 'Il semble que les fenêtres contextuelles soient bloquées, empêchant ce package DEFFINUM de s\'exécuter. Veuillez vérifier les paramètres de votre navigateur avant d\'essayer à nouveau.';
$string['position_error'] = 'La balise {$a->tag} ne peut pas être enfant de la balise {$a->parent}';
$string['preferencesuser'] = 'Préférences pour ce rapport';
$string['preferencespage'] = 'Préférences uniquement pour cette page';
$string['prev'] = 'Précédent';
$string['privacy:metadata:aicc:data'] = 'Données personnelles transmises via le sous-système AICC/DEFFINUM.';
$string['privacy:metadata:aicc:externalpurpose'] = 'Ce plugin envoie des données à l\'extérieur en utilisant l\'AICC HACP.';
$string['privacy:metadata:aicc_session:lessonstatus'] = 'Le statut de la leçon à suivre';
$string['privacy:metadata:aicc_session:deffinummode'] = 'Le mode de l\'élément à suivre';
$string['privacy:metadata:aicc_session:deffinumstatus'] = 'Le statut de l\'élément à suivre';
$string['privacy:metadata:aicc_session:sessiontime'] = 'Le temps de session à suivre';
$string['privacy:metadata:aicc_session:timecreated'] = 'L\'heure à laquelle l\'élément suivi a été créé';
$string['privacy:metadata:attempt'] = 'Le numéro de la tentative';
$string['privacy:metadata:scoes_track:element'] = 'Le nom de l\'élément à suivre';
$string['privacy:metadata:scoes_track:value'] = 'La valeur de l\'élément donné';
$string['privacy:metadata:deffinum_aicc_session'] = 'Les informations de session de l\'AICC HACP';
$string['privacy:metadata:deffinum_scoes_track'] = 'Les données suivies des SCOes appartenant à l\'activité';
$string['privacy:metadata:timemodified'] = 'L\'heure à laquelle l\'élément suivi a été modifié pour la dernière fois';
$string['privacy:metadata:userid'] = 'L\'ID de l\'utilisateur ayant accédé à l\'activité DEFFINUM';
$string['protectpackagedownloads'] = 'Protéger les téléchargements de packages';
$string['protectpackagedownloads_desc'] = 'Si activé, les packages DEFFINUM ne peuvent être téléchargés que si l\'utilisateur dispose de la capacité course:manageactivities. Si désactivé, les packages DEFFINUM peuvent toujours être téléchargés (par mobile ou autres moyens).';
$string['raw'] = 'Score brut';
$string['regular'] = 'Manifeste régulier';
$string['report'] = 'Rapport';
$string['reports'] = 'Rapports';
$string['reportcountallattempts'] = '{$a->nbattempts} tentatives pour {$a->nbusers} utilisateurs, sur {$a->nbresults} résultats';
$string['reportcountattempts'] = '{$a->nbresults} résultats ({$a->nbusers} utilisateurs)';
$string['response'] = 'Réponse';
$string['result'] = 'Résultat';
$string['results'] = 'Résultats';
$string['review'] = 'Réviser';
$string['reviewmode'] = 'Mode révision';
$string['rightanswer'] = 'Bonne réponse';
$string['deffinumstandard'] = 'Mode normes DEFFINUM';
$string['deffinumstandarddesc'] = 'Lorsque désactivé, Moodle permet aux packages DEFFINUM 1.2 de stocker plus que ce que la spécification autorise, et utilise les paramètres de format de nom complet Moodle lors de la transmission du nom de l\'utilisateur au package DEFFINUM.';
$string['scoes'] = 'Objets d\'apprentissage';
$string['score'] = 'Score';
$string['deffinum:addinstance'] = 'Ajouter un nouveau package DEFFINUM';
$string['deffinumclose'] = 'Disponible jusqu\'à';
$string['deffinumcourse'] = 'Cours d\'apprentissage';
$string['deffinum:deleteresponses'] = 'Supprimer les tentatives DEFFINUM';
$string['deffinumloggingoff'] = 'Le journal de l\'API est désactivé';
$string['deffinumloggingon'] = 'Le journal de l\'API est activé';
$string['deffinumopen'] = 'Disponible à partir de';
$string['deffinumresponsedeleted'] = 'Tentatives d\'utilisateur supprimées';
$string['deffinum:deleteownresponses'] = 'Supprimer ses propres tentatives';
$string['deffinum:savetrack'] = 'Enregistrer les suivis';
$string['deffinum:skipview'] = 'Passer l\'aperçu';
$string['deffinumtype'] = 'Type';
$string['deffinumtype_help'] = 'Ce paramètre détermine comment le package est inclus dans le cours. Il y a jusqu\'à 4 options :

* Package téléchargé - Permet de choisir un package DEFFINUM via le sélecteur de fichiers
* Manifeste DEFFINUM externe - Permet de spécifier une URL imsmanifest.xml. Remarque : Si l\'URL a un nom de domaine différent de celui de votre site, alors "Package téléchargé" est une meilleure option, car sinon les notes ne sont pas enregistrées.
* Package téléchargé - Permet de spécifier une URL de package. Le package sera décompressé et enregistré localement, et mis à jour lorsque le package DEFFINUM externe est mis à jour.
* URL AICC externe - cette URL est l\'URL de lancement pour une activité AICC unique. Un package pseudo sera construit autour de cela.';
$string['deffinum:viewreport'] = 'Voir les rapports';
$string['deffinum:viewscores'] = 'Voir les scores';
$string['scrollbars'] = 'Permettre le défilement de la fenêtre';
$string['search:activity'] = 'Package DEFFINUM - informations sur l\'activité';
$string['selectall'] = 'Tout sélectionner';
$string['selectnone'] = 'Tout désélectionner';
$string['show'] = 'Afficher';
$string['sided'] = 'Sur le côté';
$string['skipview'] = 'L\'étudiant passe la page de la structure du contenu';
$string['skipview_help'] = 'Ce paramètre spécifie si la page de structure du contenu doit être toujours passée (non affichée). Si le package contient un seul objet d\'apprentissage, la page de structure du contenu peut toujours être passée.';
$string['skipviewdesc'] = 'Cette préférence définit la valeur par défaut pour quand passer la structure du contenu pour une page';
$string['slashargs'] = 'AVERTISSEMENT : les arguments de barre oblique

 sont désactivés sur ce site et les objets peuvent ne pas fonctionner comme prévu !';
$string['stagesize'] = 'Taille de la scène';
$string['stagesize_help'] = 'Ces deux paramètres spécifient la largeur et la hauteur du cadre/fenêtre pour les objets d\'apprentissage.';
$string['started'] = 'Commencé le';
$string['status'] = 'Statut';
$string['statusbar'] = 'Afficher la barre de statut';
$string['student_response'] = 'Réponse';
$string['subplugintype_deffinumreport'] = 'Rapport';
$string['subplugintype_deffinumreport_plural'] = 'Rapports';
$string['suspended'] = 'Suspendu';
$string['syntax'] = 'Erreur de syntaxe';
$string['tag_error'] = 'Balise inconnue ({$a->tag}) avec ce contenu : {$a->value}';
$string['time'] = 'Temps';
$string['title'] = 'Titre';
$string['toolbar'] = 'Afficher la barre d\'outils';
$string['too_many_attributes'] = 'La balise {$a->tag} a trop d\'attributs';
$string['too_many_children'] = 'La balise {$a->tag} a trop d\'enfants';
$string['totaltime'] = 'Temps';
$string['trackingloose'] = 'AVERTISSEMENT : Les données de suivi de ce package seront perdues !';
$string['type'] = 'Type';
$string['typeaiccurl'] = 'URL AICC externe';
$string['typeexternal'] = 'Manifeste DEFFINUM externe';
$string['typelocal'] = 'Package téléchargé';
$string['typelocalsync'] = 'Package téléchargé';
$string['undercontent'] = 'Sous le contenu';
$string['unziperror'] = 'Une erreur s\'est produite lors de la décompression du package';
$string['updatefreq'] = 'Fréquence de mise à jour automatique';
$string['updatefreq_error'] = 'La fréquence de mise à jour automatique ne peut être définie que lorsque le fichier de package est hébergé à l\'extérieur';
$string['updatefreq_help'] = 'Cela permet de télécharger et de mettre à jour automatiquement le package externe';
$string['updatefreqdesc'] = 'Cette préférence définit la fréquence de mise à jour automatique par défaut d\'une activité';
$string['validateadeffinum'] = 'Valider un package';
$string['validation'] = 'Résultat de la validation';
$string['validationtype'] = 'Cette préférence définit la bibliothèque DOMXML utilisée pour valider le Manifeste DEFFINUM. Si vous ne savez pas, laissez le choix sélectionné.';
$string['value'] = 'Valeur';
$string['versionwarning'] = 'La version du manifeste est antérieure à 1.3, avertissement à la balise {$a->tag}';
$string['viewallreports'] = 'Voir les rapports pour {$a} tentatives';
$string['viewalluserreports'] = 'Voir les rapports pour {$a} utilisateurs';
$string['whatgrade'] = 'Noter les tentatives';
$string['whatgrade_help'] = 'Si plusieurs tentatives sont autorisées, ce paramètre spécifie si la note la plus élevée, la moyenne (moyenne), la première ou la dernière tentative complétée est enregistrée dans le carnet de notes. L\'option de la dernière tentative complétée n\'inclut pas les tentatives avec un statut \'échoué\'.

Remarques sur la gestion des tentatives multiples :

* L\'option de démarrer une nouvelle tentative est fournie par une case à cocher au-dessus du bouton Entrer sur la page de structure du contenu, alors assurez-vous de fournir un accès à cette page si vous souhaitez autoriser plus d\'une tentative.
* Certains packages DEFFINUM sont intelligents à propos des nouvelles tentatives, beaucoup ne le sont pas. Cela signifie que si l\'apprenant ré-entre dans une tentative existante, si le contenu DEFFINUM n\'a pas de logique interne pour éviter de remplacer les tentatives précédentes, elles peuvent être remplacées, même si la tentative était \'terminée\' ou \'passée\'.
* Les paramètres "Forcer terminé", "Forcer une nouvelle tentative" et "Verrouiller après la dernière tentative" fournissent également une gestion supplémentaire des tentatives multiples.';
$string['whatgradedesc'] = 'Si la note la plus élevée, la moyenne (moyenne), la première ou la dernière tentative complétée est enregistrée dans le carnet de notes si plusieurs tentatives sont autorisées.';
$string['width'] = 'Largeur';
$string['window'] = 'Fenêtre';
$string['youmustselectastatus'] = 'Vous devez sélectionner un statut à exiger';

// Deprecated since Moodle 4.3.
$string['completionscorerequired_help'] = 'Activer ce paramètre obligera un utilisateur à avoir au moins le score minimum entré pour être marqué comme terminé dans cette activité DEFFINUM, ainsi que toute autre exigence de terminaison d\'activité.';

// Langue personnalisée deffinum.
$string['resource'] = 'Fichier ressource accessible sans connexion';
$string['resource_help'] = 'Le fichier ressource est un fichier zip (ou pif) contenant des fichiers de définition de cours DEFFINUM/AICC.';
$string['resourcefile'] = 'Aucun fichier ressource spécifié';
$string['resourcehdr'] = 'Ressource';
$string['resourceurl'] = 'URL';
$string['resourceurl_help'] = 'Ce paramètre permet de spécifier une URL pour le package DEFFINUM au lieu de choisir un fichier via le sélecteur de fichiers.';
$string['filenotfound'] = 'Fichier non trouvé, désolé.';
$string['deffinumsettings'] = 'Paramètres spécifiques de DEFFINUM.';
$string['alloweddomains'] = 'Domaines autorisés';
$string['complete']  = 'Terminé';
$string['customtype'] = 'Type';
$string['customtype_360'] = '360';
$string['customtype_augmented_reality'] = 'Réalité augmentée';
$string['customtype_virtual_reality'] = 'Réalité virtuelle';
$string['customtype_serious_game'] = 'Jeu sérieux';
$string['virtual_reality_instructions'] = 'Voici le lien de téléchargement vers le module de Réalité Virtuelle à déposer dans votre casque {$a->downloadlink}.<br>Une fois le module lancé, vous serez invité à vous connecter avec vos identifiants Moodle en indiquant le code suivant <strong style="font-size: 120%">{$a->scoid}</strong> afin de suivre votre progression.';
$string['visiturl'] = 'URL de la visite';
$string['domainnotallowed'] = 'Domaine non autorisé';
$string['privacy:metadata:deffinumuserjsonra'] = 'User Json info for RA';
$string['privacy:metadata:deffinumuserjsonrv'] = 'User Json info for RV';
$string['privacy:metadata:deffinumuserjsonsg'] = 'User Json info for SG';
$string['privacy:metadata:deffinumuserjson360'] = 'User Json info for 360';
$string['vrurl'] = 'Lien de la ressource';
