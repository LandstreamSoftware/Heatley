<?php
// Include the main.php file
include 'emailconnection.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice</title>
    <meta name="author" content="Barry Pyle" />
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        h1 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 15pt;
        }

        .p, p, body {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 12pt;
            margin: 0pt;
        }

        b, strong {
            font-weight: $font-weight-bolder;
        }

        h3 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 10pt;
        }

        h2 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
        }

        .s1 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: italic;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
        }

        li {
            display: block;
        }

        #l1 {
            padding-left: 0pt;
            counter-reset: c1 1;
        }

        #l1>li>*:first-child:before {
            counter-increment: c1;
            content: counter(c1, upper-latin)". ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 18px;
        }

        #l1>li:first-child>*:first-child:before {
            counter-increment: c1 0;
        }

        #l2 {
            padding-left: 0pt;
            counter-reset: c2 1;
        }

        #l2>li>*:first-child:before {
            counter-increment: c2;
            content: counter(c2, decimal)" ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
            padding-right: 20px;
        }

        #l2>li:first-child>*:first-child:before {
            counter-increment: c2 0;
        }

        #l3 {
            padding-left: 0pt;
            counter-reset: c3 1;
        }

        #l3>li>*:first-child:before {
            counter-increment: c3;
            content: counter(c2, decimal)"." counter(c3, decimal)" ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 10px;
        }

        #l3>li:first-child>*:first-child:before {
            counter-increment: c3 0;
        }

        #l4 {
            padding-left: 0pt;
            counter-reset: d1 1;
        }

        #l4>li>*:first-child:before {
            counter-increment: d1;
            content: "(" counter(d1, lower-latin)") ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 15px;
        }

        #l4>li:first-child>*:first-child:before {
            counter-increment: d1 0;
        }

        #l5 {
            padding-left: 0pt;

            counter-reset: e1 1;
        }

        #l5>li>*:first-child:before {
            counter-increment: e1;
            content: "(" counter(e1, lower-latin)") ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 15px;
        }

        #l5>li:first-child>*:first-child:before {
            counter-increment: e1 0;
        }

        td, th {
            vertical-align: middle;
            border-bottom: solid;
            border-bottom-color: rgb(222, 226, 230);
            border-bottom-width: 1.11111px;
            border-left-width: 0;
            border-right-width: 0;
            padding:10px;
        }

        #highlight {
            color:orange;
        }

        @media screen {
            p.page-number {
                display:none;
            }
        }

    </style>
</head>
<body>
<table style="width:700px; margin-left:auto; margin-right:auto;">
    <tr>
        <td colspan="3" style="padding: 0 0 10px 5px;">
            <h1 style="font-size:40px; padding-top: 3pt;padding-top: 3pt;text-indent: 0pt;text-align:right;">Company logo here</h1>
        </td>
    </tr>
</table>
</body>
</html>