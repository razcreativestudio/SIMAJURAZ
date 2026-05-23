<?php
$content = file_get_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php');
// Add missing quote for kb_c8_desc in both id and en
$content = str_replace("</ul>\r\n    ],", "</ul>',\r\n    ],", $content);
$content = str_replace("</ul>\n    ],", "</ul>',\n    ],", $content);

// For the last one in EN
$content = str_replace("</ul>\r\n    ]", "</ul>'\r\n    ]", $content);
$content = str_replace("</ul>\n    ]", "</ul>'\n    ]", $content);

file_put_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php', $content);
file_put_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\includes\RAZlang.php', $content);
echo "Quotes fixed!";
?>
