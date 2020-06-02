function server_request(url, method) {
    let xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.send();
    return xhr;
}

document.getElementsByClassName('button')[0].onclick = function() {
    let button = server_request('/vstream/test', 'GET');
    button.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
        }
    }
};