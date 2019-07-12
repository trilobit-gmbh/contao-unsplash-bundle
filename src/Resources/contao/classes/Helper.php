<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-unsplash-bundle
 */

namespace Trilobit\UnsplashBundle;

use Contao\Controller;
use Contao\DC_File;
use Contao\File;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Helper.
 *
 * @author trilobit GmbH <https://github.com/trilobit-gmbh>
 */
class Helper
{
    /**
     * @return string
     */
    public static function getVendowDir()
    {
        return \dirname(__DIR__);
    }

    /**
     * @return mixed
     */
    public static function getConfigData()
    {
        $strYml = file_get_contents(self::getVendowDir().'/../config/config.yml');

        return Yaml::parse($strYml)['trilobit']['unsplash'];
    }

    /**
     * @param $strCacheFile
     *
     * @throws \Exception
     *
     * @return array|mixed
     */
    public static function getCacheData($strCacheFile)
    {
        // prepare cache controll
        $strCachePath = StringUtil::stripRootDir(System::getContainer()->getParameter('kernel.cache_dir'));
        $strCacheFile = $strCachePath.'/contao/unsplash/'.$strCacheFile.'.json';

        // Load the cached result
        if (file_exists(TL_ROOT.'/'.$strCacheFile)) {
            $objFile = new File($strCacheFile);

            return json_decode($objFile->getContent(), true);
        }

        return [];
    }

    /**
     * @return mixed
     */
    public static function generateFilterPalette()
    {
        Controller::loadLanguageFile('tl_unsplash');
        Controller::loadDataContainer('tl_unsplash');

        $objUnsplash = new DC_File('tl_unsplash');

        return preg_replace('/^(.*?)<fieldset (.*)<\/fieldset>(.*)?$/si', '<fieldset $2</fieldset>', $objUnsplash->edit());
    }
}
