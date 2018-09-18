<?php
$_GENL = Array(
    "global" => []
);
$_CNTRL = Array(
    "header" => [
        "file" => "server/templates/header.php",
        "type" => "function",
    ],
    "footer" => [
        "file" => "server/templates/footer.php",
        "type" => "function",
    ],
    "auth" => [
        "file" => "server/classes/Auth.php", // path to php file or directory (index.php)
        "class" => "Auth", // name class, default name == filename or directory name
        "main" => "init", // start method
        "type" => "class", // class or function
        "config" => true, // send Config
        "global" => [ // global param to method init
            "Session"
        ],
        "before" => [ // start before this controller
        ],
        "after" => [ // start after this controller
        ],
    ],
    "logout" => [
        "file" => "server/classes/LogOut.php",
        "class" => "Logout",
        "main" => "init",
        "type" => "class",
        "config" => false,
        "global" => [
            "Session"
        ]
    ],
    "safe_load" => [
        "file" => "server/vendors/SafeLoad",
        "class" => "SafeLoad",
        "main" => "main",
        "global" => [
            "Remainder", "Core"
        ],
        "before" => [ // controller for rights checking
        ],
        "html" => false
    ],
    "api" => [
        "file" => "server/classes/Api.php",
        "class" => "Api",
        "global" => ["Output"],
        "before" => ["api_get"],
        "config" => false,
        "html" => false,
        "methods" => ["GET"] // http method request on upper case, default All methods
    ],
    "api_get" => [
        "file" => "server/classes/ApiGet.php",
        "class" => "ApiGet",
        //"global" => ["Remainder", "DB"],
        "ParametersInit" => false
    ]
);