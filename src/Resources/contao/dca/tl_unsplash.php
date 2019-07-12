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
 * "Table" tl_unsplash
 */
$GLOBALS['TL_DCA']['tl_unsplash'] = [
    // Config
    'config' => [
        'dataContainer' => 'File',
        'closed' => true,
    ],

    // Palettes
    'palettes' => [
        'default' => '{unsplash_filter_legend},unsplash_orientation',
    ],

    // Fields
    'fields' => [
        'unsplash_orientation' => [
            'label' => &$GLOBALS['TL_LANG']['tl_unsplash']['orientation'],
            'inputType' => 'select',
            'options_callback' => ['tl_unsplash', 'getOrientation'],
            'reference' => &$GLOBALS['TL_LANG']['tl_unsplash']['options']['orientation'],
            'eval' => ['chosen' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_unsplash.
 */
class tl_unsplash extends Backend
{
    /**
     * tl_unsplash constructor.
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
    public function getOrientation(DataContainer $dc)
    {
        return array_keys(\Trilobit\UnsplashBundle\Helper::getConfigData()['orientation']);
    }
}
