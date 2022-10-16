<?php
use NoLibForIt\Bandcamp\PlaylistByDir;
include("../config.php");
$playlist = new PlaylistByDir;
?>
<!DOCTYPE html>
<html>
<head>
<title>Playlist</title>
<meta http-equiv="Content-Type" content="text/html;charset=<?php echo HTML_CHARSET; ?>" />
<meta charset="<?php echo HTML_CHARSET; ?>" />
<link rel="icon" type="image/png" href="pics/zero.png">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#222">
<link href="<?php echo SRC_CSS; ?>/playlist.css" rel="stylesheet" type="text/css" />
</head>
<script src="<?php echo SRC_JS; ?>/playlist.js"></script>
<body >

<div id="page">

  <span id="gotop" onclick="topFunction()"></span>

  <div class="flexplayer">
    <div id="nowplaying" onclick="scrollToPlaylist()">
      <div class="artist">n/a</div>
      <div class="song">n/a</div>
      <span id="status"></span>
    </div>
    <div id="cover" onclick="playNextAlbum()">
      <img src="pics/cover-320px.jpg">
    </div>
    <audio id="player" src="" controls
      onloadstart="statusDisplay('cloud_download','green blink')"
      onprogress="statusDisplay('cloud_download','green blink')"
      onwaiting="statusDisplay('cloud_queue','yellow blink')"
      onstalled="statusDisplay('cloud_queue','yellow blink')"
      onpause="statusDisplay('')"
      ontimeupdate="networkDisplay()"
      onended="playNext()"
    ></audio>
    <div id="nav">
      <span id="prev" onclick="playPrev()"></span>
      <span id="next" onclick="playNext()"></span>
      <a id="download" href="" download ></a>
    </div>
  </div>

  <div class="flexplaylist">
  <?php foreach( (array) $playlist->get_nfo() as $nfo ): ?>
    <div data-artist="<?php echo $nfo['artist']; ?>" data-album="<?php echo $nfo['album']; ?>" data-cover="<?php echo $nfo['path']; ?>cover-320px.jpg">
    <div class="albumhead">
      <img class="smallcover" src="<?php echo $nfo['path']; ?>cover-128px.jpg">
      <div class="nfo">
      <span class="artist"><?php echo $nfo['artist']; ?></span>
      <span class="album"><?php echo $nfo['album']; ?></span>
      <span class="released"><?php echo substr($nfo['released'],0,4); ?></span>
    </div>
    </div>
  <?php foreach ($nfo['tracks'] as $track): ?>
  <div onclick="playMe(this);" data-mp3="<?php if(!empty($track['mp3']) and file_exists($playlist->root().$track['mp3'])) echo $playlist->root().$track['mp3']; ?>">
    <span class="track"><?php echo $track['num']; ?></span>
    <span class="title"><?php echo $track['title']; ?></span>
  </div>
  <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  </div>

</div>

</body>
</html>
