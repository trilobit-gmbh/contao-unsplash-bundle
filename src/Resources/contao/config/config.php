<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-unsplash-bundle
 */

/**
 * Add css.
 */
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/trilobitunsplash/css/backend.css';
}
