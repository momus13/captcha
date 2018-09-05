<?php
$_CONFIG = array(
    "global" => array(
        "Path" => '../', // global path from WWW
        "Error" => 'server/html/error.html', // html file global error
        "Auth" => array(
            "Url" => '/auth', // absolute web path to auth
            "Controller" => 'authsilents', // name of controller for silents test
            "Redirect" => true, // redirect to authorization page or 403
        ),
        "Default" => '/', // web path to default redirect
        "Login" => 'user', // session param, isset == auth
        "NotFound" => 'server/html/404.html', // html file page not found
        "AccessDeny" => 'server/html/403.html', // html file page access deny
        //"cache" => "/memory-fs/" // path to cache file
        "LogLevel" => 3, // Log level: 0 - only critical, 3 - maximum
        "DateFormatLog" => "j.n.y G:i:s", // datetime format
        //"LogPath" => "/var/log/path/name" name and path to log, default ../logs/log.log
    ),
    "default" => array (
        "init" => "init", // start method
        "type" => "class", // class or function
        "config" => true, // send param config to construct class
        "LetterCase" => true, // convert to capital first letter from default class name
        "global" => [ // global param to method init
            "DB", "Session", "Remainder"
        ],
        "MaxDepth" => 10, // Maximum nesting controllers
        "html" => true,
        "lang" => "ru",
        "ParametersInit" => true,
        "first" => "header", // controller name, for always first load from html, or false if not loaded
        "last" => "footer", // controller name, for always last load from html, or false if not loaded
    ),
    "include" => array(
        "session" => array(
            "File" => 'server/modules/preset/session.php', // path to php file class session
            "Class" => 'Session', // class name
            "Type" => 'simple', // session type
            "Required" => "required", // method return list of preset class required
            "Init" => "init", // method return list of preset class required
        ),
        "db" => array( // node received to construct class db
            "Path" => 'server/modules/preset/db', // path to dir DB connector
            "Required" => "required", // method return list of preset class required
            "Init" => "init", // method return list of preset class required
            "Class" => 'DbConnection', // class name
            "Type" => 'pg', // DB type (pg - PostgreSQL, my - mySQL, wa - webAPI, mo - Mock)
            "Host" => 'localhost', // server host
            "Port" => '5432', // port
            "User" => 'user', // user login
            "Pass" => 'password', // password
            "DB" => 'postgres', // data base
            "Schem" => 'public', // schema
            "Opt" => '--client_encoding=UTF8' // option
        ),
        "output" => array (
            "File" => 'server/modules/preset/output', // path to php file class output
            "Class" => 'Output', // class name
            "Path" => 'server/modules/preset/output', // path to dir output
        ),
        "getter" => array (
            "File" => 'server/modules/preset/getter.php', // path to php file class output
            "Class" => 'Getter', // class name , default name == Method
            "Required" => "required", // method return list of preset class required
            "Init" => "init", // method return list of preset class required
        )
    ),
    "core" => array (
        "File" => 'server/modules/preset/coref.php', // path to php file class output
        "Class" => 'CoreFunction', // class name , default name == Method
    ),
    "map" => array (
        "public" => 'server/map/public.php', // path to php file map
        "private" => 'server/map/private.php' // path to php file map, routing private only after auth
    ),
    "controllers" => array (
        "fileControllers" => "server/control" // path to php file or dir (index.php) controller
    ),
    "api" => array(
        "default" => array(
            "CountFigureX" => 5,
            "CountFigureY" => 3,
            "MaxFigureX" => 9,
            "MaxFigureY" => 9,
            "MaxCorrect" => 50,
            "MinBlockPixel" => 24,
            "MaxBlockPixel" => 42,
            "MinQuality" => 19,
            "MaxQuality" => 51,
            "MayBeMinus" => true,
            "MayBeZero" => true,
            "MinAnswer" => 0,
            "MaxAnswer" => 9,
            "BodyColor" => array(255,255,255),
            "PathFon" => "pic/fon/",
            "FileBody" => "machka.jpg",
            "PathElement" => "pic/image/",
            "Colors" => array('k','o','b','g','r'),
        )
    )
);