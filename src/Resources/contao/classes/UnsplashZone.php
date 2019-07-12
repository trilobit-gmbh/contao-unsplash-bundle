<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-unsplash-bundle
 */

namespace Trilobit\UnsplashBundle;

use Contao\Config;
use Contao\Controller;
use Contao\Dbafs;
use Contao\Environment;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\System;

/**
 * Class UnsplashZone.
 *
 * @author trilobit GmbH <https://github.com/trilobit-gmbh>
 */
class UnsplashZone extends FileUpload
{
    /**
     * Check the uploaded files and move them to the target directory.
     *
     * @param string $strTarget
     *
     * @throws \Exception
     *
     * @return array
     */
    public function uploadTo($strTarget)
    {
        // Prepare file data
        $arrApiData = Helper::getCacheData(Input::post('tl_unsplash_cache'));

        $arrApiDataHighResolution = [];

        $strImageSource = 'download_location';

        if (empty($arrApiData)) {
            Message::addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            $this->reload();
        }

        if ('' === $strTarget || \Validator::isInsecurePath($strTarget)) {
            throw new \InvalidArgumentException('Invalid target path '.$strTarget);
        }

        $blnImageSource = true;

        foreach ($arrApiData['results'] as $value) {
            if (!\in_array((string) $value['id'], Input::post('tl_unsplash_imageIds'), true)) {
                continue;
            }

            $arrPathParts = [
                'extension' => 'jpg',
                'basename' => $value['alt_description'].'-'.$value['id'],
            ];

            // Sanitize the filename
            try {
                $arrPathParts['basename'] = str_replace(' ', '-', \StringUtil::sanitizeFileName($arrPathParts['basename']));
            } catch (\InvalidArgumentException $e) {
                \Message::addError($GLOBALS['TL_LANG']['ERR']['filename']);
                $this->blnHasError = true;

                continue;
            }

            $strFileNameNew = $strTarget.'/'.$arrPathParts['basename'].'.'.$arrPathParts['extension'];

            $arrApiData['id'][$value['id']] = [
                'files' => [
                    'contao' => $strFileNameNew,
                ],
                'values' => $value,
            ];

            $arrApiData['id'][$value['id']]['files']['download'] = json_decode(file_get_contents($value['links'][$strImageSource].'?client_id='.$arrApiData['__api__']['key']), true)['url'];
        }

        // Upload the files
        $maxlength_kb = $this->getMaximumUploadSize();
        $maxlength_kb_readable = $this->getReadableSize($maxlength_kb);

        $maxImageWidth = Config::get('imageWidth');
        $maxImageHeight = Config::get('imageHeight');
        $arrUploaded = [];

        $arrLanguages = \Contao\Database::getInstance()
                ->prepare("SELECT COUNT(language) AS language_count, language FROM tl_page WHERE type='root' AND published=1 GROUP BY language ORDER BY language_count DESC")
                ->limit(1)
                ->execute()
                ->fetchAllAssoc();

        if (empty($arrLanguages[0]['language'])) {
            $arrLanguages[0]['language'] = 'en';
        }

        foreach (Input::post('tl_unsplash_imageIds') as $value) {
            $strFileTmp = 'system/tmp/'.md5(uniqid(mt_rand(), true));
            $strFileDownload = $arrApiData['id'][$value]['files']['download'].'&w='.$maxImageWidth.'&h='.$maxImageHeight;
            $strNewFile = $arrApiData['id'][$value]['files']['contao'];

            /*
            // get files
            $stream = file_get_contents($strFileDownload);

            $fileHandle = fopen(TL_ROOT.'/'.$strFileTmp, 'w');

            fwrite($fileHandle, $stream);
            fclose($fileHandle);
            */

            // file handle
            $fileHandle = fopen(TL_ROOT.'/'.$strFileTmp, 'w');

            // get file: curl
            $objCurl = curl_init($strFileDownload);

            curl_setopt($objCurl, CURLOPT_HEADER, false);
            curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($objCurl, CURLOPT_BINARYTRANSFER, true);

            curl_setopt($objCurl, CURLOPT_USERAGENT, 'Contao Pixabay API');
            curl_setopt($objCurl, CURLOPT_COOKIEJAR, TL_ROOT.'/system/tmp/curl.cookiejar.txt');
            curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($objCurl, CURLOPT_ENCODING, '');
            curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($objCurl, CURLOPT_AUTOREFERER, true);
            curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, false);    // required for https urls
            curl_setopt($objCurl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($objCurl, CURLOPT_TIMEOUT, 30);
            curl_setopt($objCurl, CURLOPT_MAXREDIRS, 10);

            $stream = curl_exec($objCurl);
            $returnCode = curl_getinfo($objCurl, CURLINFO_HTTP_CODE);

            // write
            fwrite($fileHandle, $stream);
            fclose($fileHandle);

            curl_close($objCurl);

            // move file to target
            $this->import('Files');

            // Set CHMOD and resize if neccessary
            if ($this->Files->rename($strFileTmp, $strNewFile)) {
                $this->Files->chmod($strNewFile, Config::get('defaultFileChmod'));

                $objFile = Dbafs::addResource($strNewFile);

                $objFile->meta = serialize([
                    $arrLanguages[0]['language'] => [
                        'title' => 'ID: '.$value
                            .' | '
                            .'Tags: '.implode(' ', array_values(array_map(function ($a) {return $a['title']; }, $arrApiData['id'][$value]['values']['photo_tags'])))
                            .' | '
                            .'User: '.$arrApiData['id'][$value]['values']['user']['username']
                            .' | '
                            .'Name: '.$arrApiData['id'][$value]['values']['user']['name'],
                        'alt' => 'Unsplash: '.$arrApiData['id'][$value]['values']['links']['html'],
                    ],
                ]);

                $objFile->save();

                // Notify the user
                Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['MSC']['fileUploaded'], $strNewFile));

                System::log('File "'.$strNewFile.'" has been uploaded', __METHOD__, TL_FILES);

                // Resize the uploaded image if necessary
                $this->resizeUploadedImage($strNewFile);

                $arrUploaded[] = $strNewFile;
            }
        }

        if (empty($arrUploaded)) {
            Message::addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            $this->reload();
        }

        $this->blnHasError = false;

        return $arrUploaded;
    }

    public function generateMarkup()
    {
        Controller::loadLanguageFile('tl_unsplash');

        $arrCache = Helper::getCacheData(Input::get('cache'));
        $arrApiParameter = Helper::getConfigData()['api'];

        $arrGlobalsConfig = $GLOBALS['TL_CONFIG'];

        $unsplash_search = '';
        $blnUnsplashCache = false;

        if (\count($arrCache)) {
            $blnUnsplashCache = true;
            $unsplash_search = $arrCache['__api__']['parameter']['query'];
        }

        $this->import('BackendUser', 'User');

        foreach ($arrApiParameter as $key => $value) {
            $GLOBALS['TL_CONFIG']['unsplash_'.$key] = $this->User->{'unsplash_'.$key};

            if ($blnUnsplashCache
                && isset($arrCache['__api__']['parameter'][$key])
                && '' !== $arrCache['__api__']['parameter'][$key]
            ) {
                if ('bool' === strtolower($value)) {
                    $GLOBALS['TL_CONFIG']['unsplash_'.$key] = 1;
                } elseif ('int' === strtolower($value)) {
                    $GLOBALS['TL_CONFIG']['unsplash_'.$key] = \intval($arrCache['__api__']['parameter'][$key], 10);
                } else {
                    $GLOBALS['TL_CONFIG']['unsplash_'.$key] = $arrCache['__api__']['parameter'][$key];
                }
            }
        }

        // Generate the markup
        $return = '
<input type="hidden" name="action" value="unsplashupload">

<div id="unsplash_inform">
    <h2>'.$GLOBALS['TL_LANG']['tl_unsplash']['poweredBy'][0].'</h2>
    <br>
    <a href="https://unsplash.com/" target="_blank" rel="noopener noreferrer"><span style="display: inline-block; background-color: #ffffff; padding: 9px 12px; margin: 15px 15px 15px 0"><img src="/bundles/trilobitunsplash/unsplash_logo_full.svg" height="50"></span></a>
    <a href="https://www.trilobit.de" target="_blank" rel="noopener noreferrer"><img src="/bundles/trilobitunsplash/trilobit_gmbh.svg" width="auto" height="50"></a><br>
    <div class="hint"><br><br><span>'.$GLOBALS['TL_LANG']['MSC']['unsplash']['hint'].'</span></div>
</div>

</div></div>

<div id="unsplash_form">
    <fieldset id="pal_unsplash_search_legend" class="tl_box">
        <legend onclick="AjaxRequest.toggleFieldset(this,\'unsplash_search_legend\',\'tl_unsplash\')">'.$GLOBALS['TL_LANG']['tl_unsplash']['unsplash_search_legend'].'</legend>
        <div class="w50 widget">
            <h3>'.$GLOBALS['TL_LANG']['tl_unsplash']['searchTerm'][0].'</h3>
            <input name="unsplash_search" type="text" value="'.$unsplash_search.'" class="tl_text search">
            <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_unsplash']['searchTerm'][1].'</p>
        </div>

        <div class="w50 widget">
            <h3>'.$GLOBALS['TL_LANG']['tl_unsplash']['unsplash']['searchUnsplash'][0].'</h3>
            <button class="tl_submit">'.$GLOBALS['TL_LANG']['MSC']['unsplash']['searchUnsplash'].'</button>
            <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_unsplash']['searchUnsplash'][1].'</p>
        </div>
    </fieldset>

    '.Helper::generateFilterPalette().'

    <fieldset id="pal_unsplash_result_legend" class="tl_box collapsed">
        <legend onclick="AjaxRequest.toggleFieldset(this,\'unsplash_result_legend\',\'tl_unsplash\')">'.$GLOBALS['TL_LANG']['tl_unsplash']['unsplash_result_legend'].'</legend>
        <div class="widget clr" id="unsplash_images">
            <div class="widget"><p>'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</a></div>
        </div>
        <div class="tl_box clr" id="unsplash_pagination">
        </div>
    </fieldset>
</div>

<div><div>

<script>
    window.addEventListener("load", function(event) {
        //$$(\'div.tl_formbody_submit\').addClass(\'invisible\');
    });

    var unsplashImages      = $(\'unsplash_images\');
    var unsplashPagination  = $(\'unsplash_pagination\');
    var unsplashPage        = 1;
    var unsplashPages       = 1;
    var resultsPerPage      = \''.(floor(Config::get('resultsPerPage') / 4) * 4).'\';
    var language            = \''.$GLOBALS['TL_LANGUAGE'].'\';
    var strHtmlEmpty        = \'<div class="widget"><p>'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['noResult']).'<\/p><\/div>\';
    var strHtmlGoToPage     = \''.sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['goToPage']), '##PAGE##').'\';
    var strHtmlDimensions   = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['dimensions'].'\';
    var strHtmlDescription  = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['description'].'\';
    var strHtmlLikes        = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['likes'].'\';
    var strHtmlUsername     = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['username'].'\';
    var strHtmlName         = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['name'].'\';
    var strHtmlBio          = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['bio'].'\';
    var strHtmlLocation     = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['location'].'\';
    var strHtmlTwitter      = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['twitter'].'\';
    var strHtmlInstagram    = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['instagram'].'\';
    var strHtmlCachedResult = \''.$GLOBALS['TL_LANG']['MSC']['unsplash']['cachedResult'].'\';
    var blnAutoSearch       = \''.($blnUnsplashCache ? 'true' : 'false').'\';

    function unsplashGoToPage(page)
    {
        return strHtmlGoToPage.replace("##PAGE##", page);
    }

    function unsplashImagePagination(totalHits)
    {
        var paginationLinks = 7;
        var strHtmlPagination;
        var firstOffset;
        var lastOffset;
        var firstLink;
        var lastLink;

        // get pages
        unsplashPages = Math.ceil(totalHits / resultsPerPage);

        // get links
        paginationLinks = Math.floor(paginationLinks / 2);

        firstOffset = unsplashPage - paginationLinks - 1;

        if (firstOffset > 0) firstOffset = 0;

        lastOffset = unsplashPage + paginationLinks - unsplashPages;

        if (lastOffset < 0) lastOffset = 0;

        firstLink = unsplashPage - paginationLinks - lastOffset;

        if (firstLink < 1) firstLink = 1;

        lastLink = unsplashPage + paginationLinks - firstOffset;

        if (lastLink > unsplashPages) lastLink = unsplashPages;

        // html: open pagination container
        strHtmlPagination = \'<div class="pagination">\'
            + \'<p>'.preg_replace('/^(.*?)%s(.*?)%s(.*?)$/', '$1\' + unsplashPage + \'$2\' + unsplashPages + \'$3', $GLOBALS['TL_LANG']['MSC']['totalPages']).'<\/p>\'
            + \'<ul>\'
            ;

        // html: previous
        if (unsplashPage > 1)
        {
            strHtmlPagination += \'<li class="first">\'
                + \'<a href="#" onclick="return unsplashSearchUpdate(1);" class="first" title="\' + unsplashGoToPage(1) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['first']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                + \'<li class="previous">\'
                + \'<a href="#" onclick="return unsplashSearchUpdate(unsplashPage-1);" class="previous" title="\' + unsplashGoToPage(unsplashPage-1) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['previous']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                ;
        }

        // html: links
        if (unsplashPages > 1)
        {
            for (i=firstLink; i<=lastLink; i++)
            {
                if (i == unsplashPage)
                {
                    strHtmlPagination += \'<li><span class="active">\' + unsplashPage + \'<\/span><\/li>\'
                }
                else
                {
                    strHtmlPagination += \'<li><a href="#" onclick="return unsplashSearchUpdate(\' + i + \');" class="link" title="\' + unsplashGoToPage(i) + \'">\' + i + \'<\/a><\/li>\'
                }
            }
        }

        // html: next
        if (unsplashPage < unsplashPages)
        {
            strHtmlPagination += \'<li class="next">\'
                + \'<a href="#" onclick="return unsplashSearchUpdate(unsplashPage+1);" class="next" title="\' + unsplashGoToPage(unsplashPage+1) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['next']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                + \'<li class="last">\'
                + \'<a href="#" onclick="return unsplashSearchUpdate(\' + unsplashPages + \');" class="last" title="\' + unsplashGoToPage(unsplashPages) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['last']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                ;
        }

        // html: close pagination container
        strHtmlPagination += \'<\/ul>\'
            + \'<\/div>\'
            ;

        unsplashPagination.innerHTML = strHtmlPagination;
    }

    function unsplashImageList(unsplashJsonData)
    {
        var strHtmlImages;

        unsplashImages.innerHTML = strHtmlEmpty;

        if (unsplashJsonData.total > 0)
        {
            strHtmlImages = \'\'
                + \'<input type="hidden" name="tl_unsplash_images" value="">\'
                + \'<input type="hidden" name="tl_unsplash_imageIds" value="">\'
                + \'<input type="hidden" name="tl_unsplash_cache" value="\' + unsplashJsonData.__api__.cache + \'">\'
                + \'<div class="widget">\'
                + \'<h3>\' + unsplashJsonData.total + \' '.$GLOBALS['TL_LANG']['MSC']['unsplash']['searchUnsplashResult'].'<\/h3>\'
                + \'<\/div>\'
                + \'<div class="flex-container">\'
                ;

            for (var key in unsplashJsonData.results)
            {
                if (unsplashJsonData.results.hasOwnProperty(key))
                {
                    var value = unsplashJsonData.results[key];

                    strHtmlImages += \'\'
                        + \'<div class="widget preview" id="unsplash_preview_\' + key + \'">\'
                            + \'<label for="unsplash_image_\' + key + \'">\'
                            + \'<div class="image-container" style="background-image:url(\' + value.urls.small + \')">\'
                                + \'<a href="\' + value.links.html + \'" \'
                                    + \' title="\' + value.alt_description + \'" \'
                                    + \' target="_blank" rel="noopener noreferrer"\'
                                + \'>\'
                                    + \'<!---<img src="\' + value.urls.small + \'" style="display:none">--->\'
                                + \'<\/a>\'
                            + \'<\/div>\'
                            + \'<br>\'
                            + \'<input type="checkbox" id="unsplash_image_\' + key + \'" value="\' + value.id + \'" name="tl_unsplash_imageIds[]" onclick="$$(\\\'#unsplash_preview_\' + key + \'\\\').toggleClass(\\\'selected\\\')">\'
                                + \'ID: <strong>\' + value.id + \'<\/strong>\'
                            + \'<table class="tl_show">\'
                                + \'<tbody>\'
                                    + \'<tr>\'
                                        + \'<td class="tl_bg"><span class="tl_label">\' + strHtmlDimensions + \': <\/span><\/td>\'
                                        + \'<td class="tl_bg">\' + value.width + \' x \' + value.height + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td><span class="tl_label">\' + strHtmlDescription + \': <\/span><\/td>\'
                                        + \'<td>\' + (value.description !== null ? value.description : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td class="tl_bg"><span class="tl_label">\' + strHtmlLikes + \': <\/span><\/td>\'
                                        + \'<td class="tl_bg">\' + (value.likes !== null ? value.likes : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td><span class="tl_label">\' + strHtmlUsername + \': <\/span><\/td>\'
                                        + \'<td>\' + (value.user.username !== null ? value.user.username : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td class="tl_bg"><span class="tl_label">\' + strHtmlName + \': <\/span><\/td>\'
                                        + \'<td class="tl_bg">\' + (value.user.name !== null ? value.user.name : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td><span class="tl_label">\' + strHtmlBio + \': <\/span><\/td>\'
                                        + \'<td>\' + (value.user.bio !== null ? value.user.bio : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td class="tl_bg"><span class="tl_label">\' + strHtmlLocation + \': <\/span><\/td>\'
                                        + \'<td class="tl_bg">\' + (value.user.location !== null ? value.user.location : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td><span class="tl_label">\' + strHtmlTwitter + \': <\/span><\/td>\'
                                        + \'<td>\' + (value.user.twitter_username !== null ? value.user.twitter_username : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td class="tl_bg"><span class="tl_label">\' + strHtmlInstagram + \': <\/span><\/td>\'
                                        + \'<td class="tl_bg">\' + (value.user.instagram_username !== null ? value.user.instagram_username : \'-/-\') + \'<\/td>\'
                                    + \'<\/tr>\'
                                + \'<\/tbody>\'
                            + \'<\/table>\'
                            + \'<\/label>\'
                        + \'<\/div>\'
                        ;
                }
            }
            
            strHtmlImages += \'<\/div>\';

            strHtmlImages += (unsplashJsonData.__api__.cachedResult ? \'<br clear="all"><div class="widget"><p class="tl_help tl_tip">\' + strHtmlCachedResult + \'<\/p><\/div>\' : \'\');

            unsplashImages.innerHTML = strHtmlImages;
            unsplashImagePagination(unsplashJsonData.total);

            new Fx.Scroll(window).toElement(\'pal_unsplash_result_legend\');
        }
    }

    function unsplashException(unsplashJsonData)
    {
        unsplashImages.innerHTML = \'<br clear="all">\'
            + \'<div class="widget tl_error">\'
                + \'<p>\'
                    + \'<strong>#\' + unsplashJsonData.__api__.exceptionId + \'</strong>\'
                + \'<\/p>\'
                + \'<p>\'
                    + unsplashJsonData.__api__.exceptionMessage
                + \'<\/p>\'
            + \'<\/div>\'
            ;
    }

    function unsplashApi(search)
    {
        unsplashPagination.innerHTML = \'&nbsp;\';
        unsplashImages.innerHTML = \'<div class="spinner"><\/div>\';

        var xhr = new XMLHttpRequest();
        var url =\''.ampersand(Environment::get('script'), true).'/trilobit/unsplash\'
            + \'?query=\'       + encodeURIComponent(search)
            + \'&page=\'        + unsplashPage
            + \'&per_page=\'    + resultsPerPage
            + \'&orientation=\' + $$(\'select[name="unsplash_orientation"] option:selected\').get(\'value\')
            ;
        
        xhr.open(\'GET\', url);
        xhr.onreadystatechange = function()
        {
            if (   this.status == 200
                && this.readyState == 4
            )
            {
                var unsplashJsonData = JSON.parse(this.responseText);

                if (   unsplashJsonData
                    && unsplashJsonData.__api__
                    && unsplashJsonData.__api__.exceptionId
                )
                {
                    unsplashException(unsplashJsonData);
                }
                else
                {
                    unsplashImageList(unsplashJsonData);
                }

                return false;
            }

            var unsplashJsonData = unsplashJsonData || {};
                unsplashJsonData.__api__ = unsplashJsonData.__api__ || {};;

                unsplashJsonData.__api__.exceptionId = this.status;
                unsplashJsonData.__api__.exceptionMessage = \'[ERROR \' + this.status + \'] Please try again...\';

            unsplashException(unsplashJsonData);

        };
        xhr.send();

        return false;
    }

    function unsplashSearchUpdate(page)
    {
        if (page !== undefined)
        {
            unsplashPage = page;
        }

        var search = $$(\'input[name="unsplash_search"]\').get(\'value\');

        $$(\'#pal_unsplash_result_legend\').removeClass(\'collapsed\');
        $$(\'#pal_unsplash_filter_legend\').addClass(\'collapsed\');

        if (   search === undefined
            || search === \'\'
        )
        {
            unsplashImages.innerHTML = \'\';
            unsplashImages.innerHTML = strHtmlEmpty;

            return false;
        }

        unsplashApi(search);
        

        return false;
    }

    function unsplashSearch()
    {
        $$(\'#unsplash_form button.tl_submit\').addEvent(\'click\', function(e) {
            e.stop();

            return unsplashSearchUpdate(1);            
        });
    }

    unsplashSearch();
    
    if (blnAutoSearch) unsplashSearchUpdate('.$GLOBALS['TL_CONFIG']['page'].');
</script>';

        $GLOBALS['TL_CONFIG'] = $arrGlobalsConfig;

        return $return;
    }
}

class_alias(UnsplashZone::class, 'UnsplashZone');
