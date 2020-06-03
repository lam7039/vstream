document.getElementsByClassName('button')[0].onclick = function() {
    fetch('/vstream/test')
        .then(response => response.text())
        .then(result => console.log(result));
};

document.getElementsByTagName('a')[0].onclick = function() {
    section = document.getElementsByTagName('section')[0];
    fetch('/vstream/page')
        .then(response => response.text())
        .then(result => {
            section.innerHTML = result;
        });
}