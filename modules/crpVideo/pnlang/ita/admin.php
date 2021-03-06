<?php
/**
 * crpVideo
 *
 * @copyright (c) 2007, Daniele Conca
 * @link http://noc.postnuke.com/projects/crpvideo Support and documentation
 * @author Daniele Conca <conca dot daniele at gmail dot com>
 * @license GNU/GPL - v.2
 * @package crpVideo
 */

define('_CRPVIDEO', 'Video');
define('_CRPVIDEO_VIDEO', 'Video');
define('_CRPVIDEO_VIDEOS', 'Video');

define('_CRPVIDEO_DISPLAYWRAPPER', 'Mostra informazioni addizionali nella pagina');
define('_CRPVIDEO_GENERAL', 'Impostazioni generali');

// main list
define('_CRPVIDEO_CHANGE_STATUS','Modifica stato');
define('_CRPVIDEO_CHANGE_STATUS_MODIFYING','Cambia lo stato modificando il video');
define('_CRPVIDEO_NOT_SPECIFIED','Non specificato');
define('_CRPVIDEO_PENDING','In attesa');
define('_CRPVIDEO_REJECTED','Rifiutato');
define('_CRPVIDEO_STATUS','Stato');

// creation form
define('_CRPVIDEO_AUTHOR','Autore del video');
define('_CRPVIDEO_CURRENT_FILE','File corrente');
define('_CRPVIDEO_DELETE_FILE','Delete file');
define('_CRPVIDEO_FILE','Video file (.flv o .mp3) - Max');
define('_CRPVIDEO_EXTERNAL','Embed URL (http://)');
define('_CRPVIDEO_EXTERNAL_SOURCE','Sorgente esterno (http://)');
define('_CRPVIDEO_FILEBLANK','(deve essere il nome del video flv in pnmedia/video)');
define('_CRPVIDEO_IMAGE','Immagine video (.gif, .jpg, .png) - Max');
define('_CRPVIDEO_IMAGE_WIDTH','Larghezza delle immagini per i video');
define('_CRPVIDEO_REQUIRED','(*)');
define('_CRPVIDEO_REQUIRED_EXT','Campo obbligatorio');
define('_CRPVIDEO_SHOW_FILE','Visualizza file');
define('_CRPVIDEO_TAGS','Tags');

// config
define('_CRPVIDEO_BROWSER_PATH','URL Path relativo all\'installazione di PostNuke (con slash finale)');
define('_CRPVIDEO_COVER_DIMENSION','Massima dimensione della cover per l\'upload (bytes)');
define('_CRPVIDEO_DISPLAY_EMBED','Visualizza codice embed');
define('_CRPVIDEO_DISPLAY_HEIGHT','Altezza visualizzatore');
define('_CRPVIDEO_FILE_DIMENSION','Massima dimensione del file per l\'upload (bytes)');
define('_CRPVIDEO_GD_AVAILABLE','GD Library');
define('_CRPVIDEO_IMAGES','Immagini');
define('_CRPVIDEO_IMAGE_RESIZE','Le immagini saranno scalate alla larghezza di');
define('_CRPVIDEO_MAIN_ITEMS','Elementi nell\'indice');
define('_CRPVIDEO_MANDATORY_COVER','Cover obbligatoria');
define('_CRPVIDEO_NOTIFICATION_MAIL','Notifiche di inserimenti degli utenti (nessuna se vuoto)');
define('_CRPVIDEO_PLAYER','Player');
define('_CRPVIDEO_PLAYER_HEIGHT','Altezza player');
define('_CRPVIDEO_PLAYER_WIDTH','Larghezza player');
define('_CRPVIDEO_SHARE','Condivisione');
define('_CRPVIDEO_UPLOAD','Upload');
define('_CRPVIDEO_UPLOAD_PATH','Path upload (percorso relativo del server rispetto all\'installazione di PostNuke, deve avere permessi 777)');
define('_CRPVIDEO_USE_BROWSER','crpVideo utilizza il browser');
define('_CRPVIDEO_USE_GD','crpVideo usa le GD Library');
define('_CRPVIDEO_USERLIST_IMAGE','Mostra thumbnails nell\'elenco utenti');
define('_CRPVIDEO_USERLIST_WIDTH','Larghezza thumbnails nell\'elenco utenti');

