let video_displayed = false;
document.getElementsByClassName('playpause')[0].onclick = () => {
    fetch('/test')
        .then(response => response.json())
        .then(result => console.log(result));

    let video = document.getElementsByTagName('video')[0];
    let content = document.getElementsByClassName('yielded_content')[0];
    if (video_displayed) {
        video.style.display = "none";
        content.style.display = "block";
        video_displayed = false;
        this.style.background = "#97979c";
        return;
    }
    video.style.display = "block";
    content.style.display = "none";
    this.style.background = "#73737c";
    video_displayed = true;
};

//let links = document.getElementsByTagName('a');
//for (let i = 0; i < links.length; i++) {
//    links[i].onclick = function() {
//        section = document.getElementsByTagName('section')[0];
//        fetch('/content_' + links[i].innerHTML)
//            .then(response => response.text())
//            .then(result => {
//                section.innerHTML = result;
//            });
//    }
//}

//TODO: use ajax calls instead of redirects