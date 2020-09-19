class ajax {
    xhr = null;

    constructor() {
        this.xhr = new XMLHttpRequest;
    }

    request = (path, object = {}, header = null) => {
        this.xhr.open('GET', path, true);
        if (header) {
            this.xhr.setRequestHeader('Content-Type', header);
        }
        this.xhr.onreadystatechange = () => {
            if (this.xhr.readyState !== 4 || this.xhr.status !== 200) {
                return;
            }
        }

        this.xhr.send();
    }

    get = (path, object = {}) => {
        this.request(path, object);
    }

    post = (path, object = {}) => {
        this.request(path, object, 'application/x-www-form-urlencoded');
    }
}

class router {

    get = (path) => {



        document.getElementsByClassName('content')[0].innerHTML = '';

    }
}

export default router;