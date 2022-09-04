<?php
   $version="5.29";
   $rev="?v".$version;
    
    /* When client loads fw profile editor, his browser loads bunch of files
    (images, javascripts, etc) and caches them locally.
    Your server is configured to somehow force the browser to cache the
    files longer than it should, so if any of the javascripts was modified
    on server, the client's browser in many cases kept using the old
    version (which it has already cached locally) and it did not reload
    the new javascript version from your server until the user presses
    Ctrl+F5, which is very inconvenient.

    For that reason I added $rev variable, and all javascripts are loaded
    using URL such as
    /loadsave.js?12345678

    Since $rev is a random number each time, this ensures that next time
    the URL will be always different and that forces the browser to reload
    the javascript from server again, each time the user opens fw profile
    editor.

    If you are sure that the javascripts on server will never change, then
    you can remove this line, and $rev will be set to version number. This
    way, all javascripts will be loaded with parameter such as
    loadsave.js?5.00 all the time, thus browser can cache them. If you
    modify any javascript fike, you have to increment version number, and
    everything will be fine as well.

    This random rev was there only for me, because during development I
    needed to ensure each time I access the editor, it forces to reload
    files from server's disk, without me needing to update version number
    each time I change something.

    If you leave this line active, nothing bad happens, only each time
    user opens fw profile editor in his browser, all javascripts will be
    reloaded from the server. This does not hurt, since it is just few
    kilobytes of code.

    If you remove or comment out this line, it will be somehow more
    optimal, but then you must ensure that if you change any file on the
    server, you always have to increment the version number a bit.   */
   $rev="?".mt_rand(1,111111111);   // comment out on production

   if ($_REQUEST['rev']=='1') $rev="";
?>
