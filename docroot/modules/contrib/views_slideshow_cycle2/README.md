Submodule for [Views Slideshow](https://drupal.org/project/views_slideshow) which uses [Cycle2](http://jquery.malsup.com/cycle2/) library.
Main goal is to provide simple RESPONSIVE slideshow.

If you want to use local library use following in "repositories" section of your root composer.json:

    {
        "type": "composer",
        "url": "https://asset-packagist.org"
    },
by default module uses remote library. Change settings at /config/media/views_slideshow_cycle2