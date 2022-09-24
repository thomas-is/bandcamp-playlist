<?php

include("../config.php");

?>
<!DOCTYPE html>
<html>
<head>
<title>Table of Processes</title>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo Config::HTML_CHARSET; ?>" />
<meta charset="<?php echo Config::HTML_CHARSET; ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#222">
<link href="<?php echo Config::CSS; ?>/top.css" rel="stylesheet" type="text/css" />
</head>
<script src="<?php echo Config::JS; ?>/xhr.js"></script>

<script>
function updateTop() {
    jget("jtop.php",buildTop);
}
function buildTop( data ) {
    var p;
    var t = document.querySelector("#top");
    t.innerHTML = "";
    for( var id in data ) {
        p = document.querySelector("#components>.process").cloneNode(true);
        t.appendChild(p);
        p.setAttribute("data-id",id);
        p.querySelector(".state").innerHTML = data[id]['state'];
        p.querySelector(".title").innerHTML = data[id]['title'];
    }
}
function send(e) {
    var id = e.parentElement.getAttribute("data-id");
    jget("jtop.php?start="+id,buildTop);
}
function clean()        { jget("jtop.php?clean",buildTop); }
function gencovers()    { jget("jtop.php?gencovers",buildTop); }

</script>

<body onload="window.setInterval(updateTop,2000)">

    <div id="page">
        <h1>Downloads queue</h1>
        <button onclick="gencovers()">Gen covers</button>
        <button onclick="clean()">Clean</button>
        <div id="top">

        </div>
    </div>




<div id="components">

    <div class="process" data-id="">
        <button onclick="send(this)">Start</button>
        <span class="state"></span>
        <span class="title"></span>
    </div>

</div>

</body>
</html>
