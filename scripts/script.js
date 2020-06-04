document.getElementsByClassName('button')[0].onclick = function() {
    fetch('/test')
        .then(response => response.text())
        .then(result => console.log(result));
};

//var links = document.getElementsByTagName('a');
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