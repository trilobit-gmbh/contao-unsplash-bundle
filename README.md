TrilobitUnsplashBundle
==============================================

Mit der Unsplash Erweiterung können sie über die Dateiverwaltung von Contao Bilder oder Fotos von der freien Bilddatenbank Unsplash herunterladen. Um Unsplash benutzen zu können, benötigen sie eine API-Key, den sie nach der Registrierung bei Unsplash anfordern können. Sie können außerdem Voreinstellungen für die Unsplash-Suche in der Benutzerverwaltung festlegen.


With the Unsplash extension you can download images or photos from the free image database Unsplash via the file management of Contao. In order to use Unsplash, you will need an API key that you can request after registering on the Unsplash website. You can also set preferences for the Unsplash search in the User Management.


Backend Ausschnitt
------------

![Backend Ausschnitt](docs/images/unsplash_bundle.png?raw=true "TrilobitUnsplashBundle")


Installation
------------

Install the extension via composer: [trilobit-gmbh/contao-unsplash-bundle](https://packagist.org/packages/trilobit-gmbh/contao-unsplash-bundle).

And add the following code (with the API-Key from the Unsplash Website) to the config.yml of your project.

    contao:
      localconfig:
        unsplashApiKey: 'Your API-Key'


Compatibility
-------------

- Contao version ~4.4
