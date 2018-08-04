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
        "file" => "server/classes/Auth.php", // path to php file or dir (index.php)
        "class" => "Auth", // name class, default name == filename or dirname
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
    "test" => [
        "file" => "server/modules/test.php",
        "type" => "function",
        "global" => ["Remainder", "Core"],
        'html' => false
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
        'html' => false
    ]
);