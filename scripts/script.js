function server_request(url, method) {
    let ajax = new XMLHttpRequest();
    ajax.open(method, url, true);
    ajax.send();
    ajax.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            alert(this.response);
            return JSON.parse(this.responseText);
        }
    }
}

var buttons = document.getElementsByClassName('button');
for (var i = 0; i < buttons.length; i++) {
    buttons[i].onclick = function() {
        console.log(server_request('/vstream/test', 'GET'));
    };
}