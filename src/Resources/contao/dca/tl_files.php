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
 * Table tl_files
 */

if ('' !== \Config::get('unsplashApiKey')) {
    $GLOBALS['TL_DCA']['tl_files']['config']['onload_callback'][] = ['tl_files_unsplash', 'setUploader'];

    $GLOBALS['TL_DCA']['tl_files']['list']['global_operations'] = array_merge(
        ['unsplash' => [
            'label' => &$GLOBALS['TL_LANG']['tl_unsplash']['operationAddFromUnsplash'],
            'href' => 'act=paste&mode=move&source=unsplash',
            'class' => 'header_unsplash',
            'icon' => '/bundles/trilobitunsplash/unsplash_symbol.svg',
            'button_callback' => ['tl_files_unsplash', 'unsplash'],
        ]],
        $GLOBALS['TL_DCA']['tl_files']['list']['global_operations']
    );
}

/**
 * Class tl_files_unsplash.
 */
class tl_files_unsplash extends Backend
{
    /**
     * tl_files_unsplash constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * @param $href
     * @param $label
     * @param $title
     * @param $class
     * @param $attributes
     *
     * @return string
     */
    public function unsplash($href, $label, $title, $class, $attributes)
    {
        $canUpload = $this->User->hasAccess('f1', 'fop');
        $canUnsplash = $this->User->hasAccess('unsplash', 'fop');

        return $canUnsplash && $canUpload ? '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'" class="'.$class.'"'.$attributes.'>'.$label.'</a> ' : '';
    }

    public function setUploader()
    {
        if ('move' === \Input::get('act') && 'unsplash' === \Input::get('source')) {
            $this->import('BackendUser', 'User');
            $this->User->uploader = 'Trilobit\UnsplashBundle\UnsplashZone';
        }
    }
}
