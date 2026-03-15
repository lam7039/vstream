class Ajax {
    constructor() {
        this.xhr = new XMLHttpRequest;
    }

    async #request(method, path, data = {}, headers = {}, username = null, password = null) {
        let response = await fetch(path, {
            method,
            headers,
            // body: JSON.stringify({
            //     name: username,
            //     password
            // })
        }).catch((error) => console.log('Error: ', error));
        console.log(response.text());
    }

    get = (path, data = {}, username = null, password = null) => {
        return this.#request('GET', path, data, {'Access-Control-Allow-Origin': 'http://vstream.localhost'}, username, password, async);
    }

    post = (path, data = {}, username = null, password = null) => {
        return this.#request('POST', path, data, {'Content-Type': 'application/x-www-form-urlencoded'}, username, password, async);
    }
}

const ajax = new Ajax;

export function ajax_get(path, data = {}, username = null, password = null) {
    return ajax.get(path, data, username, password, async);
}

export function ajax_post(path, data = {}, username = null, password = null) {
    return ajax.post(path, data, username, password, async);
}

export default Ajax;
