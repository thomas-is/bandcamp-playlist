
function statusDisplay( s, c = false) {

    if( ! navigator.onLine ) {
        s = "cloud_off";
        c = "red";
    }

    var a = document.getElementById("player");
    var current = document.querySelector(".current");
    if( a.getAttribute("src") ) {
        var e = document.getElementById("status");
        e.innerHTML = s;
        c ? e.setAttribute("class",c) : e.removeAttribute("class");
        c ? current.setAttribute("class","current "+c) : current.setAttribute("class","current");
    }

}

function networkDisplay() {
    navigator.onLine ? statusDisplay("cloud_queue","lime") : statusDisplay("cloud_off","red");
/*
    var a = document.getElementById("player");
0 = NETWORK_EMPTY - audio/video has not yet been initialized
1 = NETWORK_IDLE - audio/video is active and has selected a resource, but is not using the network
2 = NETWORK_LOADING - browser is downloading data
3 = NETWORK_NO_SOURCE - no audio/video source found
*/
}

function playMe(e) {

    var cover = document.querySelector("#cover>img");
    var audio = document.getElementById("player");
    var download = document.getElementById("download");

    var audiosrc = e.getAttribute("data-mp3");

    if( ! audiosrc ) {
        playIndex(currentIndex()+1);
        return;
    }

    var coversrc = e.parentElement.getAttribute("data-cover");
    download.setAttribute("href",audiosrc);

    var l = document.querySelectorAll("[data-mp3]");
    for( var i=0;i<l.length;i++) {
        l[i].removeAttribute("class");
    }
    e.setAttribute("class","current");
    audio.setAttribute("src",audiosrc);
    cover.setAttribute("src",coversrc);

    updateCurrent();
    document.children[0].focus();
    audio.load();
    audio.play();
    // topFunction();
}

function playIndex( index) {
    if (  index < 0 ) {
        playIndex( maxIndex() );
        return;
    }
    if ( index > maxIndex() ) playIndex(0);
    var e = document.querySelectorAll("[data-mp3]")[index];
    if( e.getAttribute("data-mp3") != "" ) {
        playMe(e);
        return;
    }
    playIndex(index+1);
}

function currentIndex(){
    var l = document.querySelectorAll("[data-mp3]");
    for( var i=0;i<l.length;i++) {
        if ( l[i].getAttribute("class") ) {
            if ( l[i].getAttribute("class").indexOf("current") != -1 ) { return i; }
        }
    }
    return -1;
}

function maxIndex(){ return document.querySelectorAll("[data-mp3]").length - 1; }
function playNext(){ playIndex(currentIndex()+1); }
function playPrev(){ playIndex(currentIndex()-1); }

function startPlaylist( e ){
    e.setAttribute("style","display:none");
    playIndex(0);
}

function albumSrc( index ){
    var e = document.querySelectorAll("[data-mp3]")[index];
    if ( ! e ) { return null; }
    var s = e.getAttribute("data-mp3");
    if ( ! s ) { return null; }
    return s.substr(0,s.lastIndexOf("/"));
}

function playNextAlbum(){
    var index = currentIndex();
    if ( index === null ) {
        playIndex(0);
        return;
    }
    var src = albumSrc(index);
    while( albumSrc(index) == src ){ index++; }
    playIndex( index );
}

function updateCurrent(){
    var index = currentIndex();
    var e = document.querySelectorAll("[data-mp3]")[index];
    if ( ! e ) {
        document.title = "Ol.fi - Playlist";
        document.querySelector("#nowplaying>.artist").innerHTML = "";
        document.querySelector("#nowplaying>.song").innerHTML = "";
        return;
    }
    var artist,album,song;
    song   = e.querySelector(".title").innerText;
    album  = e.parentElement.getAttribute("data-album");
    artist = e.parentElement.getAttribute("data-artist");
    document.title = artist+" - "+song;
    document.querySelector("#nowplaying>.artist").innerHTML = artist;
    document.querySelector("#nowplaying>.song").innerHTML = song;
    document.getElementById("nowplaying").setAttribute("data-index",index);
}

function scrollToPlaylist() {
    var index,l;
    index = currentIndex();
    l = document.querySelectorAll("[data-mp3]")[index];
    if ( ! l ) { return; }
    l.parentElement.scrollIntoView();
}


// When the user scrolls down 40px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    var e =document.getElementById("gotop");
    if (document.body.scrollTop > 60 || document.documentElement.scrollTop > 60) {
        e.setAttribute("style","visibility:visible");
    } else {
        e.setAttribute("style","visibility:hidden");
    }
}

// When the user clicks on the button, scroll to the top of the document
function topFunction() {
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}


// UNUSED 
/*
function playMeTop( e ){
    playMe(e);
    window.scrollTo(0,0);
}

function currentAlbum(){
    var e = document.querySelector(".current");
    if ( ! e) { return; }
    var s = e.getAttribute("data-src");
    return s.substr(0,s.lastIndexOf("/"));
}
*/


