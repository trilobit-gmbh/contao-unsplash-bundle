<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-unsplash-bundle
 */

// global operations
$GLOBALS['TL_LANG']['tl_unsplash']['operationAddFromUnsplash'][0] = 'Unsplash';
$GLOBALS['TL_LANG']['tl_unsplash']['operationAddFromUnsplash'][1] = 'Unsplash';

// legends
$GLOBALS['TL_LANG']['tl_unsplash']['unsplash_search_legend'] = 'Suche';
$GLOBALS['TL_LANG']['tl_unsplash']['unsplash_filter_legend'] = 'Filter';
$GLOBALS['TL_LANG']['tl_unsplash']['unsplash_result_legend'] = 'Suchergebnisse';
$GLOBALS['TL_LANG']['tl_unsplash']['unsplash_legend'] = 'Unsplash-Einstellungen';

$GLOBALS['TL_LANG']['tl_user']['unsplash_legend'] = $GLOBALS['TL_LANG']['tl_unsplash']['unsplash_legend'];
$GLOBALS['TL_LANG']['tl_settings']['unsplash_legend'] = $GLOBALS['TL_LANG']['tl_unsplash']['unsplash_legend'];

// fields
$GLOBALS['TL_LANG']['tl_unsplash']['unsplashApiKey'][0] = 'Unsplash API-Key';
$GLOBALS['TL_LANG']['tl_unsplash']['unsplashApiKey'][1] = 'Bitte geben Sie hier Ihren Unsplash-API-Key ein. Weitere Informationen unter <a href="https://unsplash.com/documentation/" target="_blank" rel="noopener noreferrer"><u>unsplash.com/api/docs/</u></a>.';
$GLOBALS['TL_LANG']['tl_unsplash']['unsplashApiUrl'][0] = 'Unsplash API-URL';
$GLOBALS['TL_LANG']['tl_unsplash']['unsplashApiUrl'][1] = 'Bitte geben Sie hier Ihren Unsplash-API-URL ein. Weitere Informationen unter <a href="https://unsplash.com/documentation/" target="_blank" rel="noopener noreferrer"><u>unsplash.com/api/docs/</u></a>.';

$GLOBALS['TL_LANG']['tl_unsplash']['fileupload'][0] = 'Datei-Upload Unsplash';
$GLOBALS['TL_LANG']['tl_unsplash']['fileupload'][1] = 'Datei-Upload Unsplash';
$GLOBALS['TL_LANG']['tl_unsplash']['poweredBy'][0] = 'Unsplash für Contao &mdash; zur Verfügung gestellt von:';
$GLOBALS['TL_LANG']['tl_unsplash']['poweredBy'][1] = 'Unsplash für Contao &mdash; zur Verfügung gestellt von...';
$GLOBALS['TL_LANG']['tl_unsplash']['searchTerm'][0] = 'Suche';
$GLOBALS['TL_LANG']['tl_unsplash']['searchTerm'][1] = 'Bitte geben sie hier ihren Suchbegriff bzw. ihre Suchbegriffe ein.';
$GLOBALS['TL_LANG']['tl_unsplash']['searchUnsplash'][0] = '';
$GLOBALS['TL_LANG']['tl_unsplash']['searchUnsplash'][1] = 'Klicken sie auf \'Bildersuche starten\', um die Suche mit ihrem Suchbegriff zu starten.';
$GLOBALS['TL_LANG']['tl_unsplash']['orientation'][0] = 'Ausrichtung';
$GLOBALS['TL_LANG']['tl_unsplash']['orientation'][1] = 'Ausrichtung.';

$GLOBALS['TL_LANG']['MSC']['unsplash']['cachedResult'] = 'Cache-Ergebnis; Um die Unsplash-API für alle schnell zu halten, werden Anfragen 24 Stunden zwischengespeichert.';
$GLOBALS['TL_LANG']['MSC']['unsplash']['searchUnsplash'] = 'Bildersuche starten';
$GLOBALS['TL_LANG']['MSC']['unsplash']['searchUnsplashResult'] = 'Treffer';

$GLOBALS['TL_LANG']['MSC']['unsplash']['dimensions'] = 'Abmessungen';
$GLOBALS['TL_LANG']['MSC']['unsplash']['description'] = 'Beschreibung';
$GLOBALS['TL_LANG']['MSC']['unsplash']['likes'] = 'Likes';
$GLOBALS['TL_LANG']['MSC']['unsplash']['username'] = 'Benutzer';
$GLOBALS['TL_LANG']['MSC']['unsplash']['name'] = 'Name';
$GLOBALS['TL_LANG']['MSC']['unsplash']['bio'] = 'Bio';
$GLOBALS['TL_LANG']['MSC']['unsplash']['location'] = 'Standort';
$GLOBALS['TL_LANG']['MSC']['unsplash']['twitter'] = 'twitter';
$GLOBALS['TL_LANG']['MSC']['unsplash']['instagram'] = 'Instagram';
$GLOBALS['TL_LANG']['MSC']['unsplash']['tags'] = 'Tags';

$GLOBALS['TL_LANG']['MSC']['unsplash']['hint'] = '<p>Fotos zur Verfügung gestellt von <a href="https://unsplash.com/" target="_blank" rel="noopener noreferrer"><u>Unsplash</u></a>.</p>'
    .'<p><strong>Richtlinien</strong></p>'
    .'<ul>'
    .'<li>Standardmäßig ist die API beschränkt auf 5.000 Abfragen pro Stunde.</li>'
    .'<li>Nennen Sie immer unseren Fotografen wenn möglich (z.B. "Foto von John Doe auf Pexels" mit einem Link zur Fotoseite auf Pexels).</li>'
    .'</ul>'
    .'<br>'
    .'<p>API-Dokumentation: <a href="https://unsplash.com/developer/" target="_blank" rel="noopener noreferrer"><u>unsplash.com/developer/</u></a></p>'
    .'<p>Beachten Sie bitte die <strong>Unsplash <a href="https://unsplash.com/terms" target="_blank" rel="noopener noreferrer"><u>Nutzungsbedingungen</u></a></strong>!</p>'
;

$GLOBALS['TL_LANG']['tl_unsplash']['options']['orientation']['all'] = 'Jede Ausrichtung';
$GLOBALS['TL_LANG']['tl_unsplash']['options']['orientation']['landscape'] = 'Horizontal';
$GLOBALS['TL_LANG']['tl_unsplash']['options']['orientation']['portrait'] = 'Vertikal';
$GLOBALS['TL_LANG']['tl_unsplash']['options']['orientation']['squarish'] = 'Quadratisch';

$GLOBALS['TL_LANG']['ERR']['imageSourceNotAvailable'] = 'Die gewünschte Bildquelle "%s" ist nicht mit diesem API-Key verfügbar.<br>Weitere Informationen unter <a href="https://unsplash.com/documentation/" target="_blank" rel="noopener noreferrer"><u>unsplash.com/api/docs/</u></a>.';
