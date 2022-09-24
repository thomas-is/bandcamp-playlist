<?php

    include("../config.php");

//die("<h1>Disabled by admin</h1>");

    $url = rawurldecode(@$_GET['url']);

    $bandcamp = new Bandcamp($url);

    if( isset($_GET['download']) ) $bandcamp->download();

?>
<!DOCTYPE html>
<html>
<head>
<title>Bandcamp downloader</title>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo Config::HTML_CHARSET; ?>" />
<meta charset="<?php echo Config::HTML_CHARSET; ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#222">
<link href="<?php echo Config::CSS; ?>/bandcamp.css" rel="stylesheet" type="text/css" />
</head>
<script src="<?php echo Config::JS; ?>/xhr.js"></script>
<script>
function playMe( e ) {
    var tracks = document.getElementById("tracks");
    for( var i=0;i<tracks.childElementCount;i++){
        tracks.children[i].removeAttribute("data-playing");
    }
    e.setAttribute("data-playing",true);
    var src = e.getAttribute("data-src");
    var index = e.getAttribute("data-index");
    audio = document.getElementById("player");
    audio.setAttribute("src",src);
    audio.setAttribute("data-index",index);
    audio.play();
}
function playNext() {
    var audio = document.getElementById("player");
    var index = audio.getAttribute("data-index");
    var tracks = document.getElementById("tracks");
    var next = tracks.children[index];
    console.log(next);
    next ? playMe(next) : playMe(tracks.children[0]); 
}
</script>
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

        <h1>Bandcamp downloader</h1>

        <form action="<?php echo $_SERVER['PHP_SELF'];?>">
            <input type="text" name="url" placeholder="Enter bandcamp url" value="">
            <button type="submit">Preview</button>
            <button type="submit" name="download">Download</button>
        </form>

        <div id="bandcamp" <?php if( empty($bandcamp->get_album()) ) { echo 'style="display:none"'; } ?>>
            <a id="url" href="<?php echo @$bandcamp->get_url(); ?>"><?php echo @$bandcamp->get_url(); ?></a>
            <span id="artist"   ><?php echo @$bandcamp->get_HTML_artist();    ?></span> 
            <span id="album"    ><?php echo @$bandcamp->get_HTML_album();     ?></span>
            <span id="released" ><?php echo @$bandcamp->get_HTML_released();  ?></span>
            <img  id="cover" src="<?php echo @$bandcamp->get_cover_src(); ?>">
            <audio id="player" onended="playNext()" controls type="audio/mpeg" src=""></audio>
               
            <div id="tracks">
            <?php foreach( @$bandcamp->get_tracks() as $track ): ?>
                <span
                    data-src="<?php echo $track->get_mp3url();?>"
                    data-index="<?php echo $track->get_num();?>"
                    onclick="playMe(this)"
                >
                    <?php echo $track->get_HTML_title(); ?>
                </span>
            <?php endforeach; ?>
            </div>
        </div>
        <a href="<?php echo $_SERVER['PHP_SELF']."?url=".@$bandcamp->get_url()."&download"; ?>"><button>Add to queue</button></a>
        <button onclick="gencovers()">Rebuild covers</button>
        <button onclick="clean()">Clean</button>
        <div id="top"></div>

        <div id="components">
            <div class="process" data-id="">
                <span class="state" onclick="send(this)"></span>
                <span class="title"></span>
            </div>
        </div>
    </div>
</body>
</html>
