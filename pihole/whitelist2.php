<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="icon" href="admin/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="admin/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
    <title>Pihole Whitelist</title>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
        h1{
            text-align: center;
        }
    </style>
    </head>
    <body>
        <h1>Pihole Whitelist</h1>
        <table>
            <tr>
                <th>Domain Name</th>
            </tr>
            <?php
            $db = new SQLite3('/etc/pihole/gravity.db');
            $res = $db->query('SELECT * FROM domainlist WHERE type = 1 OR type = 3 ORDER BY id ASC;');
            while ($row = $res->fetchArray()) {
                $datecreate = date("d:m:Y H:i:s",$row['date_added']);
                $datemodify = date("d:m:Y H:i:s",$row['date_modified']);
                if($row['enabled'])
                {
                    $enabled = "True";
                } else
                {
                    $enabled = "False";
                }
                if($row['type'] == 1)
                {
                    $type = "Normal entry";
                } else
                {
                    $type = "RegEx Entry";
                }
                echo '<tr><td>'.$row['domain'].'</td></tr>';
            }
            ?>
        </table>
    </body>
</html>
<?php
/*
//echo gettype($res) . "<br />";
            //echo "abc<br />";
//$fileexst = file_exists("/etc/pihole/gravity.db");
//$db = SQLite3_connect("/etc/pihole/gravity.db",SQLITE3_OPEN_READONLY);
        //$db->open("/etc/pihole/gravity.dbs",SQLITE3_OPEN_READWRITE)
        //$res = $db->query('SELECT * FROM `domainlist` WHERE `type` = 1;');
 //$version = $db->querySingle('SELECT SQLITE_VERSION()');
        //echo $fileexst . "<br />";
        //echo $version . "<br />";
        //echo $res;
//var_dump($res);
*/
?>