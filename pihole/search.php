<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="icon" href="admin/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="admin/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
    <title>Pihole Adlists</title>
    <script>
        function placeholderIsSupported() {
            test = document.createElement('input');
            return ('placeholder' in test);
        }

        $(document).ready(function(){
            placeholderSupport = placeholderIsSupported() ? 'placeholder' : 'no-placeholder';
            $('html').addClass(placeholderSupport);  
        });
    </script>
    <style>
        body{
            background:#b4b4b4;
        }

        #registration-form {
            font-family:'Open Sans Condensed', sans-serif;
            width: 400px;
            min-width:250px;
            margin: 20px auto;
            position: relative;
        }

        #registration-form .fieldset {
            background-color:#d5d5d5;
            border-radius: 3px;
        }

        #registration-form legend {
            text-align: center;
            background: #364351;
            width: 100%;
            padding: 30px 0;
            border-radius: 3px 3px 0 0;
            color: white;
            font-size:2em;
        }

        .fieldset form{
            border:1px solid #2f2f2f;
            margin:0 auto;
            display:block;
            width:100%;
            padding:30px 20px;
            box-sizing:border-box;
            border-radius:0 0 3px 3px;
        }
        .placeholder #registration-form label {
            display: none;
        }
        .no-placeholder #registration-form label{
            margin-left:5px;
            position:relative;
            display:block;
            color:grey;
            text-shadow:0 1px white;
            font-weight:bold;
        }
        /* all */
        ::-webkit-input-placeholder { text-shadow:1px 1px 1px white; font-weight:bold; }
        ::-moz-placeholder { text-shadow:0 1px 1px white; font-weight:bold; } /* firefox 19+ */
        :-ms-input-placeholder { text-shadow:0 1px 1px white; font-weight:bold; } /* ie */
        #registration-form input[type=text] {
            padding: 15px 20px;
            border-radius: 1px;
            margin:5px auto;
            background-color:#f7f7f7;
            border: 1px solid silver;
            -webkit-box-shadow: inset 0 1px 5px rgba(0,0,0,0.2);
            box-shadow: inset 0 1px 5px rgba(0,0,0,0.2), 0 1px rgba(255,255,255,.8);
            width: 100%;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            -ms-box-sizing: border-box;
            box-sizing: border-box;
            -webkit-transition:background-color .5s ease;
            -moz-transition:background-color .5s ease;
            -o-transition:background-color .5s ease;
            -ms-transition:background-color .5s ease;
            transition:background-color .5s ease;
        }
        .no-placeholder #registration-form input[type=text] {
            padding: 10px 20px;
        }

        #registration-form input[type=text]:active, .placeholder #registration-form input[type=text]:focus {
            outline: none;
            border-color: silver;
            background-color:white;
        }

        #registration-form input[type=submit] {
            font-family:'Open Sans Condensed', sans-serif;
            text-transform:uppercase;
            outline:none;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            -ms-box-sizing: border-box;
            box-sizing: border-box;
            width: 100%;
            background-color: #5C8CA7;
            padding: 10px;
            color: white;
            border: 1px solid #3498db;
            border-radius: 3px;
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 5px;
            cursor: pointer;
            position:relative;
            top:0;
        }

        #registration-form input[type=submit]:hover {
            background-color: #2980b9;
        }

        #registration-form input[type=submit]:active {
            background:#5C8CA7;
        }


        .parsley-error-list{
            background-color:#C34343;
            padding: 5px 11px;
            margin: 0;
            list-style: none;
            border-radius: 0 0 3px 3px;
            margin-top:-5px;
            margin-bottom:5px;
            color:white;
            border:1px solid #870d0d;
            border-top:none;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            position:relative;
            top:-1px;
            text-shadow:0px 1px 1px #460909;
            font-weight:700;
            font-size:12px;
        }
        .parsley-error{
            border-color:#870d0d!important;
            border-bottom:none;
        }
        #registration-form select{
            width:100%;
            padding:5px;
        }
        ::-moz-focus-inner {
            border: 0;
        }
        h1{
            text-align: center;
        }
    </style>
    </head>
    <body>
        <h1>Pihole search adlist</h1>
        <?php
        if(!isset($_POST['domain']))
        echo '
        <div id="registration-form">
            <div class="fieldset">
                <legend>Search in adlists(not in blacklist or whitelist)</legend>
                <form action="" method="post" data-validate="parsley">
                    <div class="row">
                        <label for="domain">Domain to search</label>
                        <input type="text" placeholder="Domain to search" name="domain" id="domain" data-required="true" data-error-message="Domain is required!">
                    </div>
                    <input type="submit" value="Search">
                </form>
            </div>
        </div>
        ';
        else
        {
            echo '<div id="registration-form">';
            //((http)*(s)*(:\/\/)*)
            $lower = strtolower($_POST['domain']);
            $match = preg_match("/^((http)+(s)*(:\/\/)*[a-z1-9\$–_.+!*‘(),]*\.*[a-z1-9\$–_.+!*‘(),]*\.[a-z]*.*)$/",$lower);
            $match2 = preg_match("/^([a-z1-9\$–_.+!*‘(),]*\.*[a-z1-9\$–_.+!*‘(),]*\.[a-z]*.*)$/",$lower);
            if($match == 1)
            {
                $dom = sscanf($lower,"%[a-z]://%s");
                $dom2 = $dom[1];
            } else
            {
                $dom2 = $lower;
            }
            $wwwmatch = preg_match("/^(www\.+[a-z1-9\$–_.+!*‘(),]*\.[a-z]*.*)$/",$dom2);
            //echo $dom2;
            switch($match)
            {
                case 1:
                    {
                        if($wwwmatch == 1)
                        {
                            //echo "11<br />";
                           $array = sscanf($lower,"%[a-z]://%[a-z1-9$–_+!*‘(),].%[a-z1-9$–_+!*‘(),].%[a-z]");
                           $domain = $array[2].'.'.$array[3];
                        } else
                        {
                            //echo "10<br />";
                            $array = sscanf($lower,"%[a-z1-9$–_+!*‘(),]://%[a-z1-9$–_+!*‘(),].%[a-z]");
                            $domain = $array[1].'.'.$array[2];
                        }
                        break; 
                    }
                case 0:
                    {
                        if($match2 == 1)
                        {
                            if($wwwmatch == 1)
                            {
                                //echo "01<br />";
                                $array = sscanf($lower,"%[a-z1-9$–_+!*‘(),].%[a-z1-9$–_+!*‘(),].%[a-z]");
                                $domain = $array[1].'.'.$array[2];
                            } else
                            {
                                //echo "00<br />";
                                $array = sscanf($lower,"%[a-z1-9$–_+!*‘(),].%[a-z]");
                                $domain = $array[0].'.'.$array[1];
                                //echo "array(0) = '".$array[0]."'<br />array(1) = '".$array[1]."'<br />";
                            }
                        }
                        break; 
                    }
                case false:
                    {
                        echo "general error! contact to rhs@rhscze.cf<br />";
                        $error = true;
                        break; 
                    }

            }
            if(!isset($domain))
            {
                $error = true;
            }
            if(!$error)
            {
            //echo $domain."<br />";
            //echo "SELECT * FROM gravity WHERE domain like `%'.$domain.'`;";
            $db = new SQLite3('/etc/pihole/gravity.db');
            $res = $db->query("SELECT * FROM gravity WHERE domain like '%".$domain."';");
            if($res == false)
            {
                echo "domain not found!!";
            } else
            {
                echo "domains founded:<br />";
                while ($row = $res->fetchArray()) {
                    echo "domain: ".$row['domain']."<br />";
                    }
            }
            }
            echo '</div>';
        }
        ?>
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