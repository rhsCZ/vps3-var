<?php
$result = dns_get_record("pihole.rhscze.cf",DNS_A);
//var_dump($result);
//echo "<br />";
echo $result[0]["ip"];
?>