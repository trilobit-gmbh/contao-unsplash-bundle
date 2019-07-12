<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-unsplash-bundle
 */

// Load language file(s)
System::loadLanguageFile('tl_unsplash');

// Load data container
Controller::loadDataContainer('tl_unsplash');

/*
 * Table tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_unsplash']['fields'], $GLOBALS['TL_DCA']['tl_user']['fields']);

$GLOBALS['TL_DCA']['tl_unsplash']['palettes']['default'] = str_replace('unsplash_filter_legend', 'unsplash_legend', $GLOBALS['TL_DCA']['tl_unsplash']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_unsplash']['palettes']['default'] = str_replace('order', 'priority', $GLOBALS['TL_DCA']['tl_unsplash']['palettes']['default']);

foreach ($GLOBALS['TL_DCA']['tl_user']['palettes'] as $key => $value) {
    $GLOBALS['TL_DCA']['tl_user']['palettes'][$key] = str_replace(
        ';{password_legend',
        ';'.$GLOBALS['TL_DCA']['tl_unsplash']['palettes']['default'].';{password_legend',
        $value
    );
}
