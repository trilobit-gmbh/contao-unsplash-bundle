<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-unsplash-bundle
 */

// Load language file(s)
System::loadLanguageFile('tl_unsplash');

/*
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace(
    ';{proxy_legend',
    ';{unsplash_legend:hide},unsplashApiKey,unsplashImageSource;{proxy_legend',
    $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']
);

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['unsplashApiKey'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_unsplash']['unsplashApiKey'],
    'inputType' => 'text',
    'eval' => ['tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['unsplashHighResolution'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_unsplash']['unsplashHighResolution'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['unsplashImageSource'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_unsplash']['unsplashImageSource'],
    'inputType' => 'select',
    'options_callback' => ['tl_settings_unsplash', 'getImageSource'],
    'reference' => &$GLOBALS['TL_LANG']['tl_unsplash']['options']['image_source'],
    'eval' => ['chosen' => true, 'tl_class' => 'clr w50'],
];

/**
 * Class tl_settings_unsplash.
 */
class tl_settings_unsplash extends Backend
{
    /**
     * tl_settings_unsplash constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getImageSource(DataContainer $dc)
    {
        return array_keys(\Trilobit\UnsplashBundle\Helper::getConfigData()['imageSource']);
    }
}
