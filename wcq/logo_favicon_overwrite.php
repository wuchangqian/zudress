<?php

function overwrite_favicon_ico(){
    /*

       favicon.ico 最原始的大小是16X16
    */

    $fvs = array(
    '../downloader/favicon.ico'
    ,'../errors/default/images/favicon.ico'
    ,'../errors/enterprise/images/favicon.ico'
    ,'../favicon.ico'
    ,'../skin/adminhtml/default/default/favicon.ico'
    ,'../skin/frontend/base/default/favicon.ico'
    ,'../skin/frontend/default/new/favicon.ico'
    ,'../skin/frontend/enterprise/default/favicon.ico'
    ,'../skin/frontend/enterprise/iphone/favicon.ico'
    ,'../skin/frontend/enterprise/mobile/favicon.ico'
    ,'../skin/install/default/default/favicon.ico'
    );

    $fin = base64_decode('AAABAAEAEBAAAAEACABoBQAAFgAAACgAAAAQAAAAIAAAAAEACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeXn/AIOD/wCdnfsAdXX8AD4+/gBJSf8AsbH/ALy8/wAICJgAAABKADU1VABJSVQAMjJUAFVVtwAODv8AAADRAH9//AAAAJoAAQGBAC0tfQA6OkMAAwMFAAcHEQArK7kAAADoAAAA3QB4eP4AAADcAAAA8QAlJfUALCydABERHAACAioACgq8AAAA7gAAAMoAAQG6AE1N8QAAAPMAAADtAAsLqAAFBR0AAgIoAAAApwAAANkAAADNAAAAtAAAAKQAQ0NDADs7+gAAAP0AAADmAAAAkwAAAAoAAAAXAAAAagAAAJIAAACIAAAAkAAFBXQADAw/AAAA5wAAANsAAAAmAAAABQAAAA0AAAAOAAAAQwAAANAAAAC2AAAAqwAAAKYAAQGvAAAAogAAAJgAAAC5AAAAgQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAATTwAAAAAAAAAAAAAAAAATDU8PAAAAAAAAAAAAAAASUlKSzk8AAAAAAAAAAAAEEVFRkdIEjUAAAAAAAA9Pj9AAEFCQ0JEPD0AAAAxMjM0NTY3ODk6OzA8MQAAACYnHCgpKissLS4vMAAAAAAbDxwdHh8gISIjJCUAAAAAEQgPEhMUFRYXGBkaAAAAAAEHCAkKAAsMDQ4PEAAAAAAAAQECAAAAAwQFBgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP//AAD+fwAA/D8AAPgfAADwDwAA4AcAAMADAACAAQAAgAEAAIABAACAAQAAgAEAAMEDAADjhwAA//8AAP//AAA=');;


    foreach($fvs as $f){
        file_put_contents($f,$fin);
    }

}

function overwrite_logo(){

}