// error messages
define('_CRPVIDEO_ERROR_IMAGE_FILE_SIZE_TOO_BIG','Dimensioni dell\'immagine non permesse');
define('_CRPVIDEO_ERROR_IMAGE_NO_FILE','Immagine non caricata o non fornita');
define('_CRPVIDEO_ERROR_VIDEO_FILE_SIZE_TOO_BIG','Dimensioni del video non consentite');
define('_CRPVIDEO_ERROR_VIDEO_NO_AUTHOR','Indicare un autore per il video');
define('_CRPVIDEO_ERROR_VIDEO_NO_CATEGORY','Specificare una categoria');
define('_CRPVIDEO_ERROR_VIDEO_NO_CONTENT','Indicare un contenuto per il video');
define('_CRPVIDEO_ERROR_VIDEO_NO_FILE','Video non caricato o non fornito');
define('_CRPVIDEO_ERROR_VIDEO_NO_TITLE','Indicare un titolo per il video');
define('_CRPVIDEO_IMAGE_INVALID_TYPE','Formato di immagine non valido');
define('_CRPVIDEO_VIDEO_INVALID_TYPE','Formato video non permesso');
define('_CRPVIDEO_INVALID_URL','URL non valido');
define('_CRPVIDEO_INVALID_NOTIFICATION','E-mail per le notifiche in formato non valido');

// RSS define
define('_CRPVIDEO_ATOM','ATOM');
define('_CRPVIDEO_RSS','crpVideo feed');
define('_CRPVIDEO_RSS1','RSS 1.0');
define('_CRPVIDEO_RSS2','RSS 2.0');
define('_CRPVIDEO_ENABLE_RSS','Abilita feed RSS');
define('_CRPVIDEO_SHOW_RSS','Mostra link al feed RSS');
define('_CRPVIDEO_USE_RSS','Formato Feed');

// PodCast
define('_CRPVIDEO_PODCAST','crpVideo podcast');
define('_CRPVIDEO_ENABLE_PODCAST','Abilita podcasting');
define('_CRPVIDEO_PODCAST_CATEGORY','Categoria podcast');
define('_CRPVIDEO_PODCAST_DESCRIPTION','Descrizione Podcast');
define('_CRPVIDEO_PODCAST_EDITOR','Editor');
define('_CRPVIDEO_PODCAST_ICATEGORY','Descrizione di categoria');

// Playlist
define('_CRPVIDEO_ENABLE_PLAYLIST','Abilita playlist');
define('_CRPVIDEO_PLAYLIST_BY_CATEGORY','Per categoria');
define('_CRPVIDEO_PLAYLIST_BY_DATE','Per data');
define('_CRPVIDEO_PLAYLIST_BY_UPLOADER','Per uploader');
define('_CRPVIDEO_PLAYLIST_BY_VIEWS','Per visualizzazioni');
define('_CRPVIDEO_PLAYLIST_ITEMS','Elementi nella playlist');
define('_CRPVIDEO_PLAYLIST_POSITION','Posizione');
define('_CRPVIDEO_PLAYLIST_POSITION_BOTTOM','In basso');
define('_CRPVIDEO_PLAYLIST_POSITION_OVER','In alto');
define('_CRPVIDEO_PLAYLIST_POSITION_RIGHT','A destra');
define('_CRPVIDEO_PLAYLIST_SIZE','Dimensione playlist (px)');
define('_CRPVIDEO_PLAYLIST_TYPE','Tipo di playlist');

?>